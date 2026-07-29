<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
  x-data :class="{ 'dark': $store.theme.isDark }">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <meta name="color-scheme" content="light dark">

  <title>
    @isset($title)
    {{ $title }} — GDFH
    @else
    GDFH
    @endisset
  </title>

  {{-- Prevent theme flash before Alpine loads --}}
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

  {{-- Fonts --}}
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link
    href="https://fonts.bunny.net/css?family=ibm-plex-sans-arabic:400,500,600,700|inter:400,500,600,700&display=swap"
    rel="stylesheet">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
  <div class="min-h-screen">
    @include('layouts.navigation')

    @isset($header)
    <header style="
                    background-color: rgb(var(--color-surface));
                    border-bottom: 1px solid rgb(var(--color-border));
                ">
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        {{ $header }}
      </div>
    </header>
    @endisset

    <main>
      {{ $slot }}
    </main>
  </div>
</body>

</html>
