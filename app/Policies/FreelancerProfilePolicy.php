<?php

namespace App\Policies;

use App\Models\FreelancerProfile;
use App\Models\User;

class FreelancerProfilePolicy
{
    /**
     * Determine whether the user can view the freelancer profile.
     */
    public function view(?User $user, FreelancerProfile $profile): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the freelancer profile.
     */
    public function update(User $user, FreelancerProfile $profile): bool
    {
        return (int) $user->id === (int) $profile->user_id;
    }
}
