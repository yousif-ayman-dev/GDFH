<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[rgb(var(--color-text-primary))]">
          التقويم والمواعيد (Enterprise Calendar)
        </h2>
        <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">
          متابعة مواعيد وتسليمات المشاريع والمهام بجداول تفاعلية.
        </p>
      </div>

      {{-- View Switcher Buttons --}}
      <div class="flex items-center gap-1 rounded-xl bg-[rgb(var(--color-surface-soft))] p-1 border border-[rgb(var(--color-border))]">
        <a href="{{ route('calendar.index', array_merge(request()->query(), ['view' => 'month'])) }}"
          class="gdfh-btn text-xs py-1 px-3 {{ $currentView === 'month' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
          الشهر (Month)
        </a>
        <a href="{{ route('calendar.index', array_merge(request()->query(), ['view' => 'week'])) }}"
          class="gdfh-btn text-xs py-1 px-3 {{ $currentView === 'week' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
          الأسبوع (Week)
        </a>
        <a href="{{ route('calendar.index', array_merge(request()->query(), ['view' => 'agenda'])) }}"
          class="gdfh-btn text-xs py-1 px-3 {{ $currentView === 'agenda' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
          الأجندة (Agenda)
        </a>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-6">
    <div class="mx-auto max-w-7xl space-y-6">

      {{-- Filters & Navigation Bar --}}
      <div class="gdfh-card p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        {{-- Month / Week Navigators --}}
        <div class="flex items-center gap-3">
          @if ($currentView === 'month')
          <a href="{{ route('calendar.index', array_merge(request()->query(), ['month' => $prev_month])) }}" class="gdfh-btn gdfh-btn-secondary text-xs px-2.5 py-1">
            &rarr; الشهر السابق
          </a>
          <span class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
            {{ $current_month->locale('ar')->translatedFormat('F Y') }}
          </span>
          <a href="{{ route('calendar.index', array_merge(request()->query(), ['month' => $next_month])) }}" class="gdfh-btn gdfh-btn-secondary text-xs px-2.5 py-1">
            الشهر التالي &larr;
          </a>
          @elseif ($currentView === 'week')
          <a href="{{ route('calendar.index', array_merge(request()->query(), ['week_start' => $prev_week])) }}" class="gdfh-btn gdfh-btn-secondary text-xs px-2.5 py-1">
            &rarr; الأسبوع السابق
          </a>
          <span class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
            {{ $start_date->format('d M') }} - {{ $end_date->format('d M Y') }}
          </span>
          <a href="{{ route('calendar.index', array_merge(request()->query(), ['week_start' => $next_week])) }}" class="gdfh-btn gdfh-btn-secondary text-xs px-2.5 py-1">
            الأسبوع التالي &larr;
          </a>
          @else
          <span class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
            جدول المواعيد القادمة
          </span>
          @endif
        </div>

        {{-- Filter Toggles --}}
        <form method="GET" action="{{ route('calendar.index') }}" class="flex flex-wrap items-center gap-3 text-xs">
          <input type="hidden" name="view" value="{{ $currentView }}">

          <select name="type" onchange="this.form.submit()" class="gdfh-btn text-xs py-1 px-2.5 bg-[rgb(var(--color-surface))] text-[rgb(var(--color-text-primary))]">
            <option value="all" {{ ($filters['type'] ?? 'all') === 'all' ? 'selected' : '' }}>جميع المصادر</option>
            <option value="project" {{ ($filters['type'] ?? '') === 'project' ? 'selected' : '' }}>المشاريع فقط</option>
            <option value="task" {{ ($filters['type'] ?? '') === 'task' ? 'selected' : '' }}>المهام فقط</option>
          </select>

          <label class="flex items-center gap-1.5 cursor-pointer">
            <input type="checkbox" name="assigned_to_me" value="1" onchange="this.form.submit()" {{ !empty($filters['assigned_to_me']) ? 'checked' : '' }} class="rounded border-gray-300 text-[rgb(var(--color-copper))] focus:ring-[rgb(var(--color-copper))]">
            <span class="text-[rgb(var(--color-text-primary))]">مهامي فقط</span>
          </label>

          <label class="flex items-center gap-1.5 cursor-pointer">
            <input type="checkbox" name="overdue" value="1" onchange="this.form.submit()" {{ !empty($filters['overdue']) ? 'checked' : '' }} class="rounded border-gray-300 text-red-500 focus:ring-red-500">
            <span class="text-red-500 font-bold">المتأخرة فقط</span>
          </label>
        </form>

      </div>

      {{-- VIEW 1: MONTH VIEW GRID --}}
      @if ($currentView === 'month')
      <div class="gdfh-card overflow-hidden">
        {{-- Weekday Headers --}}
        <div class="grid grid-cols-7 border-b border-[rgb(var(--color-border))] text-center text-xs font-bold text-[rgb(var(--color-text-secondary))] bg-[rgb(var(--color-surface-soft)/0.5)]">
          <div class="py-2.5">الأحد</div>
          <div class="py-2.5">الإثنين</div>
          <div class="py-2.5">الثلاثاء</div>
          <div class="py-2.5">الأربعاء</div>
          <div class="py-2.5">الخميس</div>
          <div class="py-2.5">الجمعة</div>
          <div class="py-2.5">السبت</div>
        </div>

        {{-- Days Grid --}}
        <div class="grid grid-cols-7 auto-rows-fr divide-x divide-y divide-[rgb(var(--color-border))] rtl:divide-x-reverse min-h-[500px]">
          @foreach ($days as $day)
          <div class="p-2 min-h-[100px] flex flex-col justify-between transition {{ $day['is_current_month'] ? 'bg-[rgb(var(--color-surface))]' : 'bg-[rgb(var(--color-surface-soft)/0.3)] text-gray-400' }} {{ $day['is_today'] ? 'ring-2 ring-inset ring-[rgb(var(--color-copper))]' : '' }}">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold {{ $day['is_today'] ? 'flex h-6 w-6 items-center justify-center rounded-full bg-[rgb(var(--color-copper))] text-[#1b1511]' : 'text-[rgb(var(--color-text-primary))]' }}">
                {{ $day['day_number'] }}
              </span>
              @if ($day['events']->count() > 0)
              <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))]">
                {{ $day['events']->count() }}
              </span>
              @endif
            </div>

            {{-- Day Events Badges --}}
            <div class="mt-2 space-y-1 overflow-y-auto max-h-[80px]">
              @foreach ($day['events']->take(3) as $event)
              <a href="{{ $event['url'] }}" title="{{ $event['title'] }}" class="block truncate rounded px-1.5 py-0.5 text-[10px] font-bold transition hover:opacity-80"
                style="{{ $event['color_category'] === 'blue' ? 'background-color: rgba(59, 130, 246, 0.15); color: #3b82f6;' : ($event['color_category'] === 'red' ? 'background-color: rgba(239, 68, 68, 0.15); color: #ef4444;' : ($event['color_category'] === 'emerald' ? 'background-color: rgba(16, 185, 129, 0.15); color: #10b981;' : 'background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));')) }}">
                {{ $event['title'] }}
              </a>
              @endforeach
              @if ($day['events']->count() > 3)
              <span class="text-[9px] text-[rgb(var(--color-text-secondary))] block text-center">+ {{ $day['events']->count() - 3 }} المزيد</span>
              @endif
            </div>
          </div>
          @endforeach
        </div>
      </div>
      @endif

      {{-- VIEW 2: WEEK VIEW --}}
      @if ($currentView === 'week')
      <div class="gdfh-card overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-7 divide-y md:divide-y-0 md:divide-x divide-[rgb(var(--color-border))] rtl:divide-x-reverse min-h-[450px]">
          @foreach ($days as $day)
          <div class="p-4 space-y-3 flex flex-col min-h-[150px] {{ $day['is_today'] ? 'bg-[rgb(var(--color-copper-soft)/0.15)]' : '' }}">
            <div class="border-b border-[rgb(var(--color-border))] pb-2 text-center">
              <span class="block text-xs font-bold text-[rgb(var(--color-text-secondary))]">{{ $day['day_name'] }}</span>
              <span class="text-sm font-bold {{ $day['is_today'] ? 'text-[rgb(var(--color-copper))]' : 'text-[rgb(var(--color-text-primary))]' }}">{{ $day['day_number'] }}</span>
            </div>

            <div class="space-y-2 flex-1">
              @forelse ($day['events'] as $event)
              <div class="p-2.5 rounded-lg border border-[rgb(var(--color-border))] space-y-1 transition hover:shadow-sm" style="background-color: rgb(var(--color-surface));">
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded" style="{{ $event['color_category'] === 'red' ? 'background-color: rgba(239, 68, 68, 0.15); color: #ef4444;' : 'background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));' }}">
                  {{ $event['type'] }}
                </span>
                <a href="{{ $event['url'] }}" class="block text-xs font-bold text-[rgb(var(--color-text-primary))] hover:text-[rgb(var(--color-copper))] truncate">
                  {{ $event['title'] }}
                </a>
              </div>
              @empty
              <div class="text-[11px] text-[rgb(var(--color-text-secondary))] text-center py-4">لا توجد مواعيد</div>
              @endforelse
            </div>
          </div>
          @endforeach
        </div>
      </div>
      @endif

      {{-- VIEW 3: AGENDA VIEW --}}
      @if ($currentView === 'agenda')
      <div class="gdfh-card overflow-hidden">
        <div class="divide-y divide-[rgb(var(--color-border))]">
          @forelse ($agenda as $date => $dateEvents)
          <div class="p-5 space-y-3">
            <div class="flex items-center gap-2">
              <span class="text-sm font-bold text-[rgb(var(--color-copper))]">{{ \Carbon\Carbon::parse($date)->locale('ar')->translatedFormat('l, d F Y') }}</span>
              <span class="text-xs px-2 py-0.5 rounded-full bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))] font-bold">{{ $dateEvents->count() }} مواعيد</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              @foreach ($dateEvents as $event)
              <div class="gdfh-card p-3 flex items-center justify-between gap-3">
                <div class="min-w-0 space-y-1">
                  <div class="flex items-center gap-2">
                    <span class="gdfh-badge text-[10px]" style="{{ $event['color_category'] === 'red' ? 'background-color: rgba(239, 68, 68, 0.15); color: #ef4444;' : 'background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));' }}">
                      {{ $event['type'] }}
                    </span>
                    <a href="{{ $event['url'] }}" class="text-xs font-bold text-[rgb(var(--color-text-primary))] hover:text-[rgb(var(--color-copper))] truncate">
                      {{ $event['title'] }}
                    </a>
                  </div>
                  <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">
                    المشروع: {{ $event['related_project']?->title ?? 'عام' }}
                  </p>
                </div>
                <a href="{{ $event['url'] }}" class="gdfh-btn gdfh-btn-secondary text-xs py-1 px-2.5">عرض</a>
              </div>
              @endforeach
            </div>
          </div>
          @empty
          <div class="p-12 text-center text-xs text-[rgb(var(--color-text-secondary))]">لا توجد مواعيد مضافة في الأجندة حالياً.</div>
          @endforelse
        </div>
      </div>
      @endif

    </div>
  </div>
</x-app-layout>
