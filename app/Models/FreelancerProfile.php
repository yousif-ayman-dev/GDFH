<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelancerProfile extends Model
{
    use HasFactory;

    protected $table = 'freelancer_profiles';

    protected $fillable = [
        'user_id',
        'title',
        'bio',
        'hourly_rate',
        'skills',
        'location',
        'rating',
        'reviews_count',
        'completed_projects_count',
        'availability',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'hourly_rate' => 'decimal:2',
            'rating' => 'decimal:2',
            'reviews_count' => 'integer',
            'completed_projects_count' => 'integer',
            'skills' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
