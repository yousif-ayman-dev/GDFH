<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAndDiscoverabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_txt_returns_success_and_disallows_private_routes(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('User-agent: *');
        $response->assertSee('Disallow: /admin');
        $response->assertSee('Disallow: /dashboard');
        $response->assertSee('Disallow: /ai');
        $response->assertSee('Disallow: /chat');
        $response->assertSee('Sitemap:');
    }

    public function test_sitemap_xml_returns_valid_xml_with_public_urls(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('<urlset', false);
        $response->assertSee('<loc>', false);
        $response->assertSee('/about', false);
        $response->assertSee('/features', false);
        $response->assertSee('/faq', false);
        $response->assertSee('/marketplace', false);

        // Assert private routes are NOT in sitemap
        $response->assertDontSee('/admin');
        $response->assertDontSee('/dashboard');
        $response->assertDontSee('/ai');
    }

    public function test_public_info_pages_are_accessible_to_guests(): void
    {
        $this->get('/about')->assertStatus(200)->assertSee('Tasker Enterprise');
        $this->get('/features')->assertStatus(200)->assertSee('الميزات والخصائص');
        $this->get('/faq')->assertStatus(200)->assertSee('الأسئلة الشائعة');
    }

    public function test_public_pages_contain_seo_opengraph_and_json_ld_schema(): void
    {
        $response = $this->get('/about');

        $response->assertStatus(200);
        $response->assertSee('og:type', false);
        $response->assertSee('og:title', false);
        $response->assertSee('og:image', false);
        $response->assertSee('twitter:card', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('Organization', false);
        $response->assertSee('WebSite', false);
        $response->assertSee('SoftwareApplication', false);
    }
}
