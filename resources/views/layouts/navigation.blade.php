@php
$navigation = [
[
'label' => 'الرئيسية',
'route' => 'dashboard',
'active' => 'dashboard',
'icon' => 'home',
],
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
[
'label' => 'التقارير والتحليلات',
'route' => 'reports.index',
'active' => 'reports.*',
'icon' => 'reports',
],
];
@endphp

<aside class="fixed inset-y-0 end-0 z-40 hidden w-72 lg:flex lg:flex-col" style="
        background-color: rgb(var(--color-surface));
        border-inline-start: 1px solid rgb(var(--color-border));
    ">
  {{-- Brand --}}
  <div class="flex h-20 shrink-0 items-center px-6">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3" aria-label="GDFH">
      <div class="flex h-10 w-10 items-center justify-center rounded-xl"
        style="background-color: rgb(var(--color-copper)); color: #1b1511;">
        <span class="text-sm font-bold tracking-tight">G</span>
      </div>

      <div>
        <div class="text-base font-bold tracking-tight">
          GDFH
        </div>

        <div class="mt-0.5 text-xs" style="color: rgb(var(--color-text-secondary));">
          إدارة العمل بوضوح
        </div>
      </div>
    </a>
  </div>

  {{-- Main navigation --}}
  <nav class="flex-1 overflow-y-auto px-4 py-4" aria-label="التنقل الرئيسي">
    <div class="space-y-1">
      @foreach ($navigation as $item)
      @php
      $isActive = request()->routeIs($item['active']);
      @endphp

      <a href="{{ route($item['route']) }}"
        @class([ 'group flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition' , ])
        style="
                        {{ $isActive
                            ? 'background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-text-primary));'
                            : 'color: rgb(var(--color-text-secondary));'
                        }}
                    ">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" @if ($isActive)
          style="color: rgb(var(--color-copper));" @endif>
          @switch($item['icon'])
          @case('home')
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M3 10.75 12 3l9 7.75v9.5a.75.75 0 0 1-.75.75h-5.5v-6h-5.5v6h-5.5A.75.75 0 0 1 3 20.25v-9.5Z" />
          </svg>
          @break

          @case('projects')
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M4 6.75A1.75 1.75 0 0 1 5.75 5h4l1.5 2h7A1.75 1.75 0 0 1 20 8.75v8.5A1.75 1.75 0 0 1 18.25 19H5.75A1.75 1.75 0 0 1 4 17.25V6.75Z" />
          </svg>
          @break

          @case('teams')
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M8.5 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm7 1a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM3 19a5.5 5.5 0 0 1 11 0M13 15.5a4.5 4.5 0 0 1 8 2.8" />
          </svg>
          @break

          @case('invitations')
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
          </svg>
          @break

          @case('notifications')
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
          </svg>
          @break

          @case('calendar')
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            aria-hidden="true">
            <rect x="3" y="4" width="18" height="16" rx="2" />
            <path d="M16 2v4M8 2v4M3 10h18" />
          </svg>
          @break

          @case('kanban')
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-12-15h18" />
          </svg>
          @break

          @case('gantt')
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18M3 9.5h12M3 14.5h15M3 19.5h9" />
          </svg>
          @break

          @case('time-tracking')
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            aria-hidden="true">
            <circle cx="12" cy="12" r="9" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3" />
          </svg>
          @break

          @case('reports')
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
          </svg>
          @break
          @endswitch
        </span>

        <span>{{ $item['label'] }}</span>

        @if ($item['icon'] === 'notifications' && Auth::user() && Auth::user()->unreadNotificationsCount() > 0)
        <span class="ms-auto flex h-5 px-1.5 items-center justify-center rounded-full text-[10px] font-bold" style="background-color: rgb(var(--color-copper)); color: #1b1511;">
          {{ Auth::user()->unreadNotificationsCount() }}
        </span>
        @elseif ($isActive)
        <span class="ms-auto h-1.5 w-1.5 rounded-full" style="background-color: rgb(var(--color-copper));"
          aria-hidden="true"></span>
        @endif
      </a>
      @endforeach
    </div>

    {{-- Planned product areas --}}
    <div class="mt-8">
      <div class="px-3 text-xs font-semibold" style="color: rgb(var(--color-text-secondary));">
        مساحة العمل
      </div>

      <div class="mt-2 space-y-1">
        <div class="flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm opacity-50"
          style="color: rgb(var(--color-text-secondary));" title="قريبًا">
          <span class="flex h-8 w-8 items-center justify-center">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
              aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 6h14M5 12h9M5 18h6" />
              <circle cx="18" cy="12" r="2" />
            </svg>
          </span>
          <span>مهامي</span>
        </div>

        <div class="flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm opacity-50"
          style="color: rgb(var(--color-text-secondary));" title="قريبًا">
          <span class="flex h-8 w-8 items-center justify-center">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
              aria-hidden="true">
              <rect x="4" y="5.5" width="16" height="14" rx="2" />
              <path d="M8 3.5v4M16 3.5v4M4 10h16" />
            </svg>
          </span>
          <span>التقويم</span>
        </div>
      </div>
    </div>
  </nav>

  {{-- Bottom navigation --}}
  <div class="px-4 pb-4">
    <a href="{{ route('profile.edit') }}"
      class="flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition"
      style="color: rgb(var(--color-text-secondary));">
      <span class="flex h-8 w-8 items-center justify-center">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
          aria-hidden="true">
          <circle cx="12" cy="8" r="3.25" />
          <path stroke-linecap="round" d="M5.5 20a6.5 6.5 0 0 1 13 0" />
        </svg>
      </span>

      <span>الحساب والإعدادات</span>
    </a>

    <div class="mt-3 border-t pt-4" style="border-color: rgb(var(--color-border));">
      <div class="flex items-center gap-3 px-2">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold" style="
                        background-color: rgb(var(--color-mineral-soft));
                        color: rgb(var(--color-mineral));
                    ">
          {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
        </div>

        <div class="min-w-0 flex-1">
          <div class="truncate text-sm font-semibold">
            {{ Auth::user()->name }}
          </div>

          <div class="truncate text-xs font-mono" style="color: rgb(var(--color-text-secondary));">
            @if (Auth::user()->username)
              {{ '@' . Auth::user()->username }}
            @else
              {{ Auth::user()->email }}
            @endif
          </div>
        </div>
      </div>

      <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button type="submit" class="flex min-h-10 w-full items-center justify-center gap-2 rounded-xl px-3 text-xs font-semibold transition hover:bg-red-500/10"
          style="background-color: rgb(var(--color-surface-soft)); color: rgb(var(--color-error)); border: 1px solid rgb(var(--color-border));">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
          </svg>
          <span>تسجيل الخروج</span>
        </button>
      </form>
    </div>
  </div>
</aside>
