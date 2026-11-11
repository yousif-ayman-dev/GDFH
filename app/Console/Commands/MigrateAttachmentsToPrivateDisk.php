<?php

namespace App\Console\Commands;

use App\Models\Attachment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MigrateAttachmentsToPrivateDisk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attachments:migrate-to-private
                            {--dry-run : Simulate the migration without copying files or modifying records}
                            {--limit= : Limit the number of attachment records to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely copy legacy attachments from public disk to private disk with SHA-256 verification and collision handling.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        $this->info('Starting attachment migration to private storage disk...');
        if ($dryRun) {
            $this->warn('MODE: DRY-RUN (No files will be written, no database records modified)');
        }

        $query = Attachment::query()->where('disk', 'public')->orderBy('id');

        if ($limit) {
            $query->limit($limit);
        }

        $totalRecords = $query->count();

        if ($totalRecords === 0) {
            $this->info('No legacy attachments on [public] disk found. All records are up to date.');
            return Command::SUCCESS;
        }

        $this->info("Found {$totalRecords} attachment(s) to inspect.");

        $processed = 0;
        $migrated = 0;
        $skippedMissing = 0;
        $collisionsResolved = 0;
        $verificationFailed = 0;

        $attachments = $query->get();

        foreach ($attachments as $attachment) {
            $processed++;
            $sourcePath = $attachment->path;

            // 1. Verify source existence on public disk
            if (! Storage::disk('public')->exists($sourcePath)) {
                $this->warn("⚠️  [Attachment #{$attachment->id}] Source file missing from public disk: {$sourcePath}");
                Log::warning("MigrateAttachmentsToPrivateDisk: Source file missing for Attachment #{$attachment->id} at path: {$sourcePath}");
                $skippedMissing++;
                continue;
            }

            // 2. Read source and compute SHA-256 checksum
            $sourceContent = Storage::disk('public')->get($sourcePath);
            $sourceSha = hash('sha256', $sourceContent);
            $sourceSize = strlen($sourceContent);

            // 3. Determine destination path on private disk
            $targetPath = 'attachments/' . ($attachment->stored_name ?: basename($sourcePath));

            // 4. Handle destination collisions
            if (Storage::disk('private')->exists($targetPath)) {
                $targetSha = hash('sha256', Storage::disk('private')->get($targetPath));
                if ($targetSha !== $sourceSha) {
                    $collisionsResolved++;
                    $uniquePrefix = substr($sourceSha, 0, 12);
                    $targetPath = 'attachments/' . $uniquePrefix . '_' . ($attachment->stored_name ?: basename($sourcePath));
                    $this->line("ℹ️  [Attachment #{$attachment->id}] Collision detected. Renamed target to: {$targetPath}");
                }
            }

            // 5. Dry-run simulation vs real copy & verify
            if ($dryRun) {
                $this->line("🔍 [DRY-RUN] ID {$attachment->id}: public/{$sourcePath} -> private/{$targetPath} (Size: {$sourceSize} bytes, SHA256: " . substr($sourceSha, 0, 8) . "...)");
                $migrated++;
                continue;
            }

            // 6. Non-destructive write to private disk
            try {
                Storage::disk('private')->put($targetPath, $sourceContent);

                // 7. Re-read and verify target content and checksum
                if (! Storage::disk('private')->exists($targetPath)) {
                    $this->error("❌ [Attachment #{$attachment->id}] Target file not found after write: {$targetPath}");
                    $verificationFailed++;
                    continue;
                }

                $writtenSha = hash('sha256', Storage::disk('private')->get($targetPath));
                if ($writtenSha !== $sourceSha) {
                    $this->error("❌ [Attachment #{$attachment->id}] SHA-256 checksum mismatch! Expected: {$sourceSha}, Got: {$writtenSha}");
                    Log::error("MigrateAttachmentsToPrivateDisk: Checksum mismatch for Attachment #{$attachment->id}");
                    $verificationFailed++;
                    continue;
                }

                // 8. Update database record in transaction
                DB::transaction(function () use ($attachment, $targetPath, $writtenSha, $sourceSize) {
                    $attachment->update([
                        'disk' => 'private',
                        'path' => $targetPath,
                        'checksum' => $writtenSha,
                        'size' => $sourceSize,
                    ]);
                });

                $migrated++;
                $this->line("✅ [Attachment #{$attachment->id}] Successfully migrated to private/{$targetPath}");
            } catch (\Throwable $e) {
                $this->error("❌ [Attachment #{$attachment->id}] Error during migration: {$e->getMessage()}");
                Log::error("MigrateAttachmentsToPrivateDisk error on Attachment #{$attachment->id}: {$e->getMessage()}");
                $verificationFailed++;
            }
        }

        // Summary report
        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Inspected', $processed],
                ['Successfully Migrated' . ($dryRun ? ' (Simulated)' : ''), $migrated],
                ['Missing Source Files (Skipped)', $skippedMissing],
                ['Collisions Resolved', $collisionsResolved],
                ['Verification / Write Failures', $verificationFailed],
                ['Execution Mode', $dryRun ? 'DRY-RUN' : 'LIVE (Non-Destructive)'],
            ]
        );

        if ($verificationFailed > 0) {
            $this->error('Migration completed with verification failures. Inspect log for details.');
            return Command::FAILURE;
        }

        $this->info('Migration operation completed successfully.');
        return Command::SUCCESS;
    }
}
