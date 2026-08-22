<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
  x-data="{
        mobileNavigation: false,
        userMenu: false,
        quickCreateMenu: false,
        commandPalette: false,
        searchQuery: '',
        selectedIndex: 0,
        items: [
            { title: 'الرئيسية (Dashboard)', type: 'صفحة', url: '{{ route('dashboard') }}', category: 'التنقل الرئيسي' },
            { title: 'المشاريع (Projects)', type: 'صفحة', url: '{{ route('projects.index') }}', category: 'إدارة العمل' },
            { title: 'إنشاء مشروع جديد (New Project)', type: 'إجراء سريع', url: '{{ route('projects.create') }}', category: 'اختصارات' },
            { title: 'الفرق (Teams)', type: 'صفحة', url: '{{ route('teams.index') }}', category: 'إدارة العمل' },
            { title: 'جميع المهام (Tasks)', type: 'صفحة', url: '{{ route('tasks.index') }}', category: 'إدارة العمل' },
            { title: 'إنشاء فريق جديد (New Team)', type: 'إجراء سريع', url: '{{ route('teams.create') }}', category: 'اختصارات' },
            { title: 'لوحة كانبان (Kanban Board)', type: 'أداة', url: '{{ route('kanban.index') }}', category: 'الأدوات' },
            { title: 'مخطط غانت (Gantt Chart)', type: 'أداة', url: '{{ route('gantt.index') }}', category: 'الأدوات' },
            { title: 'تتبع الوقت (Time Tracking)', type: 'أداة', url: '{{ route('time-tracking.index') }}', category: 'الأدوات' },
            { title: 'مساعد الذكاء الاصطناعي (AI Assistant)', type: 'ذكاء اصطناعي', url: '{{ route('ai.index') }}', category: 'الذكاء الاصطناعي' },
            { title: 'التقارير والتحليلات (Reports)', type: 'تحليلات', url: '{{ route('reports.index') }}', category: 'التقارير' },
            { title: 'مركز الدعوات (Invitations)', type: 'صفحة', url: '{{ route('invitations.index') }}', category: 'التنقل' },
            { title: 'الحساب والإعدادات (Settings)', type: 'إعدادات', url: '{{ route('profile.edit') }}', category: 'النظام' }
        ],
        get filteredItems() {
            if (!this.searchQuery.trim()) return this.items;
            const q = this.searchQuery.toLowerCase().trim();
            return this.items.filter(i => i.title.toLowerCase().includes(q) || i.type.toLowerCase().includes(q) || i.category.toLowerCase().includes(q));
        },
        navigate() {
            const list = this.filteredItems;
            if (list.length > 0 && list[this.selectedIndex]) {
                window.location.href = list[this.selectedIndex].url;
            }
        }
    }"
  @keydown.window="(e) => {
        if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
            e.preventDefault();
            commandPalette = !commandPalette;
            if (commandPalette) {
                searchQuery = '';
                selectedIndex = 0;
                $nextTick(() => $refs.commandInput?.focus());
            }
        }
    }"
  :class="{ 'dark': $store.theme.isDark }">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <meta name="color-scheme" content="light dark">

  <title>
    @isset($title)
    {{ $title }} — Tasker Enterprise
    @else
    Tasker Enterprise SaaS
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

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@300;400;500;600;700;800&family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
  [x-cloak] {
    display: none !important;
  }
  </style>
</head>

