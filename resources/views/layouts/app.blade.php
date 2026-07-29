<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
  x-data="{
        mobileNavigation: false,
        userMenu: false
    }" :class="{ 'dark': $store.theme.isDark }">

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

  <style>
  [x-cloak] {
    display: none !important;
  }
  </style>
</head>

<body>
  <div class="min-h-screen">
    {{-- Desktop sidebar --}}
    @include('layouts.navigation')

    {{-- Mobile overlay --}}
    <div x-cloak x-show="mobileNavigation" x-transition.opacity
      class="fixed inset-0 z-40 bg-black/35 backdrop-blur-[2px] lg:hidden" @click="mobileNavigation = false"
      aria-hidden="true"></div>

    {{-- Mobile navigation --}}
    <aside x-cloak x-show="mobileNavigation" x-transition:enter="transition duration-200 ease-out"
      x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
      x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-x-0"
      x-transition:leave-end="translate-x-full" class="fixed inset-y-0 end-0 z-50 w-[min(88vw,340px)] lg:hidden" style="
                background-color: rgb(var(--color-surface));
                border-inline-start: 1px solid rgb(var(--color-border));
            " @keydown.escape.window="mobileNavigation = false">
      <div class="flex h-20 items-center justify-between px-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl text-sm font-bold"
            style="background-color: rgb(var(--color-copper)); color: #1b1511;">
            G
          </div>

          <div>
            <div class="font-bold">GDFH</div>
            <div class="text-xs" style="color: rgb(var(--color-text-secondary));">
              إدارة العمل بوضوح
            </div>
          </div>
        </a>

        <button type="button" class="flex h-10 w-10 items-center justify-center rounded-xl"
          style="color: rgb(var(--color-text-secondary));" @click="mobileNavigation = false" aria-label="إغلاق القائمة">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" />
          </svg>
        </button>
      </div>

      <nav class="px-4 py-3">
        <div class="space-y-1">
          <a href="{{ route('dashboard') }}" class="flex min-h-11 items-center rounded-xl px-4 text-sm font-medium"
            style="{{ request()->routeIs('dashboard')
                            ? 'background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-text-primary));'
                            : 'color: rgb(var(--color-text-secondary));'
                        }}">
            الرئيسية
          </a>

          <a href="{{ route('projects.index') }}" class="flex min-h-11 items-center rounded-xl px-4 text-sm font-medium"
            style="{{ request()->routeIs('projects.*')
                            ? 'background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-text-primary));'
                            : 'color: rgb(var(--color-text-secondary));'
                        }}">
            المشاريع
          </a>

          <a href="{{ route('teams.index') }}" class="flex min-h-11 items-center rounded-xl px-4 text-sm font-medium"
            style="{{ request()->routeIs('teams.*')
                            ? 'background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-text-primary));'
                            : 'color: rgb(var(--color-text-secondary));'
                        }}">
            الفرق
          </a>

          <a href="{{ route('profile.edit') }}" class="flex min-h-11 items-center rounded-xl px-4 text-sm font-medium"
            style="{{ request()->routeIs('profile.*')
                            ? 'background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-text-primary));'
                            : 'color: rgb(var(--color-text-secondary));'
                        }}">
            الحساب والإعدادات
          </a>
        </div>
      </nav>
    </aside>

    {{-- Application area --}}
    <div class="min-h-screen lg:pe-72">
      {{-- Top bar --}}
      <header class="sticky top-0 z-30" style="
                    background-color: rgb(var(--color-background) / 0.88);
                    border-bottom: 1px solid rgb(var(--color-border));
                    backdrop-filter: blur(16px);
                ">
        <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
          {{-- Mobile navigation button --}}
          <button type="button" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl lg:hidden" style="
                            background-color: rgb(var(--color-surface));
                            border: 1px solid rgb(var(--color-border));
                        " @click="mobileNavigation = true" aria-label="فتح القائمة">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" d="M5 7h14M5 12h14M5 17h14" />
            </svg>
          </button>

          {{-- Search placeholder --}}
          <button type="button"
            class="hidden min-h-10 w-full max-w-md items-center gap-3 rounded-xl px-3 text-sm sm:flex" style="
                            background-color: rgb(var(--color-surface));
                            border: 1px solid rgb(var(--color-border));
                            color: rgb(var(--color-text-secondary));
                        " title="البحث الشامل سيُضاف لاحقًا">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <circle cx="11" cy="11" r="6" />
              <path stroke-linecap="round" d="m16 16 4 4" />
            </svg>

            <span>ابحث في GDFH...</span>

            <span class="ms-auto rounded-md px-2 py-0.5 text-[11px]"
              style="background-color: rgb(var(--color-surface-soft));">
              Ctrl K
            </span>
          </button>

          <div class="ms-auto flex items-center gap-2">
            {{-- Theme --}}
            <div class="flex items-center rounded-xl p-1" style="
                                background-color: rgb(var(--color-surface));
                                border: 1px solid rgb(var(--color-border));
                            " aria-label="المظهر">
              <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg" :style="$store.theme.mode === 'light'
                                    ? 'background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));'
                                    : 'color: rgb(var(--color-text-secondary));'" @click="$store.theme.set('light')"
                aria-label="الوضع الفاتح" title="فاتح">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <circle cx="12" cy="12" r="3.5" />
                  <path stroke-linecap="round"
                    d="M12 3v2M12 19v2M3 12h2M19 12h2M5.6 5.6 7 7M17 17l1.4 1.4M18.4 5.6 17 7M7 17l-1.4 1.4" />
                </svg>
              </button>

              <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg" :style="$store.theme.mode === 'dark'
                                    ? 'background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));'
                                    : 'color: rgb(var(--color-text-secondary));'" @click="$store.theme.set('dark')"
                aria-label="الوضع الداكن" title="داكن">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M20 15.2A8 8 0 0 1 8.8 4a8.2 8.2 0 1 0 11.2 11.2Z" />
                </svg>
              </button>

              <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg" :style="$store.theme.mode === 'system'
                                    ? 'background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));'
                                    : 'color: rgb(var(--color-text-secondary));'" @click="$store.theme.set('system')"
                aria-label="استخدام إعداد النظام" title="النظام">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <rect x="3" y="4" width="18" height="13" rx="2" />
                  <path stroke-linecap="round" d="M8 21h8M12 17v4" />
                </svg>
              </button>
            </div>

            {{-- User menu --}}
            <div class="relative">
              <button type="button" class="flex h-10 items-center gap-2 rounded-xl px-2" style="
                                    background-color: rgb(var(--color-surface));
                                    border: 1px solid rgb(var(--color-border));
                                " @click="userMenu = ! userMenu" @click.outside="userMenu = false"
                :aria-expanded="userMenu">
                <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold" style="
                                        background-color: rgb(var(--color-mineral-soft));
                                        color: rgb(var(--color-mineral));
                                    ">
                  {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                </span>

                <span class="hidden max-w-32 truncate text-sm font-semibold sm:block">
                  {{ Auth::user()->name }}
                </span>

                <svg class="hidden h-4 w-4 sm:block" style="color: rgb(var(--color-text-secondary));"
                  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" d="m8 10 4 4 4-4" />
                </svg>
              </button>

              <div x-cloak x-show="userMenu" x-transition
                class="absolute end-0 mt-2 w-64 overflow-hidden rounded-2xl p-2" style="
                                    background-color: rgb(var(--color-surface));
                                    border: 1px solid rgb(var(--color-border));
                                    box-shadow: 0 18px 45px rgb(0 0 0 / 0.12);
                                ">
                <div class="px-3 py-3">
                  <div class="truncate text-sm font-semibold">
                    {{ Auth::user()->name }}
                  </div>

                  <div class="mt-1 truncate text-xs" style="color: rgb(var(--color-text-secondary));">
                    {{ Auth::user()->email }}
                  </div>
                </div>

                <div class="my-1 border-t" style="border-color: rgb(var(--color-border));"></div>

                <a href="{{ route('profile.edit') }}" class="flex min-h-10 items-center rounded-xl px-3 text-sm">
                  الحساب والإعدادات
                </a>

                <form method="POST" action="{{ route('logout') }}">
                  @csrf

                  <button type="submit" class="flex min-h-10 w-full items-center rounded-xl px-3 text-sm"
                    style="color: rgb(var(--color-error));">
                    تسجيل الخروج
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </header>

      {{-- Optional page header --}}
      @isset($header)
      <header style="border-bottom: 1px solid rgb(var(--color-border));">
        <div class="px-4 py-6 sm:px-6 lg:px-8">
          {{ $header }}
        </div>
      </header>
      @endisset

      {{-- Page content --}}
      <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        {{ $slot }}
      </main>
    </div>
  </div>
</body>

</html>
