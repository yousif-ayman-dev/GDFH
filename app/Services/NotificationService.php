<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\AppNotification;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Send notification to a single recipient.
     */
    public function send(
        User $recipient,
        string $type,
        string $title,
        string $description,
        ?User $sender = null,
        ?string $actionUrl = null,
        string $priority = 'normal',
        array $data = []
    ): AppNotification {
        return AppNotification::create([
            'user_id' => $recipient->id,
            'sender_id' => $sender?->id,
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'action_url' => $actionUrl,
            'priority' => $priority,
            'data' => $data,
            'read_at' => null,
        ]);
    }

    /**
     * Send notification to multiple recipients.
     *
     * @param  iterable<User>  $recipients
     * @return Collection<int, AppNotification>
     */
    public function sendToMany(
        iterable $recipients,
        string $type,
        string $title,
        string $description,
        ?User $sender = null,
        ?string $actionUrl = null,
        string $priority = 'normal',
        array $data = []
    ): Collection {
        $created = collect();

        foreach ($recipients as $recipient) {
            if ($sender && $recipient->id === $sender->id) {
                continue;
            }

            $created->push($this->send(
                $recipient,
                $type,
                $title,
                $description,
                $sender,
                $actionUrl,
                $priority,
                $data
            ));
        }

        return $created;
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(AppNotification $notification): AppNotification
    {
        $notification->markAsRead();

        return $notification->fresh();
    }

    /**
     * Mark all unread notifications for a user as read.
     */
    public function markAllAsRead(User $user): int
    {
        return AppNotification::where('user_id', $user->id)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Get unread notifications count for a user.
     */
    public function unreadCount(User $user): int
    {
        return AppNotification::where('user_id', $user->id)
            ->unread()
            ->count();
    }

    /**
     * Delete a notification.
     */
    public function delete(AppNotification $notification): void
    {
        $notification->delete();
    }

    /**
     * Automatically generate notifications from Activity records.
     */
    public function createFromActivity(Activity $activity): ?AppNotification
    {
        $sender = $activity->user;
        $event = $activity->event;
        $subject = $activity->subject;

        if (! $subject) {
            return null;
        }

        switch ($event) {
            case 'team_created':
                if ($subject instanceof Team && $subject->owner) {
                    return $this->send(
                        $subject->owner,
                        'team_created',
                        'تم إنشاء الفريق بنجاح',
                        "تم إنشاء الفريق '{$subject->name}' بنجاح",
                        $sender,
                        route('teams.show', $subject)
                    );
                }
                break;

            case 'invitation_accepted':
                if ($subject instanceof TeamInvitation && $subject->inviter) {
                    return $this->send(
                        $subject->inviter,
                        'invitation_accepted',
                        'تم قبول دعوة الفريق',
                        "قبِل المستخدم '{$sender?->name}' دعوتك للانضمام إلى الفريق",
                        $sender,
                        route('teams.show', $subject->team_id)
                    );
                }
                break;

            case 'project_created':
                if ($subject instanceof Project && $subject->team) {
                    $teamMembers = $subject->team->members;
                    $this->sendToMany(
                        $teamMembers,
                        'project_created',
                        'مشروع جديد في الفريق',
                        "تمت إضافة مشروع جديد '{$subject->title}' إلى فريقك",
                        $sender,
                        route('projects.show', $subject)
                    );
                }
                break;

            case 'task_created':
                if ($subject instanceof Task && $subject->project && $subject->project->owner) {
                    if ($subject->project->owner_id !== $sender?->id) {
                        return $this->send(
                            $subject->project->owner,
                            'task_created',
                            'مهمة جديدة في المشروع',
                            "تمت إضافة مهمة '{$subject->title}' في مشروعك '{$subject->project->title}'",
                            $sender,
                            route('projects.tasks.show', [$subject->project, $subject])
                        );
                    }
                }
                break;

            case 'task_assigned':
                if ($subject instanceof Task) {
                    $assigneeId = $activity->properties['assignee_id'] ?? $subject->assigned_to;
                    if ($assigneeId && $assignee = User::find($assigneeId)) {
                        return $this->send(
                            $assignee,
                            'task_assigned',
                            'تم إسناد مهمة جديدة إليك',
                            "تم إسناد المهمة '{$subject->title}' إليك في مشروع '{$subject->project?->title}'",
                            $sender,
                            route('projects.tasks.show', [$subject->project_id, $subject->id]),
                            'high'
                        );
                    }
                }
                break;

            case 'task_completed':
                if ($subject instanceof Task && $subject->creator) {
                    if ($subject->creator->id !== $sender?->id) {
                        return $this->send(
                            $subject->creator,
                            'task_completed',
                            'اكتملت المهمة',
                            "أكمل '{$sender?->name}' المهمة '{$subject->title}'",
                            $sender,
                            route('projects.tasks.show', [$subject->project, $subject])
                        );
                    }
                }
                break;

            case 'comment_created':
                if ($subject instanceof Project && $subject->owner_id !== $sender?->id) {
                    return $this->send(
                        $subject->owner,
                        'comment_added',
                        'تعليق جديد على المشروع',
                        "أضاف '{$sender?->name}' تعليقاً على مشروعك '{$subject->title}'",
                        $sender,
                        route('projects.show', $subject)
                    );
                }
                break;

            case 'attachment_uploaded':
                if ($subject instanceof Project && $subject->owner_id !== $sender?->id) {
                    return $this->send(
                        $subject->owner,
                        'attachment_uploaded',
                        'تم رفع مرفق جديد',
                        "رفع '{$sender?->name}' مرفقاً جديداً في مشروعك '{$subject->title}'",
                        $sender,
                        route('projects.show', $subject)
                    );
                }
                break;
        }

        return null;
    }
}
