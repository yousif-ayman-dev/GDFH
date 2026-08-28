<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'google_id',
        'password',
        'username',
        'account_type',
        'onboarded_at',
        'avatar_path',
        'bio',
        'notification_preferences',
        'is_admin',
        'is_banned',
        'is_verified',
        'verification_badge_at',
        'connects_balance',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'onboarded_at' => 'datetime',
            'notification_preferences' => 'array',
            'is_admin' => 'boolean',
            'is_banned' => 'boolean',
        ];
    }

    /**
     * Check if user is a system administrator.
     */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * Check if user account is banned.
     */
    public function isBanned(): bool
    {
        return (bool) $this->is_banned;
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        if (str_starts_with($this->avatar_path, 'http://') || str_starts_with($this->avatar_path, 'https://')) {
            return $this->avatar_path;
        }

        return asset('storage/' . ltrim($this->avatar_path, '/'));
    }

    public function getNotificationPreference(string $key, bool $default = true): bool
    {
        $prefs = $this->notification_preferences ?? [];

        return isset($prefs[$key]) ? (bool) $prefs[$key] : $default;
    }

    public function activities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Check if user is a freelancer.
     */
    public function isFreelancer(): bool
    {
        return $this->account_type === 'freelancer';
    }

    /**
     * Check if user is a client.
     */
    public function isClient(): bool
    {
        return $this->account_type === 'client';
    }

    /**
     * Check if user has completed onboarding.
     */
    public function hasCompletedOnboarding(): bool
    {
        return $this->onboarded_at !== null
            && ! empty($this->username)
            && ! empty($this->account_type);
    }

    /**
     * Projects owned by the user.
     */
    public function ownedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    /**
     * Portfolio items created by freelancer.
     */
    public function portfolioItems(): HasMany
    {
        return $this->hasMany(PortfolioItem::class)->latest();
    }

    /**
     * Project membership records for the user.
     */
    public function projectMemberships(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    /**
     * Projects where the user is a member.
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(
            Project::class,
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

    /**
     * Teams owned by the user.
     */
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    /**
     * Team membership records for the user.
     */
    public function teamMemberships(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    /**
     * Teams where the user is a member.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(
            Team::class,
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

    /**
     * Freelancer profile.
     */
    public function freelancerProfile(): HasOne
    {
        return $this->hasOne(FreelancerProfile::class);
    }

    /**
     * Offered services.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Proposals submitted by user.
     */
    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'freelancer_id');
    }

    /**
     * Contracts where user is the client.
     */
    public function contractsAsClient(): HasMany
    {
        return $this->hasMany(Contract::class, 'client_id');
    }

    /**
     * Contracts where user is the freelancer.
     */
    public function contractsAsFreelancer(): HasMany
    {
        return $this->hasMany(Contract::class, 'freelancer_id');
    }

    /**
     * Reviews submitted by user (reviews written).
     */
    public function reviewsWritten(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    /**
     * Reviews received by user.
     */
    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    /**
     * Tasks created by the user.
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * Tasks assigned to the user.
     */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Attachments uploaded by the user.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'uploaded_by');
    }

    /**
     * Comments created by the user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'user_id');
    }

    /**
     * App notifications received by the user.
     */
    public function appNotifications(): HasMany
    {
        return $this->hasMany(AppNotification::class, 'user_id');
    }

    public function unreadNotificationsCount(): int
    {
        return $this->appNotifications()->unread()->count();
    }

    /**
     * Team invitations sent by the user.
     */
    public function sentTeamInvitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class, 'inviter_id');
    }

    /**
     * Team invitations received by the user.
     */
    public function receivedTeamInvitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class, 'invitee_id');
    }
}
