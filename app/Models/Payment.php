<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'contract_id',
        'client_id',
        'amount',
        'currency',
        'provider',
        'provider_payment_id',
        'provider_reference',
        'status',
        'idempotency_key',
        'paid_at',
        'failed_at',
        'refunded_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'contract_id' => 'integer',
            'client_id' => 'integer',
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
            'refunded_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function commission(): HasOne
    {
        return $this->hasOne(PlatformCommission::class, 'payment_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isRefunded(): bool
    {
        return in_array($this->status, ['refunded', 'partially_refunded']);
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
