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

    /**
     * Create a new marketplace service listing.
     *
     * @param  array<string, mixed>  $data
     */
    public function createService(User $user, array $data, mixed $coverFile = null): Service
    {
        $baseSlug = \Illuminate\Support\Str::slug($data['title']);
        $slug = $baseSlug;
        $counter = 1;

        while (Service::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $coverImagePath = null;
        if ($coverFile) {
            $coverImagePath = $coverFile->store('services', 'public');
        }

        $service = Service::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'],
            'price' => $data['price'],
            'delivery_days' => $data['delivery_days'],
            'category' => $data['category'] ?? 'تطوير البرمجيات',
            'skills' => $data['skills'] ?? [],
            'status' => 'active',
            'cover_image' => $coverImagePath,
        ]);

        if ($user->account_type !== 'freelancer') {
            $user->update(['account_type' => 'freelancer']);
        }

        return $service;
    }

    /**
     * Update an existing service listing.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateService(Service $service, array $data, mixed $coverFile = null): Service
    {
        if ($coverFile) {
            if ($service->cover_image && \Illuminate\Support\Facades\Storage::disk('public')->exists($service->cover_image)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($service->cover_image);
            }
            $data['cover_image'] = $coverFile->store('services', 'public');
        }

        $service->update($data);

        return $service->fresh();
    }

    /**
     * Delete a service listing.
     */
    public function deleteService(Service $service): void
    {
        if ($service->cover_image && \Illuminate\Support\Facades\Storage::disk('public')->exists($service->cover_image)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($service->cover_image);
        }

        $service->delete();
    }

    /**
     * Upsert freelancer profile for user.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateFreelancerProfile(User $user, array $data): FreelancerProfile
    {
        $profile = FreelancerProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'title' => $data['title'],
                'hourly_rate' => $data['hourly_rate'],
                'location' => $data['location'] ?? null,
                'availability' => $data['availability'] ?? 'available',
                'skills' => $data['skills'] ?? [],
                'bio' => $data['bio'] ?? null,
            ]
        );

        if ($user->account_type !== 'freelancer') {
            $user->update(['account_type' => 'freelancer']);
        }

        return $profile;
    }

    /**
     * Order a marketplace service using the existing Contract model.
     */
    public function orderService(User $client, Service $service): \App\Models\Contract
    {
        if ((int) $client->id === (int) $service->user_id) {
            throw new \InvalidArgumentException('لا يمكنك شراء خدمتك الخاصة.');
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($client, $service) {
            $project = \App\Models\Project::create([
                'owner_id' => $client->id,
                'title' => 'مشروع خدمة: ' . $service->title,
                'description' => 'مشروع منفذ للخدمة المشتراة: ' . $service->title,
                'status' => 'in_progress',
                'visibility' => 'private',
            ]);

            $contract = \App\Models\Contract::create([
                'project_id' => $project->id,
                'client_id' => $client->id,
                'freelancer_id' => $service->user_id,
                'title' => 'طلب خدمة: ' . $service->title,
                'amount' => $service->price,
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addDays($service->delivery_days),
            ]);

            $service->increment('sales_count');

            if ($service->user?->freelancerProfile) {
                $service->user->freelancerProfile->increment('completed_projects_count');
            }

            $notificationService = app(NotificationService::class);
            $notificationService->sendNotification(
                $service->user,
                'طلب خدمة جديد!',
                "قام المشتري ({$client->name}) بطلب خدمتك ({$service->title}).",
                route('contracts.show', $contract)
            );

            return $contract;
        });
    }
}
