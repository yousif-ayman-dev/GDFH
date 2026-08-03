<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityService
{
    /**
     * Centralized activity recorder.
     */
    public function record(
        ?User $user,
        Model $subject,
        string $event,
        string $description,
        array $properties = []
    ): Activity {
        $activity = Activity::create([
            'user_id' => $user?->id,
            'subject_type' => get_class($subject),
            'subject_id' => $subject->getKey(),
            'event' => $event,
            'description' => $description,
            'properties' => $properties,
        ]);

        try {
            app(NotificationService::class)->createFromActivity($activity);
        } catch (\Throwable $e) {
            // Silently ignore notification failure during activity recording
        }

        return $activity;
    }

    public function logTeamCreated(?User $user, Team $team): Activity
    {
        return $this->record(
            $user,
            $team,
            'team_created',
            "أنشأ الفريق '{$team->name}'",
            ['team_id' => $team->id, 'team_name' => $team->name]
        );
    }

    public function logMemberJoined(?User $user, Team $team, User $member): Activity
    {
        return $this->record(
            $user,
            $team,
            'member_joined',
            "انضم العضو '{$member->name}' إلى الفريق",
            ['member_id' => $member->id, 'member_name' => $member->name]
        );
    }

    public function logInvitationAccepted(?User $user, TeamInvitation $invitation): Activity
    {
        $team = $invitation->team;

        return $this->record(
            $user,
            $team ?? $invitation,
            'invitation_accepted',
            "قبِل الدعوة للانضمام إلى الفريق",
            ['invitation_id' => $invitation->id, 'role' => $invitation->role]
        );
    }

    public function logProjectCreated(?User $user, Project $project): Activity
    {
        return $this->record(
            $user,
            $project,
            'project_created',
            "أنشأ المشروع '{$project->title}'",
            ['project_id' => $project->id, 'title' => $project->title]
        );
    }

    public function logProjectUpdated(?User $user, Project $project): Activity
    {
        return $this->record(
            $user,
            $project,
            'project_updated',
            "تم تحديث بيانات المشروع '{$project->title}'",
            ['project_id' => $project->id, 'title' => $project->title]
        );
    }

    public function logProjectArchived(?User $user, Project $project): Activity
    {
        return $this->record(
            $user,
            $project,
            'project_archived',
            "أرشف المشروع '{$project->title}'",
            ['project_id' => $project->id]
        );
    }

    public function logProjectRestored(?User $user, Project $project): Activity
    {
        return $this->record(
            $user,
            $project,
            'project_restored',
            "استعاد المشروع '{$project->title}' من الأرشيف",
            ['project_id' => $project->id]
        );
    }

    public function logProjectStatusChanged(?User $user, Project $project, string $oldStatus, string $newStatus): Activity
    {
        return $this->record(
            $user,
            $project,
            'project_status_changed',
            "غير حالة المشروع من '{$oldStatus}' إلى '{$newStatus}'",
            ['old_status' => $oldStatus, 'new_status' => $newStatus]
        );
    }

    public function logTaskCreated(?User $user, Task $task): Activity
    {
        return $this->record(
            $user,
            $task,
            'task_created',
            "أنشأ المهمة '{$task->title}'",
            ['task_id' => $task->id, 'project_id' => $task->project_id]
        );
    }

    public function logTaskAssigned(?User $user, Task $task, User $assignee): Activity
    {
        return $this->record(
            $user,
            $task,
            'task_assigned',
            "عين المهمة '{$task->title}' للعضو '{$assignee->name}'",
            ['task_id' => $task->id, 'assignee_id' => $assignee->id]
        );
    }

    public function logTaskCompleted(?User $user, Task $task): Activity
    {
        return $this->record(
            $user,
            $task,
            'task_completed',
            "أكمل المهمة '{$task->title}'",
            ['task_id' => $task->id, 'project_id' => $task->project_id]
        );
    }
}
