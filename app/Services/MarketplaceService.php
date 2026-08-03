<?php

namespace App\Services;

use App\Models\FreelancerProfile;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MarketplaceService
{
    /**
     * Get paginated freelancers directory.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getFreelancers(array $filters = []): LengthAwarePaginator
    {
        $query = User::query()
            ->where(function ($q) {
                $q->where('account_type', 'freelancer')
                  ->orWhereHas('freelancerProfile');
            })
            ->with('freelancerProfile');

        if (! empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('username', 'like', $search)
                  ->orWhereHas('freelancerProfile', function ($pq) use ($search) {
                      $pq->where('title', 'like', $search)
                        ->orWhere('bio', 'like', $search);
                  });
            });
        }

        if (! empty($filters['min_rate'])) {
            $query->whereHas('freelancerProfile', function ($q) use ($filters) {
                $q->where('hourly_rate', '>=', $filters['min_rate']);
            });
        }

        if (! empty($filters['max_rate'])) {
            $query->whereHas('freelancerProfile', function ($q) use ($filters) {
                $q->where('hourly_rate', '<=', $filters['max_rate']);
            });
        }

        return $query->latest()->paginate(12);
    }

    /**
     * Get paginated services directory.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getServices(array $filters = []): LengthAwarePaginator
    {
        $query = Service::query()
            ->where('status', 'active')
            ->with(['user.freelancerProfile']);

        if (! empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        return $query->latest()->paginate(12);
    }

    /**
     * Get paginated public marketplace jobs / projects.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPublicProjects(array $filters = []): LengthAwarePaginator
    {
        $query = Project::query()
            ->whereIn('visibility', ['marketplace', 'public'])
            ->with(['owner', 'team']);

        if (! empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        return $query->latest()->paginate(12);
    }
}
