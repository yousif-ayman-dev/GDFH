<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'commentable_type',
        'commentable_id',
        'parent_id',
        'body',
        'content',
        'edited_at',
        'task_id',
    ];

    protected function casts(): array
    {
        return [
            'edited_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Comment $comment) {
            if ($comment->task_id && ! $comment->commentable_type) {
                $comment->commentable_type = Task::class;
                $comment->commentable_id = $comment->task_id;
            } elseif ($comment->commentable_type === Task::class && ! $comment->task_id) {
                $comment->task_id = $comment->commentable_id;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->latest();
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function isEdited(): bool
    {
        return $this->edited_at !== null;
    }

    /**
     * Parse @username mentions in comment body.
     *
     * @return array<string>
     */
    public function parseMentions(): array
    {
        $text = $this->body ?: $this->content ?: '';
        preg_match_all('/@([a-zA-Z0-9_]+)/', $text, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }
}
