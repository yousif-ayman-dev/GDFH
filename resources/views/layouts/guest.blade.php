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
    const savedTheme = localStorage.getItem('gdfh-theme') || 'system';

    const isDark =
      savedTheme === 'dark' ||
      (
        savedTheme === 'system' &&
        window.matchMedia('(prefers-color-scheme: dark)').matches
      );

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
        <a href="/" aria-label="Tasker" class="flex flex-col items-center gap-2">
          <div class="flex h-12 w-12 items-center justify-center rounded-2xl font-black text-xl bg-[#2B58A8] text-white shadow-lg">
            T
          </div>
          <span class="text-xl font-bold tracking-tight text-[rgb(var(--color-text-primary))]">Tasker</span>
        </a>
      </div>

      <div class="gdfh-card p-6 sm:p-8">
        {{ $slot }}
      </div>
    </div>
  </div>
</body>

</html>
