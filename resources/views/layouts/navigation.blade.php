@php
$navigationSections = [
  [
    'title' => 'الرئيسية',
    'items' => [
      [
        'label' => 'الرئيسية',
        'route' => 'dashboard',
        'active' => 'dashboard',
        'icon' => 'home',
      ],
    ]
  ],
  [
    'title' => 'إدارة العمل',
    'items' => [
      [
        'label' => 'المشاريع',
        'route' => 'projects.index',
        'active' => 'projects.*',
        'icon' => 'projects',
      ],
      [
        'label' => 'الفرق',
        'route' => 'teams.index',
        'active' => 'teams.*',
        'icon' => 'teams',
      ],
      [
        'label' => 'الدعوات',
        'route' => 'invitations.index',
        'active' => 'invitations.*',
        'icon' => 'invitations',
      ],
      [
        'label' => 'لوحة كانبان',
        'route' => 'kanban.index',
        'active' => 'kanban.*',
        'icon' => 'kanban',
      ],
      [
        'label' => 'مخطط غانت',
        'route' => 'gantt.index',
        'active' => 'gantt.*',
        'icon' => 'gantt',
      ],
      [
        'label' => 'تتبع الوقت',
        'route' => 'time-tracking.index',
        'active' => 'time-tracking.*',
        'icon' => 'time-tracking',
      ],
    ]
  ],
  [
    'title' => 'الذكاء والتحليلات',
    'items' => [
      [
        'label' => 'مساعد الذكاء الاصطناعي',
        'route' => 'ai.index',
        'active' => 'ai.*',
        'icon' => 'ai',
      ],
      [
        'label' => 'التقارير والتحليلات',
        'route' => 'reports.index',
        'active' => 'reports.*',
        'icon' => 'reports',
      ],
      [
        'label' => 'الإشعارات',
        'route' => 'notifications.index',
        'active' => 'notifications.*',
        'icon' => 'notifications',
      ],
      [
        'label' => 'التقويم',
        'route' => 'calendar.index',
        'active' => 'calendar.*',
        'icon' => 'calendar',
      ],
    ]
  ],
  [
    'title' => 'الخدمات والتواصل',
    'items' => [
      [
        'label' => 'السوق والدليل',
        'route' => 'marketplace.index',
        'active' => 'marketplace.*',
        'icon' => 'marketplace',
      ],
      [
        'label' => 'العقود والاتفاقيات',
        'route' => 'contracts.index',
        'active' => 'contracts.*',
        'icon' => 'contracts',
      ],
      [
        'label' => 'الرسائل والمحادثات',
        'route' => 'messaging.index',
        'active' => 'messaging.*',
        'icon' => 'messaging',
      ],
    ]
  ]
];
@endphp

<aside class="fixed inset-y-0 end-0 z-40 hidden w-72 lg:flex lg:flex-col bg-[rgb(var(--color-navy))] text-slate-100 border-s border-slate-700/60 shadow-sm">
  
  {{-- Workspace Branding Header --}}
  <div class="flex h-16 shrink-0 items-center justify-between px-6 border-b border-slate-700/60">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group" aria-label="Tasker Enterprise Workspace">
      <div class="flex h-9 w-9 items-center justify-center rounded-xl font-black text-sm bg-[rgb(var(--color-copper))] text-white shadow-md group-hover:scale-105 transition-transform">
        G
      </div>

      <div class="min-w-0">
        <div class="flex items-center gap-1.5">
          <span class="text-sm font-bold tracking-tight text-white truncate">Tasker</span>
        </div>
        <div class="text-[11px] text-[rgb(var(--color-copper))] font-medium flex items-center gap-1">
          <span class="h-1.5 w-1.5 rounded-full bg-[rgb(var(--color-copper))] animate-pulse"></span>
          Enterprise SaaS
        </div>
      </div>
    </a>
  </div>

  {{-- Navigation Body --}}
  <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-6" aria-label="التنقل الرئيسي">
    
    @foreach ($navigationSections as $section)
    <div class="space-y-1">
      <div class="px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
        {{ $section['title'] }}
      </div>

      @foreach ($section['items'] as $item)
      @php
        $isActive = request()->routeIs($item['active']);
      @endphp

      <a href="{{ route($item['route']) }}"
        @class([
          'group relative flex min-h-[40px] items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all duration-150',
        ])
        style="{{ $isActive 
          ? 'background-color: rgba(55, 86, 198, 0.15); color: #3756C6; font-weight: 700;' 
          : 'color: #94A3B8;' 
        }}">
        
        @if ($isActive)
        <span class="absolute inset-y-1 end-0 w-1 rounded-s-full bg-[rgb(var(--color-mineral))]"></span>
        @endif

        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg transition-transform group-hover:scale-110" @if ($isActive) style="color: #3756C6;" @endif>
          @switch($item['icon'])
            @case('home')
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
              @break
            @case('projects')
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
              @break
            @case('teams')
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
              @break
            @case('invitations')
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              @break
            @case('kanban')
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
              @break
            @case('gantt')
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18M3 9.5h12M3 14.5h15M3 19.5h9"/></svg>
              @break
            @case('time-tracking')
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3"/></svg>
              @break
            @case('ai')
              <svg class="h-4 w-4 text-[rgb(var(--color-copper))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
              @break
            @case('reports')
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
              @break
            @case('notifications')
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
              @break
            @case('calendar')
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
              @break
            @case('marketplace')
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
              @break
            @case('contracts')
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              @break
            @case('messaging')
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
              @break
          @endswitch
        </span>

        <span class="truncate">{{ $item['label'] }}</span>

        @if ($item['icon'] === 'notifications' && Auth::user() && Auth::user()->unreadNotificationsCount() > 0)
        <span class="ms-auto flex h-4 px-1.5 items-center justify-center rounded-full text-[10px] font-bold bg-[rgb(var(--color-copper))] text-white">
          {{ Auth::user()->unreadNotificationsCount() }}
        </span>
        @endif
      </a>
      @endforeach
    </div>
    @endforeach

  </nav>

  {{-- Sidebar User Profile Footer Card --}}
  <div class="px-4 pb-4 pt-2 border-t border-slate-700/60">
    <div class="rounded-xl p-2.5 bg-slate-800/50 border border-slate-700/60 space-y-2">
      <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full font-bold text-xs bg-blue-500/20 text-blue-400">
          {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
        </div>

        <div class="min-w-0 flex-1">
          <div class="truncate text-xs font-bold text-white">
            {{ Auth::user()->name }}
          </div>
          <div class="truncate text-[10px] font-mono text-slate-400">
            {{ Auth::user()->email }}
          </div>
        </div>

        <a href="{{ route('profile.edit') }}" class="text-slate-400 hover:text-[rgb(var(--color-copper))] p-1" title="الإعدادات">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
        </a>
      </div>

      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="flex min-h-8 w-full items-center justify-center gap-1.5 rounded-lg px-2 text-[11px] font-semibold bg-slate-800 text-red-400 border border-slate-700/60 hover:bg-red-500/10 transition">
          <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          <span>تسجيل الخروج</span>
        </button>
      </form>
    </div>
  </div>

</aside>
