<?php

namespace Tests\Feature;

use App\Models\FreelancerProfile;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use App\Services\MarketplaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterpriseMarketplaceDiscoveryEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function createOnboardedUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'onboarded_at' => now(),
            'username' => 'user_' . strtolower(Str::random(8)),
            'account_type' => 'client',
        ], $attributes));
    }

    public function test_marketplace_directory_rendering_for_authenticated_user(): void
    {
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->get(route('marketplace.index'));

        $response->assertStatus(200);
        $response->assertSee('سوق المستقلين والخدمات البرمجية');
        $response->assertSee('الخدمات');
        $response->assertSee('المستقلون');
        $response->assertSee('المشاريع المفتوحة');
    }

    public function test_unauthenticated_user_cannot_access_marketplace(): void
    {
        $response = $this->get(route('marketplace.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_freelancer_directory_listing_and_filtering(): void
    {
        $user = $this->createOnboardedUser();
        $freelancer = $this->createOnboardedUser([
            'name' => 'John Freelancer',
            'account_type' => 'freelancer',
        ]);

        FreelancerProfile::create([
            'user_id' => $freelancer->id,
            'title' => 'Laravel Fullstack Expert',
            'hourly_rate' => 45.00,
        ]);

        $service = app(MarketplaceService::class);
        $results = $service->getFreelancers(['search' => 'Laravel']);

        $this->assertEquals(1, $results->total());
        $this->assertEquals('John Freelancer', $results->first()->name);
    }

    public function test_services_catalog_listing_and_filtering(): void
    {
        $user = $this->createOnboardedUser();

        $serviceObj = Service::create([
            'user_id' => $user->id,
            'title' => 'API Development in Laravel 12',
            'slug' => 'api-development-in-laravel-12',
            'description' => 'Professional RESTful API backend service.',
            'price' => 150.00,
            'delivery_days' => 3,
            'category' => 'تطوير البرمجيات',
            'status' => 'active',
        ]);

        $service = app(MarketplaceService::class);
        $results = $service->getServices(['search' => 'Laravel 12']);

        $this->assertEquals(1, $results->total());
        $this->assertEquals('API Development in Laravel 12', $results->first()->title);
    }

    public function test_public_projects_job_postings_listing(): void
    {
        $user = $this->createOnboardedUser();

        $project = Project::factory()->create([
            'owner_id' => $user->id,
            'title' => 'Public Marketplace Job Posting',
            'visibility' => 'marketplace',
        ]);

        $service = app(MarketplaceService::class);
        $results = $service->getPublicProjects();

        $this->assertEquals(1, $results->total());
        $this->assertEquals('Public Marketplace Job Posting', $results->first()->title);
    }

    public function test_service_detail_page_rendering(): void
    {
        $user = $this->createOnboardedUser();

        $serviceObj = Service::create([
            'user_id' => $user->id,
            'title' => 'Vue.js Dashboard Integration',
            'slug' => 'vuejs-dashboard-integration',
            'description' => 'Sleek dark mode dashboards.',
            'price' => 200.00,
            'delivery_days' => 5,
            'category' => 'تطوير البرمجيات',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('marketplace.services.show', $serviceObj));

        $response->assertStatus(200);
        $response->assertSee('Vue.js Dashboard Integration');
        $response->assertSee('$200.00');
    }

    public function test_freelancer_showcase_page_rendering(): void
    {
        $user = $this->createOnboardedUser();
        $freelancer = $this->createOnboardedUser([
            'name' => 'Specialist Engineer',
            'account_type' => 'freelancer',
        ]);

        $response = $this->actingAs($user)->get(route('marketplace.freelancers.show', $freelancer));

        $response->assertStatus(200);
        $response->assertSee('Specialist Engineer');
    }
}
