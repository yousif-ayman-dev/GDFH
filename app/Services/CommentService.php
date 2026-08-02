<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CommentService
{
    public function __construct(
        protected ActivityService $activityService
    ) {}

    /**
     * Create a new top-level comment on a polymorphic model.
     */
    public function createComment(User $user, Model $commentable, string $body): Comment
    {
        return DB::transaction(function () use ($user, $commentable, $body) {
            $comment = Comment::create([
                'user_id' => $user->id,
                'commentable_type' => get_class($commentable),
                'commentable_id' => $commentable->getKey(),
                'parent_id' => null,
                'body' => $body,
                'content' => $body,
            ]);

            $mentions = $comment->parseMentions();

            $this->activityService->record(
                $user,
                $commentable,
                'comment_created',
                "أضاف تعليقاً على '{$this->getSubjectName($commentable)}'",
                ['comment_id' => $comment->id, 'mentions' => $mentions]
            );

            return $comment;
        });
    }

    /**
     * Create a threaded reply to an existing comment.
     */
    public function reply(User $user, Comment $parentComment, string $body): Comment
    {
        return DB::transaction(function () use ($user, $parentComment, $body) {
            $comment = Comment::create([
                'user_id' => $user->id,
                'commentable_type' => $parentComment->commentable_type,
                'commentable_id' => $parentComment->commentable_id,
                'parent_id' => $parentComment->id,
                'body' => $body,
                'content' => $body,
            ]);

            $mentions = $comment->parseMentions();

            if ($parentComment->commentable) {
                $this->activityService->record(
                    $user,
                    $parentComment->commentable,
                    'reply_created',
                    "رد على تعليق في '{$this->getSubjectName($parentComment->commentable)}'",
                    ['reply_id' => $comment->id, 'parent_id' => $parentComment->id, 'mentions' => $mentions]
                );
            }

            return $comment;
        });
    }

    /**
     * Update an existing comment body.
     */
    public function updateComment(Comment $comment, string $newBody): Comment
    {
        return DB::transaction(function () use ($comment, $newBody) {
            $comment->update([
                'body' => $newBody,
                'content' => $newBody,
                'edited_at' => now(),
            ]);

            if ($comment->commentable) {
                $this->activityService->record(
                    $comment->user,
                    $comment->commentable,
                    'comment_updated',
                    "عدل تعليقه في '{$this->getSubjectName($comment->commentable)}'",
                    ['comment_id' => $comment->id]
                );
            }

            return $comment->fresh();
        });
    }

    /**
     * Delete a comment.
     */
    public function deleteComment(Comment $comment): void
    {
        DB::transaction(function () use ($comment) {
            $subject = $comment->commentable;

            if ($subject) {
                $this->activityService->record(
                    $comment->user,
                    $subject,
                    'comment_deleted',
                    "حذف تعليقاً من '{$this->getSubjectName($subject)}'",
                    ['comment_id' => $comment->id]
                );
            }

            $comment->delete();
        });
    }

    protected function getSubjectName(Model $subject): string
    {
        if (isset($subject->title)) {
            return $subject->title;
        }

        if (isset($subject->name)) {
            return $subject->name;
        }

        return 'العنصر';
    }
}
