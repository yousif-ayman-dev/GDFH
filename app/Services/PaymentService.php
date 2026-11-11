<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Payment;
use App\Models\PlatformCommission;
use App\Models\User;
use App\Services\Payment\PaymentGatewayManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PaymentService
{
    public function __construct(
        protected PaymentGatewayManager $gatewayManager,
        protected ActivityService $activityService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Initiate a non-custodial payment for a contract.
     */
    public function initiatePayment(User $client, Contract $contract, string $currency = 'USD'): Payment
    {
        if ((int) $contract->client_id !== (int) $client->id) {
            throw new InvalidArgumentException('غير مصرح لك بسداد هذا العقد.');
        }

        if ($contract->isPaid()) {
            throw new InvalidArgumentException('تم سداد هذا العقد سابقاً ولا يمكن إعادة الدفع.');
        }

        return DB::transaction(function () use ($client, $contract, $currency) {
            $idempotencyKey = 'PAY-CONTRACT-' . $contract->id . '-CLIENT-' . $client->id;

            // Check if existing pending payment exists with idempotency key
            $existingPayment = Payment::where('idempotency_key', $idempotencyKey)
                ->where('status', 'pending')
                ->first();

            if ($existingPayment) {
                return $existingPayment;
            }

            $amount = (float) $contract->amount;
            $providerDriver = $this->gatewayManager->driver('sandbox');

            $payment = Payment::create([
                'contract_id' => $contract->id,
                'client_id' => $client->id,
                'amount' => $amount,
                'currency' => strtoupper($currency),
                'provider' => 'sandbox',
                'status' => 'pending',
                'idempotency_key' => $idempotencyKey,
            ]);

            $intentResult = $providerDriver->createPaymentIntent($contract, $amount, $currency, [
                'payment_id' => $payment->id,
            ]);

            $payment->update([
                'provider_payment_id' => $intentResult['provider_payment_id'],
                'metadata' => [
                    'checkout_url' => $intentResult['checkout_url'],
                    'created_by' => $client->id,
                ],
            ]);

            $contract->update(['payment_status' => 'pending']);

            return $payment->fresh();
        });
    }

    /**
     * Process payment verification / callback safely and idempotently.
     */
    public function processPaymentCallback(Payment $payment, string $status = 'paid', ?string $simulatedReference = null): Payment
    {
        return DB::transaction(function () use ($payment, $status, $simulatedReference) {
            // Lock row for update to prevent concurrent double-callbacks
            $lockedPayment = Payment::where('id', $payment->id)->lockForUpdate()->firstOrFail();

            if ($lockedPayment->status === 'paid') {
                // Idempotent response: already paid
                return $lockedPayment;
            }

            $contract = $lockedPayment->contract;

            if ($status === 'paid') {
                $lockedPayment->update([
                    'status' => 'paid',
                    'provider_reference' => $simulatedReference ?: ('REF-' . Str::upper(Str::random(10))),
                    'paid_at' => now(),
                ]);

                $contract->update(['payment_status' => 'paid']);

                // Calculate Platform Commission server-side
                $rate = (float) config('monetization.commission_rate', 0.10);
                $grossAmount = (float) $lockedPayment->amount;
                $commissionAmount = round($grossAmount * $rate, 2);
                $freelancerAmount = round($grossAmount - $commissionAmount, 2);

                PlatformCommission::firstOrCreate([
                    'payment_id' => $lockedPayment->id,
                ], [
                    'contract_id' => $contract->id,
                    'rate' => $rate,
                    'gross_amount' => $grossAmount,
                    'commission_amount' => $commissionAmount,
                    'freelancer_amount' => $freelancerAmount,
                    'currency' => $lockedPayment->currency,
                    'status' => 'calculated',
                ]);

                // Activity Log & Notifications
                $this->activityService->logProjectUpdated($lockedPayment->client, $contract->project);

                $formattedFreelancerAmount = number_format($freelancerAmount, 2);
                $formattedGrossAmount = number_format($grossAmount, 2);

                $this->notificationService->sendNotification(
                    $contract->freelancer,
                    'تم تأكيد سداد العقد 💳',
                    "قام العميل ({$lockedPayment->client->name}) بسداد العقد الخاص بـ ({$contract->title}). الصافي المخصص لك: \${$formattedFreelancerAmount}.",
                    route('contracts.show', $contract)
                );

                $this->notificationService->sendNotification(
                    $lockedPayment->client,
                    'تم استلام الدفع بنجاح 🟢',
                    "تم إتمام دفع مبلغ \${$formattedGrossAmount} للعقد ({$contract->title}) بنجاح.",
                    route('contracts.show', $contract)
                );
            } else {
                $lockedPayment->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                ]);

                $contract->update(['payment_status' => 'failed']);
            }

            return $lockedPayment->fresh(['commission', 'contract']);
        });
    }

    /**
     * Process refund for a paid transaction.
     */
    public function refundPayment(User $actor, Payment $payment, ?float $amount = null, ?string $reason = null): Payment
    {
        if (! $actor->is_admin && (int) $payment->client_id !== (int) $actor->id) {
            throw new InvalidArgumentException('غير مصرح لك بطلب استرداد هذا الدفع.');
        }

        return DB::transaction(function () use ($payment, $amount, $reason) {
            $lockedPayment = Payment::where('id', $payment->id)->lockForUpdate()->firstOrFail();

            if ($lockedPayment->status !== 'paid') {
                throw new InvalidArgumentException('يمكن فقط استرداد المدفوعات المؤكدة بنجاح.');
            }

            $providerDriver = $this->gatewayManager->driver($lockedPayment->provider);
            $providerDriver->refundPayment($lockedPayment->provider_payment_id ?? '', $amount, $reason);

            $lockedPayment->update([
                'status' => 'refunded',
                'refunded_at' => now(),
            ]);

            if ($lockedPayment->commission) {
                $lockedPayment->commission->update(['status' => 'refunded']);
            }

            if ($lockedPayment->contract) {
                $lockedPayment->contract->update(['payment_status' => 'refunded']);
            }

            return $lockedPayment->fresh();
        });
    }
}
