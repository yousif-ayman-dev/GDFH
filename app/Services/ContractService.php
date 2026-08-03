<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ContractService
{
    public function __construct(
        protected ActivityService $activityService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Get all active & completed contracts for user.
     */
    public function getUserContracts(User $user): Collection
    {
        return Contract::query()
            ->where('client_id', $user->id)
            ->orWhere('freelancer_id', $user->id)
            ->with(['client', 'freelancer', 'project', 'proposal'])
            ->latest()
            ->get();
    }

    /**
     * Mark contract completed.
     */
    public function completeContract(User $user, Contract $contract): Contract
    {
        if ((int) $contract->client_id !== (int) $user->id && (int) $contract->freelancer_id !== (int) $user->id) {
            throw new InvalidArgumentException('غير مصرح لك بإتمام هذا العقد.');
        }

        return DB::transaction(function () use ($user, $contract) {
            $contract->update([
                'status' => 'completed',
                'completed_at' => now(),
                'end_date' => now(),
            ]);

            if ($contract->project) {
                $contract->project->update(['status' => 'completed']);
            }

            $otherUser = (int) $contract->client_id === (int) $user->id ? $contract->freelancer : $contract->client;

            if ($otherUser) {
                $this->notificationService->sendNotification(
                    $otherUser,
                    'تم إتمام العقد وتأكيد التسليم!',
                    "تم إغلاق وإتمام العقد الخاص بـ ({$contract->title}) بنجاح.",
                    route('contracts.show', $contract)
                );
            }

            return $contract->fresh();
        });
    }
}
