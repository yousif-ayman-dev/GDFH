<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Worklog extends Model
{
    use HasFactory;

    protected $table = 'worklogs';

    protected $fillable = [
        'user_id',
        'project_id',
        'task_id',
        'start_time',
        'end_time',
        'duration',
        'status',
        'notes',
        'is_billable',
        'is_manual',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'project_id' => 'integer',
            'task_id' => 'integer',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'duration' => 'integer',
            'is_billable' => 'boolean',
            'is_manual' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    /**
     * Get live current elapsed seconds.
     */
    public function currentSeconds(): int
    {
        $accumulated = (int) $this->duration;

        if ($this->isRunning() && $this->start_time) {
            $elapsed = (int) Carbon::now()->diffInSeconds($this->start_time);
            return $accumulated + $elapsed;
        }

        return $accumulated;
    }

    /**
     * Format duration into human readable string (e.g. "02h 15m 30s" or "45m").
     */
    public function formattedDuration(): string
    {
        $seconds = $this->currentSeconds();

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02dh %02dm', $hours, $minutes);
        }

        if ($minutes > 0) {
            return sprintf('%02dm %02ds', $minutes, $secs);
        }

        return sprintf('%02ds', $secs);
    }
}
