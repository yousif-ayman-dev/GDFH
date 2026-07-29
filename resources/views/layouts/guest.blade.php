<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
  x-data :class="{ 'dark': $store.theme.isDark }">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <meta name="color-scheme" content="light dark">

  <title>GDFH</title>

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
    }
  })();
  </script>

  <link rel="preconnect" href="https://fonts.bunny.net">
  <link
    href="https://fonts.bunny.net/css?family=ibm-plex-sans-arabic:400,500,600,700|inter:400,500,600,700&display=swap"
    rel="stylesheet">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
  <div class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6"
    style="background-color: rgb(var(--color-background));">
    <div class="w-full max-w-md">
      <div class="mb-8 flex justify-center">
        <a href="/" aria-label="GDFH">
          <x-application-logo class="h-16 w-16" style="color: rgb(var(--color-copper));" />
        </a>
      </div>

      <div class="gdfh-card p-6 sm:p-8">
        {{ $slot }}
      </div>
    </div>
  </div>
</body>

</html>
