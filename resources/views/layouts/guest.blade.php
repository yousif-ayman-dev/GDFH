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

  <title>Tasker</title>

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
    <div class="w-full max-w-md">
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
