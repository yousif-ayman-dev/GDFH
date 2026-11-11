<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use InvalidArgumentException;

class PaymentGatewayManager
{
    /**
     * Resolve the active payment gateway driver.
     */
    public function driver(?string $name = null): PaymentGatewayInterface
    {
        $driverName = $name ?: config('services.payment.default', 'sandbox');

        return match ($driverName) {
            'sandbox', 'fake' => new FakePaymentGateway(),
            default => throw new InvalidArgumentException("مزوّد الدفع المحدد ({$driverName}) غير مدعوم حالياً."),
        };
    }
}
