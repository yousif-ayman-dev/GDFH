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
          <a href="{{ route('projects.index') }}" class="transition hover:text-[rgb(var(--color-copper))]">
            المشاريع
          </a>

          <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6" />
          </svg>

          <a href="{{ route('projects.show', $project) }}" class="transition hover:text-[rgb(var(--color-copper))]">
            {{ $project->title }}
          </a>

          <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6" />
          </svg>

          <span class="truncate text-[rgb(var(--color-text-primary))]">المهام</span>
        </div>

        <h2 class="mt-1 truncate text-xl font-bold text-[rgb(var(--color-text-primary))]">
          مهام مشروع: {{ $project->title }}
        </h2>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('projects.show', $project) }}" class="gdfh-btn gdfh-btn-secondary">
          <span>العودة للمشروع</span>
        </a>

        <a href="{{ route('projects.tasks.create', $project) }}" class="gdfh-btn gdfh-btn-brand">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" d="M12 5v14M5 12h14" />
          </svg>

          <span>مهمة جديدة</span>
        </a>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
    <div class="mx-auto max-w-7xl">

      @if (session('success'))
      <div class="mb-6 flex items-start gap-3 rounded-xl border border-[rgb(var(--color-success)/0.25)] bg-[rgb(var(--color-success)/0.08)] px-4 py-3" role="alert">
        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[rgb(var(--color-success)/0.12)] text-[rgb(var(--color-success))]">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </div>

        <div class="min-w-0">
          <p class="text-sm font-semibold text-[rgb(var(--color-text-primary))]">تمت العملية بنجاح</p>
          <p class="mt-0.5 text-xs text-[rgb(var(--color-text-secondary))]">{{ session('success') }}</p>
        </div>
      </div>
      @endif

      <div class="gdfh-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-[rgb(var(--color-border))] px-5 py-4 sm:px-6">
          <h3 class="text-base font-bold text-[rgb(var(--color-text-primary))]">قائمة المهام</h3>
          <span class="gdfh-badge bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))]">
            {{ $tasks->total() }} مهمة
          </span>
        </div>

        <div class="divide-y divide-[rgb(var(--color-border))]">
          @forelse ($tasks as $task)
          <div class="flex flex-col gap-4 p-5 transition hover:bg-[rgb(var(--color-surface-soft)/0.4)] sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="text-base font-bold text-[rgb(var(--color-text-primary))] transition hover:text-[rgb(var(--color-copper))]">
                  {{ $task->title }}
                </a>

                <span class="gdfh-badge {{ $statusBadges[$task->status] ?? '' }}">
                  {{ $statusLabels[$task->status] ?? $task->status }}
                </span>

                <span class="gdfh-badge {{ $priorityBadges[$task->priority] ?? '' }}">
                  {{ $priorityLabels[$task->priority] ?? $task->priority }}
                </span>

                @if ($task->team)
                <span class="gdfh-badge inline-flex items-center gap-1 bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))]">
                  <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                  </svg>
                  {{ $task->team->name }}
                </span>
                @endif
              </div>

              @if ($task->description)
              <p class="mt-2 line-clamp-2 text-xs leading-6 text-[rgb(var(--color-text-secondary))]">
                {{ $task->description }}
              </p>
              @endif

              <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-[rgb(var(--color-text-secondary))]">
                @if ($task->assignedUser)
                <div class="flex items-center gap-1.5">
                  <span class="font-medium">المكلف:</span>
                  <span class="font-semibold text-[rgb(var(--color-text-primary))]">{{ $task->assignedUser->name }}</span>
                </div>
                @endif

                @if ($task->due_at)
                <div class="flex items-center gap-1.5">
                  <span class="font-medium">تاريخ الاستحقاق:</span>
                  <span class="font-semibold text-[rgb(var(--color-text-primary))]">{{ $task->due_at->format('Y/m/d') }}</span>
                </div>
                @endif
              </div>
            </div>

            <div class="flex items-center gap-2 self-end sm:self-center">
              <a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="gdfh-btn gdfh-btn-secondary py-1.5 text-xs">
                عرض
              </a>

              <a href="{{ route('projects.tasks.edit', [$project, $task]) }}" class="gdfh-btn gdfh-btn-secondary py-1.5 text-xs">
                تعديل
              </a>

              <form method="POST" action="{{ route('projects.tasks.destroy', [$project, $task]) }}" onsubmit="return confirm('هل تريد حذف هذه المهمة؟')">
                @csrf
                @method('DELETE')
                <button type="submit" class="gdfh-btn py-1.5 text-xs text-[rgb(var(--color-error))] hover:bg-[rgb(var(--color-error)/0.1)]">
                  حذف
                </button>
              </form>
            </div>
          </div>
          @empty
          <div class="px-6 py-12 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))]">
              <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="3" y="4" width="18" height="16" rx="2" />
                <path d="M8 9h8M8 13h5" />
              </svg>
            </div>
            <h3 class="mt-4 text-base font-bold text-[rgb(var(--color-text-primary))]">لا توجد مهام بعد</h3>
            <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">ابدأ بإضافة أول مهمة لهذا المشروع.</p>
            <div class="mt-5">
              <a href="{{ route('projects.tasks.create', $project) }}" class="gdfh-btn gdfh-btn-brand">
                إضافة مهمة جديدة
              </a>
            </div>
          </div>
          @endforelse
        </div>

        @if ($tasks->hasPages())
        <div class="border-t border-[rgb(var(--color-border))] p-4">
          {{ $tasks->links() }}
        </div>
        @endif
      </div>
    </div>
  </div>
</x-app-layout>
