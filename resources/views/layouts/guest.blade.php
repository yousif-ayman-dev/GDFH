<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
  x-data :class="{ 'dark': $store.theme.isDark }">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Favicon --}}
  <x-application-favicon />

  <meta name="color-scheme" content="light dark">

  <title>{{ isset($title) ? $title : 'منصة تاسكر Enterprise | المنصة المتكاملة لإدارة المشاريع والخدمات المصغرة' }}</title>

  <!-- SEO & Social Open Graph Meta Tags -->
  <meta name="description" content="{{ isset($metaDescription) ? $metaDescription : 'منصة تاسكر (Tasker Enterprise / GDFH) - المنصة العربية الرقمية الفائقة لإدارة المشاريع، التوظيف المستقل، وساعات العمل الذكية.' }}">
  <link rel="canonical" href="{{ isset($canonicalUrl) ? $canonicalUrl : url()->current() }}">
  
  <!-- Open Graph / Facebook / WhatsApp / Telegram / LinkedIn -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Tasker Enterprise (GDFH)">
  <meta property="og:url" content="{{ isset($canonicalUrl) ? $canonicalUrl : url()->current() }}">
  <meta property="og:title" content="{{ isset($title) ? $title : 'منصة تاسكر Enterprise | المنصة المتكاملة لإدارة المشاريع والخدمات المصغرة' }}">
  <meta property="og:description" content="{{ isset($metaDescription) ? $metaDescription : 'منصة تاسكر (Tasker Enterprise / GDFH) - المنصة العربية الرقمية الفائقة لإدارة المشاريع، التوظيف المستقل، وساعات العمل الذكية.' }}">
  <meta property="og:image" content="{{ asset('images/my-logo.png') }}">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:type" content="image/png">
  
  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="{{ isset($canonicalUrl) ? $canonicalUrl : url()->current() }}">
  <meta property="twitter:title" content="{{ isset($title) ? $title : 'منصة تاسكر Enterprise | المنصة المتكاملة لإدارة المشاريع والخدمات المصغرة' }}">
  <meta property="twitter:description" content="{{ isset($metaDescription) ? $metaDescription : 'منصة تاسكر (Tasker Enterprise / GDFH) - المنصة العربية الرقمية الفائقة لإدارة المشاريع، التوظيف المستقل، وساعات العمل الذكية.' }}">
  <meta property="twitter:image" content="{{ asset('images/my-logo.png') }}">

  <!-- JSON-LD Structured Data Schema -->
  <script type="application/ld+json">
  {!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
      [
        '@type' => 'Organization',
        '@id' => config('app.url', 'https://gdfh.online') . '/#organization',
        'name' => 'Tasker Enterprise (GDFH)',
        'url' => config('app.url', 'https://gdfh.online'),
        'logo' => asset('images/my-logo.png'),
        'description' => 'منصة Tasker Enterprise الجيل القادم لإدارة المشاريع والخدمات المصغرة والعمالة المستقلة.'
      ],
      [
        '@type' => 'WebSite',
        '@id' => config('app.url', 'https://gdfh.online') . '/#website',
        'url' => config('app.url', 'https://gdfh.online'),
        'name' => 'Tasker Enterprise',
        'description' => 'منصة سوق الخدمات المستقلة وإدارة المشاريع المؤسسية'
      ],
      [
        '@type' => 'SoftwareApplication',
        'name' => 'Tasker Enterprise Platform',
        'operatingSystem' => 'All Web Browsers',
        'applicationCategory' => 'BusinessApplication',
        'offers' => [
          '@type' => 'Offer',
          'price' => '0',
          'priceCurrency' => 'USD'
        ]
      ]
    ]
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
  </script>

  <script>
  (() => {
    const savedTheme = localStorage.getItem('gdfh-theme') || 'light';
    const isDark = savedTheme === 'dark';

    if (isDark) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  })();
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
  <div class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6"
    style="background-color: rgb(var(--color-background));">
    <div class="w-full {{ $maxWidth ?? 'max-w-md' }}">
      <div class="mb-8 flex justify-center">
        <a href="/" aria-label="Tasker">
          <x-application-logo size="lg" :showText="true" :stacked="true" />
        </a>
      </div>

      <div class="gdfh-card p-6 sm:p-8">
        {{ $slot }}
      </div>
    </div>
  </div>
</body>

</html>
