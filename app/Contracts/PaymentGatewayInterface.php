<?php

namespace App\Contracts;

use App\Models\Contract;
use App\Models\Payment;

interface PaymentGatewayInterface
{
    /**
     * Create a payment intent / checkout session for a contract.
     *
     * @param Contract $contract
     * @param float $amount
     * @param string $currency
     * @param array<string, mixed> $options
     * @return array<string, mixed> Returns provider details (provider_payment_id, checkout_url, status)
     */
    public function createPaymentIntent(Contract $contract, float $amount, string $currency, array $options = []): array;

    /**
     * Verify a payment status with the external gateway.
     *
     * @param string $providerPaymentId
     * @return array<string, mixed> Returns verification details (verified, status, provider_reference)
     */
    public function verifyPayment(string $providerPaymentId): array;

    /**
     * Fetch current payment status.
     *
     * @param string $providerPaymentId
     * @return string
     */
    public function getPaymentStatus(string $providerPaymentId): string;

    /**
     * Process a refund via the payment provider.
     *
     * @param string $providerPaymentId
     * @param float|null $amount
     * @param string|null $reason
     * @return array<string, mixed>
     */
    public function refundPayment(string $providerPaymentId, ?float $amount = null, ?string $reason = null): array;
}
