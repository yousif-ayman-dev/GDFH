<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterpriseMarketplaceCrudTest extends TestCase
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

    public function test_authenticated_user_can_create_new_service(): void
    {
        Storage::fake('public');

        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->post(route('marketplace.services.store'), [
            'title' => 'تطوير موقع إلكتروني احترافي بـ Laravel 12',
            'description' => 'تقديم خدمة تطوير المواقع والتطبيقات الإلكترونية المتكاملة بمواصفات عالية وتصميم متجاوب.',
            'price' => 250.00,
            'delivery_days' => 5,
            'category' => 'تطوير البرمجيات',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('services', [
            'user_id' => $user->id,
            'title' => 'تطوير موقع إلكتروني احترافي بـ Laravel 12',
            'price' => 250.00,
        ]);
        $this->assertEquals('freelancer', $user->fresh()->account_type);
    }

    public function test_service_owner_can_update_their_service(): void
    {
        $user = $this->createOnboardedUser(['account_type' => 'freelancer']);
        $service = Service::create([
            'user_id' => $user->id,
            'title' => 'عنوان الخدمة القديم',
            'slug' => 'old-service-title',
            'description' => 'وصف قديم للخدمة يتجاوز الحد الأدنى المطلوب.',
            'price' => 100.00,
            'delivery_days' => 3,
            'category' => 'تطوير البرمجيات',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->put(route('marketplace.services.update', $service), [
            'title' => 'عنوان الخدمة المعدل الجديد',
            'description' => 'وصف معدل للخدمة يتجاوز الحد الأدنى المطلوب.',
            'price' => 150.00,
            'delivery_days' => 4,
            'category' => 'تطوير البرمجيات',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('marketplace.services.show', $service));
        $this->assertEquals('عنوان الخدمة المعدل الجديد', $service->fresh()->title);
        $this->assertEquals(150.00, $service->fresh()->price);
    }

    public function test_service_owner_can_delete_their_service(): void
    {
        $user = $this->createOnboardedUser(['account_type' => 'freelancer']);
        $service = Service::create([
            'user_id' => $user->id,
            'title' => 'خدمة سيتم حذفها',
            'slug' => 'service-to-delete',
            'description' => 'وصف خدمة سيتم حذفها يتجاوز الحد الأدنى.',
            'price' => 50.00,
            'delivery_days' => 2,
            'category' => 'تطوير البرمجيات',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->delete(route('marketplace.services.destroy', $service));

        $response->assertRedirect(route('marketplace.index', ['tab' => 'services']));
        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_unauthorized_user_cannot_update_or_delete_other_users_service(): void
    {
        $owner = $this->createOnboardedUser();
        $stranger = $this->createOnboardedUser();

        $service = Service::create([
            'user_id' => $owner->id,
            'title' => 'خدمة المالك الأصلي',
            'slug' => 'original-owner-service',
            'description' => 'وصف الخدمة الأصلي يتجاوز الحد الأدنى.',
            'price' => 100.00,
            'delivery_days' => 3,
            'category' => 'تطوير البرمجيات',
            'status' => 'active',
        ]);

        $updateResponse = $this->actingAs($stranger)->put(route('marketplace.services.update', $service), [
            'title' => 'محاولة اختراق',
            'description' => 'وصف جديد محاولة اختراق الخدمة.',
            'price' => 10.00,
            'delivery_days' => 1,
            'category' => 'تطوير البرمجيات',
            'status' => 'active',
        ]);

        $updateResponse->assertStatus(403);
        $this->assertEquals('خدمة المالك الأصلي', $service->fresh()->title);

        $deleteResponse = $this->actingAs($stranger)->delete(route('marketplace.services.destroy', $service));

        $deleteResponse->assertStatus(403);
        $this->assertDatabaseHas('services', ['id' => $service->id]);
    }

    public function test_freelancer_can_update_freelancer_profile(): void
    {
        $user = $this->createOnboardedUser();

        $response = $this->actingAs($user)->put(route('marketplace.freelancers.profile.update'), [
            'title' => 'Laravel & Vue.js Senior Engineer',
            'hourly_rate' => 60.00,
            'location' => 'الرياض، المملكة العربية السعودية',
            'availability' => 'available',
            'bio' => 'مهندس برمجيات متخصص في بناء المنظمات والتطبيقات السحابية.',
        ]);

        $response->assertRedirect(route('marketplace.freelancers.show', $user));
        $this->assertDatabaseHas('freelancer_profiles', [
            'user_id' => $user->id,
            'title' => 'Laravel & Vue.js Senior Engineer',
            'hourly_rate' => 60.00,
        ]);
        $this->assertEquals('freelancer', $user->fresh()->account_type);
    }

    public function test_client_can_order_a_marketplace_service(): void
    {
        $freelancer = $this->createOnboardedUser(['name' => 'المستقل البائع']);
        $client = $this->createOnboardedUser(['name' => 'العميل المشتري']);

        $service = Service::create([
            'user_id' => $freelancer->id,
            'title' => 'تطوير لوحة تحكم سريعة',
            'slug' => 'fast-dashboard-dev',
            'description' => 'تقديم لوحة تحكم مخصصة ومصممة بأعلى معايير الجودة.',
            'price' => 300.00,
            'delivery_days' => 4,
            'category' => 'تطوير البرمجيات',
            'status' => 'active',
        ]);

        $response = $this->actingAs($client)->post(route('marketplace.services.order', $service));

        $response->assertRedirect();
        $this->assertDatabaseHas('contracts', [
            'client_id' => $client->id,
            'freelancer_id' => $freelancer->id,
            'amount' => 300.00,
            'status' => 'active',
        ]);
        $this->assertEquals(1, $service->fresh()->sales_count);
    }

    public function test_user_cannot_order_own_service(): void
    {
        $user = $this->createOnboardedUser();
        $service = Service::create([
            'user_id' => $user->id,
            'title' => 'خدمتي الخاصة',
            'slug' => 'my-own-service',
            'description' => 'وصف الخدمة الخاصة بي يتجاوز الحد الأدنى.',
            'price' => 100.00,
            'delivery_days' => 2,
            'category' => 'تطوير البرمجيات',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('marketplace.services.order', $service));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['order']);
        $this->assertEquals(0, $service->fresh()->sales_count);
    }

    public function test_service_cover_image_upload_and_validation(): void
    {
        Storage::fake('public');

        $user = $this->createOnboardedUser();
        $file = UploadedFile::fake()->create('cover.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)->post(route('marketplace.services.store'), [
            'title' => 'خدمة مع صورة غلاف',
            'description' => 'وصف تفصيلي للخدمة مع صورة غلاف مرفقة.',
            'price' => 120.00,
            'delivery_days' => 3,
            'category' => 'تطوير البرمجيات',
            'cover_image' => $file,
        ]);

        $response->assertRedirect();
        $service = Service::where('user_id', $user->id)->first();
        $this->assertNotNull($service->cover_image);
        Storage::disk('public')->assertExists($service->cover_image);
    }
}
