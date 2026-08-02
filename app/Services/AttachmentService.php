<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AttachmentService
{
    /**
     * Maximum file size in kilobytes (20 MB).
     */
    protected int $maxKb = 20480;

    /**
     * Allowed MIME types and extensions.
     */
    protected array $allowedExtensions = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'zip', 'rar', 'png', 'jpg', 'jpeg', 'webp', 'gif',
        'csv', 'svg',
    ];

    public function __construct(
        protected ActivityService $activityService
    ) {}

    /**
     * Validate an uploaded file.
     */
    public function validateAttachment(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new \InvalidArgumentException('الملف المرفق غير صالح أو حدث خطأ أثناء الرفع.');
        }

        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, $this->allowedExtensions, true)) {
            throw new \InvalidArgumentException("نوع الملف '.{$extension}' غير مسموح به.");
        }

        if ($file->getSize() > ($this->maxKb * 1024)) {
            throw new \InvalidArgumentException('حجم الملف يتجاوز الحد الأقصى المسموح به (20 ميجابايت).');
        }
    }

    /**
     * Generate metadata array for attachment.
     */
    public function generateMetadata(UploadedFile $file): array
    {
        return [
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'client_extension' => $file->getClientOriginalExtension(),
            'size_bytes' => $file->getSize(),
        ];
    }

    /**
     * Upload and attach a file to a polymorphic model.
     */
    public function upload(User $user, Model $attachable, UploadedFile $file, string $disk = 'public'): Attachment
    {
        $this->validateAttachment($file);

        return DB::transaction(function () use ($user, $attachable, $file, $disk) {
            $path = $file->store('attachments', $disk);
            $hash = md5_file($file->getRealPath());

            $attachment = Attachment::create([
                'user_id' => $user->id,
                'uploaded_by' => $user->id,
                'attachable_type' => get_class($attachable),
                'attachable_id' => $attachable->getKey(),
                'original_name' => $file->getClientOriginalName(),
                'stored_name' => basename($path),
                'disk' => $disk,
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'extension' => strtolower($file->getClientOriginalExtension()),
                'size' => $file->getSize(),
                'checksum' => $hash,
                'visibility' => 'private',
                'metadata' => $this->generateMetadata($file),
            ]);

            $this->activityService->record(
                $user,
                $attachable,
                'attachment_uploaded',
                "رفع المرفق '{$attachment->original_name}'",
                ['attachment_id' => $attachment->id, 'size' => $attachment->size]
            );

            return $attachment;
        });
    }

    /**
     * Replace an existing attachment file.
     */
    public function replace(Attachment $attachment, UploadedFile $newFile, string $disk = 'public'): Attachment
    {
        $this->validateAttachment($newFile);

        $newHash = md5_file($newFile->getRealPath());

        if ($attachment->checksum === $newHash) {
            throw new \InvalidArgumentException('الملف الجديد مطابق تماماً للملف الحالي (مرفق مكرر).');
        }

        return DB::transaction(function () use ($attachment, $newFile, $disk, $newHash) {
            // Delete old file from storage
            if (Storage::disk($attachment->disk)->exists($attachment->path)) {
                Storage::disk($attachment->disk)->delete($attachment->path);
            }

            $newPath = $newFile->store('attachments', $disk);

            $attachment->update([
                'original_name' => $newFile->getClientOriginalName(),
                'stored_name' => basename($newPath),
                'disk' => $disk,
                'path' => $newPath,
                'mime_type' => $newFile->getMimeType(),
                'extension' => strtolower($newFile->getClientOriginalExtension()),
                'size' => $newFile->getSize(),
                'checksum' => $newHash,
                'metadata' => $this->generateMetadata($newFile),
            ]);

            if ($subject = $attachment->attachable) {
                $this->activityService->record(
                    $attachment->user,
                    $subject,
                    'attachment_replaced',
                    "استبدل المرفق بـ '{$attachment->original_name}'",
                    ['attachment_id' => $attachment->id]
                );
            }

            return $attachment->fresh();
        });
    }

    /**
     * Download attachment file.
     */
    public function download(Attachment $attachment)
    {
        if (! Storage::disk($attachment->disk)->exists($attachment->path)) {
            abort(404, 'الملف غير موجود في خادم التخزين.');
        }

        return Storage::disk($attachment->disk)->download(
            $attachment->path,
            $attachment->original_name
        );
    }

    /**
     * Delete attachment record and file.
     */
    public function delete(Attachment $attachment): void
    {
        DB::transaction(function () use ($attachment) {
            $subject = $attachment->attachable;

            if (Storage::disk($attachment->disk)->exists($attachment->path)) {
                Storage::disk($attachment->disk)->delete($attachment->path);
            }

            if ($subject) {
                $this->activityService->record(
                    $attachment->user,
                    $subject,
                    'attachment_deleted',
                    "حذف المرفق '{$attachment->original_name}'",
                    ['attachment_id' => $attachment->id]
                );
            }

            $attachment->delete();
        });
    }
}
