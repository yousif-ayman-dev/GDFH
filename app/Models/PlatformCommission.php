<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformCommission extends Model
{
    use HasFactory;

    protected $table = 'platform_commissions';

    protected $fillable = [
        'payment_id',
        'contract_id',
        'rate',
        'gross_amount',
        'commission_amount',
        'freelancer_amount',
        'currency',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'payment_id' => 'integer',
            'contract_id' => 'integer',
            'rate' => 'decimal:4',
            'gross_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'freelancer_amount' => 'decimal:2',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
