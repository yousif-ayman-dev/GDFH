<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProposalService
{
    public function __construct(
        protected ActivityService $activityService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Submit a proposal for a project.
     */
    public function submitProposal(
        User $freelancer,
        Project $project,
        float $bidAmount,
        int $deliveryDays,
        string $coverLetter
    ): Proposal {
        if ((int) $project->owner_id === (int) $freelancer->id) {
            throw new InvalidArgumentException('لا يمكنك تقديم عرض على مشروعك الخاص.');
        }

        $existing = Proposal::where('project_id', $project->id)
            ->where('freelancer_id', $freelancer->id)
            ->first();

        if ($existing) {
            throw new InvalidArgumentException('لقد قمت بتقديم عرض على هذا المشروع سابقاً.');
        }

        return DB::transaction(function () use ($freelancer, $project, $bidAmount, $deliveryDays, $coverLetter) {
            $proposal = Proposal::create([
                'project_id' => $project->id,
                'freelancer_id' => $freelancer->id,
                'bid_amount' => $bidAmount,
                'delivery_days' => $deliveryDays,
                'cover_letter' => $coverLetter,
                'status' => 'pending',
            ]);

            // Notify project owner
            $this->notificationService->sendNotification(
                $project->owner,
                'عرض عمل جديد على مشروعك',
                "قام {$freelancer->name} بتقديم عرض جديد على مشروعك ({$project->title}).",
                route('projects.show', $project)
            );

            return $proposal;
        });
    }

    /**
     * Accept a proposal and generate a contract.
     */
    public function acceptProposal(User $client, Proposal $proposal): Contract
    {
        $project = $proposal->project;

        if ((int) $project->owner_id !== (int) $client->id) {
            throw new InvalidArgumentException('غير مصرح لك بقبول العروض لهذا المشروع.');
        }

        return DB::transaction(function () use ($client, $proposal, $project) {
            // 1. Mark proposal accepted
            $proposal->update(['status' => 'accepted']);

            // 2. Auto-reject other pending proposals for project
            Proposal::where('project_id', $project->id)
                ->where('id', '!=', $proposal->id)
                ->where('status', 'pending')
                ->update(['status' => 'rejected']);

            // 3. Update project status to in_progress
            $project->update(['status' => 'in_progress']);

            // 4. Create Contract
            $contract = Contract::create([
                'project_id' => $project->id,
                'proposal_id' => $proposal->id,
                'client_id' => $client->id,
                'freelancer_id' => $proposal->freelancer_id,
                'title' => 'عقد اتفاقية: ' . $project->title,
                'amount' => $proposal->bid_amount,
                'status' => 'active',
                'start_date' => now(),
            ]);

            // 5. Add freelancer as Project Member if not present
            ProjectMember::firstOrCreate([
                'project_id' => $project->id,
                'user_id' => $proposal->freelancer_id,
            ], [
                'role' => 'member',
                'status' => 'active',
                'invited_by' => $client->id,
                'joined_at' => now(),
            ]);

            // 6. Notifications & Activity Logs
            $this->activityService->logProjectUpdated($client, $project);
            $this->notificationService->sendNotification(
                $proposal->freelancer,
                'تم قبول عرضك وإصدار العقد!',
                "تهانينا! قام العميل {$client->name} بقبول عرضك لبدء مشروع ({$project->title}).",
                route('contracts.show', $contract)
            );

            return $contract;
        });
    }

    /**
     * Reject a proposal.
     */
    public function rejectProposal(User $client, Proposal $proposal): Proposal
    {
        if ((int) $proposal->project->owner_id !== (int) $client->id) {
            throw new InvalidArgumentException('غير مصرح لك برفض هذا العرض.');
        }

        $proposal->update(['status' => 'rejected']);

        return $proposal->fresh();
    }

    /**
     * Withdraw a proposal.
     */
    public function withdrawProposal(User $freelancer, Proposal $proposal): Proposal
    {
        if ((int) $proposal->freelancer_id !== (int) $freelancer->id) {
            throw new InvalidArgumentException('غير مصرح لك بسحب هذا العرض.');
        }

        $proposal->update(['status' => 'withdrawn']);

        return $proposal->fresh();
    }
}
