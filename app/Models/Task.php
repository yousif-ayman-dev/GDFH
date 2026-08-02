<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'team_id',
        'created_by',
        'assigned_to',
        'parent_id',
        'title',
        'description',
        'status',
        'priority',
        'start_at',
        'due_at',
        'completed_at',
        'estimated_minutes',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'estimated_minutes' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Check if task is past due date.
     */
    public function isLate(): bool
    {
        if (in_array($this->status, ['completed', 'done', 'cancelled'], true)) {
            return false;
        }

        if (! $this->due_at) {
            return false;
        }

        return now()->startOfDay()->gt($this->due_at->startOfDay());
    }

    /**
     * Get remaining days until due date.
     */
    public function remainingDays(): int
    {
        if (in_array($this->status, ['completed', 'done', 'cancelled'], true)) {
            return 0;
        }

        if (! $this->due_at || now()->startOfDay()->gte($this->due_at->startOfDay())) {
            return 0;
        }

        return (int) now()->startOfDay()->diffInDays($this->due_at->startOfDay());
    }

    /**
     * Get task duration in days.
     */
    public function durationDays(): int
    {
        $startDate = $this->start_at ?? $this->created_at;

        if (! $startDate || ! $this->due_at) {
            return 0;
        }

        return (int) $startDate->startOfDay()->diffInDays($this->due_at->startOfDay());
    }
}
