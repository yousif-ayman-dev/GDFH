<x-app-layout>
  @php
  $statusLabels = [
  'todo' => 'قيد الانتظار',
  'in_progress' => 'قيد التنفيذ',
  'in_review' => 'قيد المراجعة',
  'completed' => 'مكتمل',
  'cancelled' => 'ملغي',
  ];

  $priorityLabels = [
  'low' => 'منخفضة',
  'medium' => 'متوسطة',
  'high' => 'عالية',
  'urgent' => 'عاجلة',
  ];

  $priorityBadges = [
  'low' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
  'medium' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
  'high' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
  'urgent' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
  ];

  $statusBadges = [
  'todo' => 'bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))]',
  'in_progress' => 'bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))]',
  'in_review' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
  'completed' => 'bg-[rgb(var(--color-success)/0.15)] text-[rgb(var(--color-success))]',
  'cancelled' => 'bg-[rgb(var(--color-error)/0.15)] text-[rgb(var(--color-error))]',
  ];
  @endphp

  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="min-w-0">
        <div class="flex items-center gap-2 text-xs font-medium text-[rgb(var(--color-text-secondary))]">
          <a href="{{ route('projects.index') }}" class="transition hover:text-[rgb(var(--color-copper))]">المشاريع</a>
          <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 18l-6-6 6-6" /></svg>
          <a href="{{ route('projects.show', $project) }}" class="transition hover:text-[rgb(var(--color-copper))]">{{ $project->title }}</a>
          <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 18l-6-6 6-6" /></svg>
          <a href="{{ route('projects.tasks.index', $project) }}" class="transition hover:text-[rgb(var(--color-copper))]">المهام</a>
          <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 18l-6-6 6-6" /></svg>
          <span class="truncate text-[rgb(var(--color-text-primary))]">{{ $task->title }}</span>
        </div>

        <h2 class="mt-1 truncate text-xl font-bold text-[rgb(var(--color-text-primary))]">
          {{ $task->title }}
        </h2>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('projects.tasks.edit', [$project, $task]) }}" class="gdfh-btn gdfh-btn-brand">
          تعديل المهمة
        </a>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
    <div class="mx-auto max-w-4xl space-y-6">
      @if (session('success'))
      <div class="flex items-start gap-3 rounded-xl border border-[rgb(var(--color-success)/0.25)] bg-[rgb(var(--color-success)/0.08)] px-4 py-3" role="alert">
        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[rgb(var(--color-success)/0.12)] text-[rgb(var(--color-success))]">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m5 12 4 4L19 6" /></svg>
        </div>
        <div class="min-w-0">
          <p class="text-sm font-semibold text-[rgb(var(--color-text-primary))]">تمت العملية بنجاح</p>
          <p class="mt-0.5 text-xs text-[rgb(var(--color-text-secondary))]">{{ session('success') }}</p>
        </div>
      </div>
      @endif

      <div class="gdfh-card p-6 sm:p-8 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[rgb(var(--color-border))] pb-6">
          <div class="flex flex-wrap items-center gap-2">
            <span class="gdfh-badge {{ $statusBadges[$task->status] ?? '' }}">
              {{ $statusLabels[$task->status] ?? $task->status }}
            </span>

            <span class="gdfh-badge {{ $priorityBadges[$task->priority] ?? '' }}">
              الأولوية: {{ $priorityLabels[$task->priority] ?? $task->priority }}
            </span>

            @if ($task->team)
            <span class="gdfh-badge bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))] flex items-center gap-1">
              <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
              </svg>
              {{ $task->team->name }}
            </span>
            @endif
          </div>

          <div class="text-xs text-[rgb(var(--color-text-secondary))]">
            تاريخ الإنشاء: {{ $task->created_at->format('Y/m/d') }}
          </div>
        </div>

        <div>
          <h3 class="text-xs font-semibold text-[rgb(var(--color-text-secondary))] uppercase tracking-wider mb-2">الوصف</h3>
          <p class="text-sm leading-8 text-[rgb(var(--color-text-primary))] whitespace-pre-line">
            {{ $task->description ?: 'لا يوجد وصف متاح لهذه المهمة.' }}
          </p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 border-t border-[rgb(var(--color-border))] pt-6">
          <div>
            <span class="text-xs text-[rgb(var(--color-text-secondary))] block">المكلف بالمهمة</span>
            <span class="text-sm font-semibold text-[rgb(var(--color-text-primary))]">
              {{ $task->assignedUser?->name ?? 'غير مكلف' }}
            </span>
          </div>

          <div>
            <span class="text-xs text-[rgb(var(--color-text-secondary))] block">الفريق</span>
            <span class="text-sm font-semibold text-[rgb(var(--color-text-primary))]">
              {{ $task->team?->name ?? 'مهمة عامة للمشروع' }}
            </span>
          </div>

          <div>
            <span class="text-xs text-[rgb(var(--color-text-secondary))] block">تاريخ الاستحقاق</span>
            <span class="text-sm font-semibold text-[rgb(var(--color-text-primary))]">
              {{ $task->due_at ? $task->due_at->format('Y/m/d') : 'غير محدد' }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
