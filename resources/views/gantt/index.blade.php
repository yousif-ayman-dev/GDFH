<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[rgb(var(--color-text-primary))]">
          مخطط غانت المتقدم (Enterprise Gantt Chart)
        </h2>
        <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">
          التسلسل الزمني، التبعيات، ومسار الإنجاز للمشاريع والمهام.
        </p>
      </div>

      {{-- Zoom Controls --}}
      <div class="flex items-center gap-1 rounded-xl bg-[rgb(var(--color-surface-soft))] p-1 border border-[rgb(var(--color-border))]">
        <a href="{{ route('gantt.index', array_merge(request()->query(), ['zoom' => 'day'])) }}"
          class="gdfh-btn text-xs py-1 px-3 {{ $zoom === 'day' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
          يوم (Day)
        </a>
        <a href="{{ route('gantt.index', array_merge(request()->query(), ['zoom' => 'week'])) }}"
          class="gdfh-btn text-xs py-1 px-3 {{ $zoom === 'week' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
          أسبوع (Week)
        </a>
        <a href="{{ route('gantt.index', array_merge(request()->query(), ['zoom' => 'month'])) }}"
          class="gdfh-btn text-xs py-1 px-3 {{ $zoom === 'month' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
          شهر (Month)
        </a>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-6">
    <div class="mx-auto max-w-7xl space-y-6">

      {{-- Search & Filters Toolbar --}}
      <form method="GET" action="{{ route('gantt.index') }}" class="gdfh-card p-4 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        <input type="hidden" name="zoom" value="{{ $zoom }}">

        <div class="relative flex-1 max-w-md">
          <svg class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[rgb(var(--color-text-secondary))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="ابحث باسم المشروع أو المهمة..." class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] ps-9 pe-4 py-2 text-xs text-[rgb(var(--color-text-primary))] placeholder:text-[rgb(var(--color-text-secondary))] focus:border-[rgb(var(--color-copper))] focus:outline-none focus:ring-1 focus:ring-[rgb(var(--color-copper))]">
        </div>

        <div class="flex items-center gap-3 text-xs">
          <select name="project_id" onchange="this.form.submit()" class="gdfh-btn text-xs py-1.5 px-3 bg-[rgb(var(--color-surface))] text-[rgb(var(--color-text-primary))]">
            <option value="">جميع المشاريع</option>
            @foreach ($user_projects as $proj)
            <option value="{{ $proj->id }}" {{ ($filters['project_id'] ?? '') == $proj->id ? 'selected' : '' }}>{{ $proj->title }}</option>
            @endforeach
          </select>

          @if (!empty($filters['search']) || !empty($filters['project_id']))
          <a href="{{ route('gantt.index', ['zoom' => $zoom]) }}" class="text-xs font-bold text-[rgb(var(--color-copper))] hover:underline">إعادة ضبط</a>
          @endif
        </div>
      </form>

      {{-- Gantt Chart Grid --}}
      <div class="gdfh-card overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
          <div class="min-w-[1000px]">
            
            {{-- Timeline Header Row --}}
            <div class="flex border-b border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface-soft)/0.6)] text-xs font-bold">
              {{-- Sticky Left Info Column Header --}}
              <div class="w-80 shrink-0 p-3 border-e border-[rgb(var(--color-border))] text-[rgb(var(--color-text-primary))] bg-[rgb(var(--color-surface-soft))]">
                المشروع / المهمة
              </div>

              {{-- Timeline Timeline Columns --}}
              <div class="flex-1 flex divide-x divide-[rgb(var(--color-border))] rtl:divide-x-reverse">
                @foreach ($column_headers as $col)
                <div class="flex-1 p-3 text-center min-w-[80px] text-[11px] {{ $col['is_today'] ? 'bg-[rgb(var(--color-copper-soft)/0.3)] text-[rgb(var(--color-copper))]' : 'text-[rgb(var(--color-text-secondary))]' }}">
                  {{ $col['label'] }}
                </div>
                @endforeach
              </div>
            </div>

            {{-- Projects & Tasks Body Rows --}}
            <div class="divide-y divide-[rgb(var(--color-border))]">
              @forelse ($projects as $project)
              
              {{-- Project Row --}}
              <div class="flex items-center hover:bg-[rgb(var(--color-surface-soft)/0.3)] transition bg-[rgb(var(--color-surface))]">
                {{-- Left Info Column --}}
                <div class="w-80 shrink-0 p-3 border-e border-[rgb(var(--color-border))] space-y-1">
                  <a href="{{ $project['url'] }}" class="text-xs font-bold text-[rgb(var(--color-text-primary))] hover:text-[rgb(var(--color-copper))] truncate block">
                    📂 {{ $project['title'] }}
                  </a>
                  <div class="flex items-center gap-2 text-[10px] text-[rgb(var(--color-text-secondary))]">
                    <span>{{ $project['duration'] }} يوم</span>
                    <span>· الإنجاز: {{ $project['progress'] }}%</span>
                    @if ($project['overdue'])
                    <span class="text-red-500 font-bold">متأخر</span>
                    @endif
                  </div>
                </div>

                {{-- Right Gantt Bar Canvas --}}
                <div class="flex-1 p-3 relative h-12 flex items-center">
                  <div class="w-full relative h-6 rounded-lg bg-[rgb(var(--color-surface-soft))] overflow-hidden">
                    <div class="h-full rounded-lg transition-all duration-300 flex items-center justify-between px-2 text-[10px] font-bold text-white shadow-sm {{ $project['overdue'] ? 'bg-red-500' : 'bg-[rgb(var(--color-copper))]' }}"
                      style="width: {{ max(10, min(100, $project['progress'])) }}%;">
                      <span class="truncate">{{ $project['title'] }}</span>
                      <span>{{ $project['progress'] }}%</span>
                    </div>
                  </div>
                </div>
              </div>

              {{-- Tasks Rows --}}
              @foreach ($project['tasks'] as $task)
              <div class="flex items-center hover:bg-[rgb(var(--color-surface-soft)/0.2)] transition bg-[rgb(var(--color-surface)/0.5)] text-xs">
                {{-- Left Task Info Column --}}
                <div class="w-80 shrink-0 ps-7 pe-3 py-2.5 border-e border-[rgb(var(--color-border))] space-y-0.5">
                  <a href="{{ $task['url'] }}" class="text-xs text-[rgb(var(--color-text-primary))] hover:text-[rgb(var(--color-copper))] truncate block">
                    ↳ {{ $task['title'] }}
                  </a>
                  <div class="flex items-center gap-2 text-[10px] text-[rgb(var(--color-text-secondary))]">
                    <span>المسند: {{ $task['assignee'] }}</span>
                    <span>· {{ $task['duration'] }} يوم</span>
                    @if ($task['overdue'])
                    <span class="text-red-500 font-bold">متأخر</span>
                    @endif
                  </div>
                </div>

                {{-- Right Task Bar Canvas --}}
                <div class="flex-1 px-3 py-2.5 relative h-10 flex items-center">
                  @if ($task['is_milestone'])
                  <div class="flex items-center gap-1.5 text-amber-500 font-bold text-xs">
                    <span class="rotate-45 block h-3.5 w-3.5 bg-amber-500 rounded-sm"></span>
                    <span>محطة رئيسية (Milestone)</span>
                  </div>
                  @else
                  <div class="w-full relative h-4 rounded bg-[rgb(var(--color-surface-soft))] overflow-hidden">
                    <div class="h-full rounded transition-all duration-300 {{ $task['completion'] ? 'bg-emerald-500' : ($task['overdue'] ? 'bg-red-500' : 'bg-blue-500') }}"
                      style="width: {{ max(15, min(100, $task['progress'] > 0 ? $task['progress'] : 30)) }}%;">
                    </div>
                  </div>
                  @endif
                </div>
              </div>
              @endforeach

              @empty
              <div class="p-12 text-center text-xs text-[rgb(var(--color-text-secondary))]">
                لا توجد بيانات مخطط غانت لعرضها وفقاً للفلتر المطبق.
              </div>
              @endforelse
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>
</x-app-layout>
