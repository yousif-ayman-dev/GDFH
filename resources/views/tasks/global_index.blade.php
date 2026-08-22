<x-app-layout>
  @php
  $statusLabels = [
    'todo' => 'قيد الانتظار',
    'in_progress' => 'قيد التنفيذ',
    'in_review' => 'قيد المراجعة',
    'completed' => 'مكتملة',
    'cancelled' => 'ملغاة',
  ];

  $priorityLabels = [
    'low' => 'منخفضة',
    'medium' => 'متوسطة',
    'high' => 'عالية',
    'urgent' => 'عاجلة',
  ];

  $statusBadges = [
    'todo' => 'bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))]',
    'in_progress' => 'bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))] font-bold',
    'in_review' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 font-bold',
    'completed' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold',
    'cancelled' => 'bg-red-500/10 text-red-500 font-bold',
  ];
  @endphp

  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[rgb(var(--color-text-primary))]">
          جميع المهام (Enterprise Task Center)
        </h2>
        <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">
          إدارة، تصفية ومتابعة كافّة مهام المشاريع والفرق عبر منصّة واحدة.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <span class="gdfh-badge text-xs font-bold" style="background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));">
          إجمالي المهام: {{ $tasks->total() }}
        </span>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-6">
    <div class="mx-auto max-w-7xl space-y-6">

      {{-- Search & Filter Toolbar --}}
      <form method="GET" action="{{ route('tasks.index') }}" class="gdfh-card p-4 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        
        {{-- Search Input --}}
        <div class="relative flex-1 max-w-md">
          <svg class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[rgb(var(--color-text-secondary))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="ابحث باسم المهمة أو الوصف..." class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] ps-9 pe-4 py-2 text-xs text-[rgb(var(--color-text-primary))] placeholder:text-[rgb(var(--color-text-secondary))] focus:border-[rgb(var(--color-copper))] focus:outline-none focus:ring-1 focus:ring-[rgb(var(--color-copper))]">
        </div>

        {{-- Dropdown Filters --}}
        <div class="flex flex-wrap items-center gap-3 text-xs">
          {{-- Project Filter --}}
          <select name="project_id" onchange="this.form.submit()" class="gdfh-btn text-xs py-1.5 px-3 bg-[rgb(var(--color-surface))] text-[rgb(var(--color-text-primary))]">
            <option value="">جميع المشاريع</option>
            @foreach ($userProjects as $proj)
            <option value="{{ $proj->id }}" {{ ($filters['project_id'] ?? '') == $proj->id ? 'selected' : '' }}>{{ $proj->title }}</option>
            @endforeach
          </select>

          {{-- Status Filter --}}
          <select name="status" onchange="this.form.submit()" class="gdfh-btn text-xs py-1.5 px-3 bg-[rgb(var(--color-surface))] text-[rgb(var(--color-text-primary))]">
            <option value="">جميع الحالات</option>
            <option value="todo" {{ ($filters['status'] ?? '') === 'todo' ? 'selected' : '' }}>قيد الانتظار</option>
            <option value="in_progress" {{ ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
            <option value="in_review" {{ ($filters['status'] ?? '') === 'in_review' ? 'selected' : '' }}>قيد المراجعة</option>
            <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>مكتملة</option>
          </select>

          {{-- Priority Filter --}}
          <select name="priority" onchange="this.form.submit()" class="gdfh-btn text-xs py-1.5 px-3 bg-[rgb(var(--color-surface))] text-[rgb(var(--color-text-primary))]">
            <option value="">جميع الأولويات</option>
            <option value="urgent" {{ ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' }}>عاجل</option>
            <option value="high" {{ ($filters['priority'] ?? '') === 'high' ? 'selected' : '' }}>عالية</option>
            <option value="medium" {{ ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' }}>متوسطة</option>
            <option value="low" {{ ($filters['priority'] ?? '') === 'low' ? 'selected' : '' }}>منخفضة</option>
          </select>

          {{-- Overdue Checkbox --}}
          <label class="flex items-center gap-1.5 cursor-pointer">
            <input type="checkbox" name="overdue" value="1" onchange="this.form.submit()" {{ !empty($filters['overdue']) ? 'checked' : '' }} class="rounded border-gray-300 text-red-500 focus:ring-red-500">
            <span class="text-red-500 font-bold">المتأخرة فقط</span>
          </label>

          @if (!empty($filters['search']) || !empty($filters['project_id']) || !empty($filters['status']) || !empty($filters['priority']) || !empty($filters['overdue']))
          <a href="{{ route('tasks.index') }}" class="text-xs font-bold text-[rgb(var(--color-copper))] hover:underline">إعادة ضبط</a>
          @endif
        </div>
      </form>

      {{-- Tasks List View --}}
      <div class="gdfh-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-[rgb(var(--color-border))] px-5 py-4 sm:px-6">
          <h3 class="text-base font-bold text-[rgb(var(--color-text-primary))]">سجل المهام</h3>
          <span class="gdfh-badge bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))]">
            {{ $tasks->total() }} مهمة
          </span>
        </div>

        @if ($tasks->isEmpty())
        <div class="gdfh-empty-state">
          <div class="gdfh-empty-icon">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <h3>لا توجد مهام مطابقة</h3>
          <p>جرّب تعديل الفلاتر أو كلمات البحث للوصول للمهام المطلوبة.</p>
        </div>
        @else
        <div class="divide-y divide-[rgb(var(--color-border))]">
          @foreach ($tasks as $task)
          <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between hover:bg-[rgb(var(--color-surface-soft)/0.4)] transition">
            <div class="flex items-start gap-4 min-w-0">
              <div class="mt-1">
                <span class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))]">
                  {{ mb_substr($task->assignee?->name ?? 'غ', 0, 1) }}
                </span>
              </div>

              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}" class="text-sm font-bold text-[rgb(var(--color-text-primary))] hover:text-[rgb(var(--color-copper))] truncate">
                    {{ $task->title }}
                  </a>
                  
                  <span class="gdfh-badge text-[10px] {{ $statusBadges[$task->status] ?? $statusBadges['todo'] }}">
                    {{ $statusLabels[$task->status] ?? $task->status }}
                  </span>

                  <span class="gdfh-badge text-[10px] {{ $task->priority === 'urgent' ? 'bg-red-500/10 text-red-500 font-bold' : ($task->priority === 'high' ? 'bg-amber-500/10 text-amber-500 font-bold' : 'bg-gray-500/10 text-gray-500') }}">
                    {{ $priorityLabels[$task->priority] ?? $task->priority }}
                  </span>
                </div>

                @if ($task->description)
                <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))] line-clamp-1">
                  {{ $task->description }}
                </p>
                @endif

                <div class="mt-2 flex flex-wrap items-center gap-3 text-[11px] text-[rgb(var(--color-text-secondary))]">
                  <span>المشروع: <a href="{{ route('projects.show', $task->project) }}" class="font-bold text-[rgb(var(--color-text-primary))] hover:underline">{{ $task->project?->title }}</a></span>
                  <span>•</span>
                  <span>المُسند: <strong class="text-[rgb(var(--color-text-primary))]">{{ $task->assignee?->name ?? 'غير مُسند' }}</strong></span>
                  @if ($task->due_at)
                  <span>•</span>
                  <span class="{{ $task->isLate() ? 'text-red-500 font-bold' : '' }}">
                    التاريخ: {{ $task->due_at->format('Y-m-d') }}
                  </span>
                  @endif
                </div>
              </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
              <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}" class="gdfh-btn gdfh-btn-secondary text-xs py-1.5 px-3">
                عرض التفاصيل
              </a>
            </div>
          </div>
          @endforeach
        </div>

        @if ($tasks->hasPages())
        <div class="border-t border-[rgb(var(--color-border))] p-4">
          {{ $tasks->links() }}
        </div>
        @endif
        @endif
      </div>

    </div>
  </div>
</x-app-layout>
