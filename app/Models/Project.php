<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Project extends Model
{
    protected $fillable = [
        'owner_id',
        'title',
        'slug',
        'description',
        'category',
        'visibility',
        'status',
        'budget_type',
        'budget_min',
        'budget_max',
        'currency',
        'start_date',
        'deadline',
        'published_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
            'start_date' => 'date',
            'deadline' => 'date',
            'published_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'project_members'
        )
            ->withPivot([
                'role',
                'status',
                'invited_by',
                'joined_at',
                'left_at',
            ])
            ->withTimestamps();
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(
            Team::class,
            'project_team'
        )
            ->withPivot([
                'is_primary',
                'joined_at',
            ])
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function attachments(): MorphMany
{
    return $this->morphMany(Attachment::class, 'attachable');
}

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
