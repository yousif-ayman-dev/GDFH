<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'price',
        'delivery_days',
        'category',
        'skills',
        'status',
        'cover_image',
        'sales_count',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'price' => 'decimal:2',
            'delivery_days' => 'integer',
            'sales_count' => 'integer',
            'rating' => 'decimal:2',
            'skills' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
