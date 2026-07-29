<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Team extends Model
{
    protected static function booted(): void
    {
        static::creating(function (self $team): void {
            if (empty($team->slug)) {
                $team->slug = self::generateUniqueSlug($team->name);
            }
        });

        static::saving(function (self $team): void {
            if (empty($team->slug)) {
                $team->slug = self::generateUniqueSlug($team->name);
            }

            if ($team->isDirty('name') && ! $team->isDirty('slug')) {
                $team->slug = self::generateUniqueSlug($team->name);
            }
        });
    }

    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'slug',
        'type',
        'visibility',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'team_members'
        )
        ->withPivot([
            'role',
            'status',
            'joined_at',
            'invited_by',
        ])
        ->withTimestamps();
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(
            Project::class,
            'project_team'
        )
        ->withPivot([
            'is_primary',
            'joined_at',
        ])
        ->withTimestamps();
    }

    protected static function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'team';
        $suffix = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 6);
        $slug = $baseSlug . '-' . $suffix;

        while (static::query()->where('slug', $slug)->exists()) {
            $suffix = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 6);
            $slug = $baseSlug . '-' . $suffix;
        }

        return $slug;
    }
}
