<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $project): void {
            if (empty($project->slug)) {
                $project->slug = self::generateUniqueSlug($project->title);
            }
        });

        static::saving(function (self $project): void {
            if (empty($project->slug)) {
                $project->slug = self::generateUniqueSlug($project->title);
            }

            if ($project->isDirty('title') && ! $project->isDirty('slug')) {
                $project->slug = self::generateUniqueSlug($project->title);
            }
        });
    }

    protected $fillable = [
        'team_id',
        'owner_id',
        'title',
        'slug',
        'description',
        'category',
        'visibility',
        'status',
        'budget',
        'budget_type',
        'budget_min',
        'budget_max',
        'currency',
        'start_date',
        'due_date',
        'deadline',
        'published_at',
        'completed_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
            'start_date' => 'date',
            'due_date' => 'date',
            'deadline' => 'date',
            'published_at' => 'datetime',
            'completed_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
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

    public function memberRecords(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
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

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    protected static function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title) ?: 'project';
        $suffix = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 6);
        $slug = $baseSlug . '-' . $suffix;

        while (static::query()->where('slug', $slug)->exists()) {
            $suffix = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 6);
            $slug = $baseSlug . '-' . $suffix;
        }

        return $slug;
    }
}
