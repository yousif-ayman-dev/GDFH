<?php

namespace App\Http\Controllers;

use App\Models\FreelancerProfile;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\View\View;

class SeoController extends Controller
{
    /**
     * Render dynamic robots.txt.
     */
    public function robots(): Response
    {
        $sitemapUrl = url('/sitemap.xml');

        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Allow: /about',
            'Allow: /features',
            'Allow: /faq',
            'Allow: /marketplace',
            'Allow: /marketplace/services/*',
            'Allow: /marketplace/freelancers/*',
            'Allow: /build/*',
            'Allow: /images/*',
            '',
            '# Private authenticated workspace & admin routes',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /forgot-password',
            'Disallow: /reset-password',
            'Disallow: /dashboard',
            'Disallow: /admin',
            'Disallow: /admin/*',
            'Disallow: /onboarding',
            'Disallow: /projects',
            'Disallow: /projects/*',
            'Disallow: /tasks',
            'Disallow: /tasks/*',
            'Disallow: /teams',
            'Disallow: /teams/*',
            'Disallow: /kanban',
            'Disallow: /gantt',
            'Disallow: /time-tracking',
            'Disallow: /calendar',
            'Disallow: /ai',
            'Disallow: /ai/*',
            'Disallow: /chat',
            'Disallow: /chat/*',
            'Disallow: /notifications',
            'Disallow: /reports',
            'Disallow: /profile',
            'Disallow: /settings',
            'Disallow: /contracts',
            'Disallow: /contracts/*',
            'Disallow: /invitations',
            'Disallow: /portfolio',
            'Disallow: /search',
            'Disallow: /storage/*',
            '',
            'Sitemap: ' . $sitemapUrl,
        ]);

        return ResponseFacade::make($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Render dynamic sitemap.xml.
     */
    public function sitemap(): Response
    {
        $baseUrl = rtrim(config('app.url', 'https://gdfh.online'), '/');

        $publicRoutes = [
            ['url' => $baseUrl . '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => $baseUrl . '/about', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => $baseUrl . '/features', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => $baseUrl . '/faq', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => $baseUrl . '/marketplace', 'priority' => '0.9', 'changefreq' => 'daily'],
        ];

        // Add active public marketplace services
        try {
            $services = Service::where('is_active', true)->latest()->limit(100)->get();
            foreach ($services as $service) {
                $publicRoutes[] = [
                    'url' => $baseUrl . '/marketplace/services/' . $service->id,
                    'priority' => '0.8',
                    'changefreq' => 'weekly',
                    'lastmod' => $service->updated_at ? $service->updated_at->toAtomString() : now()->toAtomString(),
                ];
            }
        } catch (\Throwable $e) {}

        // Add public freelancer profiles
        try {
            $freelancers = FreelancerProfile::with('user')->latest()->limit(100)->get();
            foreach ($freelancers as $profile) {
                if ($profile->user_id) {
                    $publicRoutes[] = [
                        'url' => $baseUrl . '/marketplace/freelancers/' . $profile->user_id,
                        'priority' => '0.7',
                        'changefreq' => 'weekly',
                        'lastmod' => $profile->updated_at ? $profile->updated_at->toAtomString() : now()->toAtomString(),
                    ];
                }
            }
        } catch (\Throwable $e) {}

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($publicRoutes as $item) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($item['url']) . '</loc>' . "\n";
            if (!empty($item['lastmod'])) {
                $xml .= '    <lastmod>' . $item['lastmod'] . '</lastmod>' . "\n";
            }
            $xml .= '    <changefreq>' . $item['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $item['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return ResponseFacade::make($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Public About page.
     */
    public function about(): View
    {
        return view('public.about');
    }

    /**
     * Public Features page.
     */
    public function features(): View
    {
        return view('public.features');
    }

    /**
     * Public FAQ page.
     */
    public function faq(): View
    {
        return view('public.faq');
    }
}