<body class="font-sans antialiased text-[rgb(var(--color-text-primary))] bg-[rgb(var(--color-background))]">
  <div class="min-h-screen">
    {{-- Desktop sidebar --}}
    @include('layouts.navigation')

    {{-- Mobile overlay --}}
    <div x-cloak x-show="mobileNavigation" x-transition.opacity
      class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden" @click="mobileNavigation = false"
      aria-hidden="true"></div>

    {{-- Mobile navigation --}}
    <aside x-cloak x-show="mobileNavigation" x-transition:enter="transition duration-200 ease-out"
      x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
      x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-x-0"
      x-transition:leave-end="translate-x-full" class="fixed inset-y-0 end-0 z-50 w-[min(88vw,340px)] lg:hidden bg-[rgb(var(--color-navy))] text-slate-100 border-s border-slate-700/60" @keydown.escape.window="mobileNavigation = false">
      <div class="flex h-20 items-center justify-between px-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl font-extrabold text-sm bg-[rgb(var(--color-copper))] text-white">
            G
          </div>
          <div>
            <div class="font-bold text-white">Tasker Enterprise</div>
            <div class="text-xs text-slate-400">منصة إدارة الأعمال</div>
          </div>
        </a>

        <button type="button" class="flex h-10 w-10 items-center justify-center rounded-xl text-slate-400 hover:text-white" @click="mobileNavigation = false" aria-label="إغلاق القائمة">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" />
          </svg>
        </button>
      </div>

      <nav class="px-4 py-3 space-y-1">
        <a href="{{ route('dashboard') }}" class="flex min-h-11 items-center rounded-xl px-4 text-sm font-medium transition" style="{{ request()->routeIs('dashboard') ? 'background-color: rgba(55, 86, 198, 0.15); color: #3756C6; font-weight: 700;' : 'color: #94A3B8;' }}">
          الرئيسية
        </a>
        <a href="{{ route('projects.index') }}" class="flex min-h-11 items-center rounded-xl px-4 text-sm font-medium transition" style="{{ request()->routeIs('projects.*') ? 'background-color: rgba(55, 86, 198, 0.15); color: #3756C6; font-weight: 700;' : 'color: #94A3B8;' }}">
          المشاريع
        </a>
        <a href="{{ route('teams.index') }}" class="flex min-h-11 items-center rounded-xl px-4 text-sm font-medium transition" style="{{ request()->routeIs('teams.*') ? 'background-color: rgba(55, 86, 198, 0.15); color: #3756C6; font-weight: 700;' : 'color: #94A3B8;' }}">
          الفرق
        </a>
        <a href="{{ route('tasks.index') }}" class="flex min-h-11 items-center rounded-xl px-4 text-sm font-medium transition" style="{{ request()->routeIs('tasks.*') ? 'background-color: rgba(55, 86, 198, 0.15); color: #3756C6; font-weight: 700;' : 'color: #94A3B8;' }}">
          المهام
        </a>
        <a href="{{ route('kanban.index') }}" class="flex min-h-11 items-center rounded-xl px-4 text-sm font-medium transition" style="{{ request()->routeIs('kanban.*') ? 'background-color: rgba(55, 86, 198, 0.15); color: #3756C6; font-weight: 700;' : 'color: #94A3B8;' }}">
          لوحة كانبان
        </a>
        <a href="{{ route('gantt.index') }}" class="flex min-h-11 items-center rounded-xl px-4 text-sm font-medium transition" style="{{ request()->routeIs('gantt.*') ? 'background-color: rgba(55, 86, 198, 0.15); color: #3756C6; font-weight: 700;' : 'color: #94A3B8;' }}">
          مخطط غانت
        </a>
        <a href="{{ route('ai.index') }}" class="flex min-h-11 items-center rounded-xl px-4 text-sm font-medium transition" style="{{ request()->routeIs('ai.*') ? 'background-color: rgba(55, 86, 198, 0.15); color: #3756C6; font-weight: 700;' : 'color: #94A3B8;' }}">
          المساعد الذكي
        </a>
        <a href="{{ route('reports.index') }}" class="flex min-h-11 items-center rounded-xl px-4 text-sm font-medium transition" style="{{ request()->routeIs('reports.*') ? 'background-color: rgba(55, 86, 198, 0.15); color: #3756C6; font-weight: 700;' : 'color: #94A3B8;' }}">
          التقارير
        </a>
        <a href="{{ route('profile.edit') }}" class="flex min-h-11 items-center rounded-xl px-4 text-sm font-medium transition" style="{{ request()->routeIs('profile.*') ? 'background-color: rgba(55, 86, 198, 0.15); color: #3756C6; font-weight: 700;' : 'color: #94A3B8;' }}">
          الحساب والإعدادات
        </a>

        <form method="POST" action="{{ route('logout') }}" class="mt-4 pt-4 border-t border-slate-700/60">
          @csrf
          <button type="submit" class="flex min-h-11 w-full items-center justify-center gap-2 rounded-xl px-4 text-sm font-semibold bg-slate-800 text-red-400 border border-slate-700/60">
            <span>تسجيل الخروج</span>
          </button>
        </form>
      </nav>
    </aside>

    {{-- Main Content Area --}}
    <div class="min-h-screen lg:pe-72">
      
      {{-- Sticky Glass Topbar --}}
      <header class="sticky top-0 z-30 bg-[rgb(var(--color-background)/0.8)] border-b border-[rgb(var(--color-border))] backdrop-blur-md">
        <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
          
          {{-- Mobile Hamburger Button --}}
          <button type="button" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl lg:hidden bg-[rgb(var(--color-surface))] border border-[rgb(var(--color-border))]" @click="mobileNavigation = true" aria-label="فتح القائمة">
            <svg class="h-5 w-5 text-[rgb(var(--color-text-primary))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" d="M5 7h14M5 12h14M5 17h14" />
            </svg>
          </button>

          {{-- Command Palette Trigger Button (Ctrl+K) --}}
          <button type="button" @click="commandPalette = true; $nextTick(() => $refs.commandInput?.focus())"
            class="hidden min-h-10 w-full max-w-md items-center gap-3 rounded-xl px-3.5 text-sm sm:flex bg-[rgb(var(--color-surface))] border border-[rgb(var(--color-border))] text-[rgb(var(--color-text-secondary))] hover:border-[rgb(var(--color-copper)/0.5)] transition shadow-sm" title="البحث الشامل والأوامر السريعة (Ctrl+K)">
            <svg class="h-4 w-4 shrink-0 text-[rgb(var(--color-copper))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="6" />
              <path stroke-linecap="round" d="m16 16 4 4" />
            </svg>

            <span class="truncate">ابحث في Tasker أو نفّذ أمراً...</span>

            <span class="ms-auto rounded-md px-2 py-0.5 text-[11px] font-mono font-semibold bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))] border border-[rgb(var(--color-border))]">
              Ctrl K
            </span>
          </button>

          <div class="ms-auto flex items-center gap-2.5">

            {{-- Quick Create Dropdown Button --}}
            <div class="relative">
              <button type="button" @click="quickCreateMenu = !quickCreateMenu" @click.outside="quickCreateMenu = false"
                class="gdfh-btn gdfh-btn-brand text-xs min-h-[38px] px-3">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                </svg>
                <span class="hidden sm:inline">+ جديد</span>
              </button>

              <div x-cloak x-show="quickCreateMenu" x-transition
                class="absolute end-0 mt-2 w-48 overflow-hidden rounded-xl p-1.5 bg-[rgb(var(--color-surface))] border border-[rgb(var(--color-border))] shadow-xl z-50">
                <a href="{{ route('projects.create') }}" class="gdfh-dropdown-item">
                  <svg class="h-4 w-4 text-[rgb(var(--color-copper))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12h14"/></svg>
                  <span>مشروع جديد</span>
                </a>
                <a href="{{ route('teams.create') }}" class="gdfh-dropdown-item">
                  <svg class="h-4 w-4 text-[rgb(var(--color-mineral))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                  <span>فريق جديد</span>
                </a>
                <a href="{{ route('invitations.index') }}" class="gdfh-dropdown-item">
                  <svg class="h-4 w-4 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                  <span>دعوة عضو</span>
                </a>
              </div>
            </div>

            {{-- Theme Toggle --}}
            <div class="flex items-center rounded-xl p-1 bg-[rgb(var(--color-surface))] border border-[rgb(var(--color-border))]" aria-label="المظهر">
              <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg transition" :style="$store.theme.mode === 'light' ? 'background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));' : 'color: rgb(var(--color-text-secondary));'" @click="$store.theme.set('light')" aria-label="الوضع الفاتح" title="فاتح">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <circle cx="12" cy="12" r="3.5" />
                  <path stroke-linecap="round" d="M12 3v2M12 19v2M3 12h2M19 12h2M5.6 5.6 7 7M17 17l1.4 1.4M18.4 5.6 17 7M7 17l-1.4 1.4" />
                </svg>
              </button>

              <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg transition" :style="$store.theme.mode === 'dark' ? 'background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));' : 'color: rgb(var(--color-text-secondary));'" @click="$store.theme.set('dark')" aria-label="الوضع الداكن" title="داكن">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M20 15.2A8 8 0 0 1 8.8 4a8.2 8.2 0 1 0 11.2 11.2Z" />
                </svg>
              </button>

              <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg transition" :style="$store.theme.mode === 'system' ? 'background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));' : 'color: rgb(var(--color-text-secondary));'" @click="$store.theme.set('system')" aria-label="استخدام إعداد النظام" title="النظام">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <rect x="3" y="4" width="18" height="13" rx="2" />
                  <path stroke-linecap="round" d="M8 21h8M12 17v4" />
                </svg>
              </button>
            </div>

            {{-- User Menu --}}
            <div class="relative">
              <button type="button" class="flex h-10 items-center gap-2 rounded-xl px-2.5 bg-[rgb(var(--color-surface))] border border-[rgb(var(--color-border))] hover:border-[rgb(var(--color-copper)/0.4)] transition" @click="userMenu = !userMenu" @click.outside="userMenu = false" :aria-expanded="userMenu">
                <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold bg-[rgb(var(--color-mineral-soft))] text-[rgb(var(--color-mineral))]">
                  {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                </span>
                <span class="hidden max-w-32 truncate text-xs font-bold sm:block">
                  {{ Auth::user()->name }}
                </span>
                <svg class="hidden h-4 w-4 sm:block text-[rgb(var(--color-text-secondary))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" d="m8 10 4 4 4-4" />
                </svg>
              </button>

              <div x-cloak x-show="userMenu" x-transition class="absolute end-0 mt-2 w-64 overflow-hidden rounded-2xl p-2 bg-[rgb(var(--color-surface))] border border-[rgb(var(--color-border))] shadow-2xl z-50">
                <div class="px-3 py-3">
                  <div class="truncate text-sm font-bold">{{ Auth::user()->name }}</div>
                  <div class="mt-0.5 truncate text-xs font-mono text-[rgb(var(--color-text-secondary))]">
                    @if (Auth::user()->username)
                      {{ '@' . Auth::user()->username }}
                    @else
                      {{ Auth::user()->email }}
                    @endif
                  </div>
                </div>

                <div class="my-1 border-t border-[rgb(var(--color-border))]"></div>

                <a href="{{ route('invitations.index') }}" class="gdfh-dropdown-item">
                  <span>مركز الدعوات</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="gdfh-dropdown-item">
                  <span>الحساب والإعدادات</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="gdfh-dropdown-item w-full text-[rgb(var(--color-error))]">
                    <span>تسجيل الخروج</span>
                  </button>
                </form>
              </div>
            </div>

          </div>
        </div>
      </header>

      {{-- Optional page header --}}
      @isset($header)
      <header class="border-b border-[rgb(var(--color-border))]">
        <div class="px-4 py-6 sm:px-6 lg:px-8">
          {{ $header }}
        </div>
      </header>
      @endisset

      {{-- Page Content Slot --}}
      <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        {{ $slot }}
      </main>

    </div>
  </div>

  {{-- Interactive Command Palette Modal (Ctrl+K) --}}
  <div x-cloak x-show="commandPalette" x-transition.opacity
    class="fixed inset-0 z-50 flex items-start justify-center pt-16 sm:pt-24 px-4 bg-black/50 backdrop-blur-sm"
    @keydown.escape.window="commandPalette = false">
    
    <div class="relative w-full max-w-2xl overflow-hidden rounded-2xl bg-[rgb(var(--color-surface))] border border-[rgb(var(--color-border))] shadow-2xl space-y-0"
      @click.outside="commandPalette = false">
      
      {{-- Input Header --}}
      <div class="flex items-center px-4 border-b border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))]">
        <svg class="h-5 w-5 text-[rgb(var(--color-copper))] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="6" />
          <path stroke-linecap="round" d="m16 16 4 4" />
        </svg>
        <input x-ref="commandInput" type="text" x-model="searchQuery"
          @keydown.down.prevent="selectedIndex = Math.min(selectedIndex + 1, filteredItems.length - 1)"
          @keydown.up.prevent="selectedIndex = Math.max(selectedIndex - 1, 0)"
          @keydown.enter.prevent="navigate()"
          placeholder="اكتب أمراً أو ابحث في الصفحات، المشاريع، والمهام..."
          class="w-full bg-transparent border-0 py-4 px-3 text-sm focus:ring-0 text-[rgb(var(--color-text-primary))] placeholder-[rgb(var(--color-text-secondary))]">
        
        <span class="rounded-md px-2 py-1 text-[10px] font-mono bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))]">ESC للإغلاق</span>
      </div>

      {{-- Items List --}}
      <div class="max-h-96 overflow-y-auto p-2 divide-y divide-[rgb(var(--color-border)/0.4)]">
        <template x-if="filteredItems.length === 0">
          <div class="py-12 text-center text-xs text-[rgb(var(--color-text-secondary))]">
            لا توجد نتائج مطابقة لـ "<span x-text="searchQuery" class="font-bold"></span>"
          </div>
        </template>

        <template x-for="(item, index) in filteredItems" :key="index">
          <div @click="window.location.href = item.url"
            :class="{ 'bg-[rgb(var(--color-copper-soft)/0.6)] text-[rgb(var(--color-copper))]': selectedIndex === index }"
            @mouseenter="selectedIndex = index"
            class="flex items-center justify-between px-3 py-2.5 rounded-xl cursor-pointer transition text-xs">
            <div class="flex items-center gap-3">
              <span class="font-bold text-[rgb(var(--color-text-primary))]" x-text="item.title"></span>
            </div>
            <span class="gdfh-badge gdfh-badge-copper text-[10px]" x-text="item.category"></span>
          </div>
        </template>
      </div>

      {{-- Command Palette Footer Shortcuts --}}
      <div class="flex items-center justify-between px-4 py-2.5 bg-[rgb(var(--color-surface-soft)/0.4)] border-t border-[rgb(var(--color-border))] text-[11px] text-[rgb(var(--color-text-secondary))]">
        <div class="flex items-center gap-3">
          <span><kbd class="px-1.5 py-0.5 rounded bg-[rgb(var(--color-surface))] border border-[rgb(var(--color-border))] font-mono">↑↓</kbd> للتنقل</span>
          <span><kbd class="px-1.5 py-0.5 rounded bg-[rgb(var(--color-surface))] border border-[rgb(var(--color-border))] font-mono">↵</kbd> للاختيار</span>
        </div>
        <span class="font-semibold text-[rgb(var(--color-copper))]">Tasker Command Engine</span>
      </div>

    </div>
  </div>

</body>

</html>
