<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Contract;
use Illuminate\Support\Str;

class FakePaymentGateway implements PaymentGatewayInterface
{
    /**
     * Create a payment intent / sandbox session.
     */
    public function createPaymentIntent(Contract $contract, float $amount, string $currency, array $options = []): array
    {
        $providerPaymentId = 'SANDBOX-PAY-' . Str::upper(Str::random(12));
        $simulatedStatus = $options['simulated_status'] ?? 'pending';

        return [
            'provider' => 'sandbox',
            'provider_payment_id' => $providerPaymentId,
            'checkout_url' => route('payments.checkout', ['payment' => $options['payment_id'] ?? 0]),
            'status' => $simulatedStatus,
            'currency' => strtoupper($currency),
            'amount' => $amount,
        ];
    }

    /**
     * Verify payment status in the sandbox.
     */
    public function verifyPayment(string $providerPaymentId): array
    {
        return [
            'verified' => true,
            'status' => 'paid',
            'provider_reference' => 'REF-' . Str::upper(Str::random(8)),
        ];
    }

    /**
     * Get payment status.
     */
    public function getPaymentStatus(string $providerPaymentId): string
    {
        return 'paid';
    }

    /**
     * Refund payment in sandbox.
     */
    public function refundPayment(string $providerPaymentId, ?float $amount = null, ?string $reason = null): array
    {
        return [
            'refunded' => true,
            'status' => 'refunded',
            'refund_reference' => 'RFD-' . Str::upper(Str::random(8)),
            'amount' => $amount,
            'reason' => $reason ?? 'Requested by user/admin',
        ];
    }
}
