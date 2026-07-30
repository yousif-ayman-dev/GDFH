<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-1">
      <span class="text-xs font-semibold text-[rgb(var(--color-copper))]">
        مساحة العمل
      </span>

      <h1 class="text-xl font-bold text-[rgb(var(--color-text-primary))]">
        المشاريع
      </h1>
    </div>
  </x-slot>

  <div class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <div class="mx-auto max-w-[1440px]">

      {{-- Success message --}}
      @if (session('success'))
      <div
        class="mb-6 flex items-start gap-3 rounded-xl border border-[rgb(var(--color-success)/0.25)] bg-[rgb(var(--color-success)/0.08)] px-4 py-3"
        role="alert">
        <div
          class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[rgb(var(--color-success)/0.12)] text-[rgb(var(--color-success))]">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            aria-hidden="true">
            <path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </div>

        <div class="min-w-0">
          <p class="text-sm font-semibold text-[rgb(var(--color-text-primary))]">
            تم تنفيذ العملية بنجاح
          </p>

          <p class="mt-0.5 text-xs leading-6 text-[rgb(var(--color-text-secondary))]">
            {{ session('success') }}
          </p>
        </div>
      </div>
      @endif

      {{-- Page introduction --}}
      <section
        class="mb-7 flex flex-col gap-5 border-b border-[rgb(var(--color-border))] pb-7 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-2xl">
          <div class="mb-3 flex items-center gap-3">
            <span class="h-px w-7 bg-[rgb(var(--color-copper))]"></span>

            <span class="text-xs font-semibold text-[rgb(var(--color-copper))]">
              إدارة المشاريع
            </span>
          </div>

          <h2 class="text-2xl font-bold tracking-tight text-[rgb(var(--color-text-primary))] sm:text-3xl">
            مشاريعك في مكان واحد
          </h2>

          <p class="mt-3 max-w-xl text-sm leading-7 text-[rgb(var(--color-text-secondary))]">
            تابع المشاريع التي تديرها، راقب حالتها، وادخل إلى مساحة كل مشروع
            لإدارة المهام والأعضاء والتفاصيل.
          </p>
        </div>

        <a href="{{ route('projects.create') }}" class="gdfh-btn gdfh-btn-brand w-full sm:w-auto">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            aria-hidden="true">
            <path d="M12 5v14M5 12h14" stroke-linecap="round" />
          </svg>

          <span>مشروع جديد</span>
        </a>
      </section>

      {{-- Summary --}}
      <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <h3 class="text-base font-bold text-[rgb(var(--color-text-primary))]">
            جميع المشاريع
          </h3>

          <span
            class="inline-flex min-w-7 items-center justify-center rounded-full bg-[rgb(var(--color-copper-soft))] px-2 py-1 text-xs font-bold text-[rgb(var(--color-copper))]">
            {{ $projects->total() }}
          </span>
        </div>

        @if ($projects->total() > 0)
        <p class="text-xs text-[rgb(var(--color-text-secondary))]">
          عرض
          {{ $projects->firstItem() }}
          –
          {{ $projects->lastItem() }}
          من
          {{ $projects->total() }}
          مشروع
        </p>
        @endif
      </div>

      @forelse ($projects as $project)
      @if ($loop->first)
      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @endif

        @php
        $statusLabels = [
        'draft' => 'مسودة',
        'open' => 'مفتوح',
        'in_progress' => 'قيد التنفيذ',
        'on_hold' => 'متوقف مؤقتًا',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغي',
        ];

        $visibilityLabels = [
        'private' => 'خاص',
        'marketplace' => 'السوق',
        ];

        $statusLabel = $statusLabels[$project->status] ?? $project->status;
        $visibilityLabel = $visibilityLabels[$project->visibility] ?? $project->visibility;

        $statusClasses = match ($project->status) {
        'completed' =>
        'bg-[rgb(var(--color-success)/0.10)] text-[rgb(var(--color-success))]',
        'on_hold' =>
        'bg-[rgb(var(--color-warning)/0.10)] text-[rgb(var(--color-warning))]',
        'cancelled' =>
        'bg-[rgb(var(--color-error)/0.10)] text-[rgb(var(--color-error))]',
        'in_progress' =>
        'bg-[rgb(var(--color-mineral-soft))] text-[rgb(var(--color-mineral))]',
        'open' =>
        'bg-[rgb(var(--color-info)/0.10)] text-[rgb(var(--color-info))]',
        default =>
        'bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))]',
        };
        @endphp

        <article
          class="group relative flex min-h-[250px] flex-col overflow-hidden rounded-2xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] transition duration-200 hover:-translate-y-0.5 hover:border-[rgb(var(--color-copper)/0.55)] hover:shadow-[0_14px_36px_rgb(0_0_0/0.06)] dark:hover:shadow-[0_14px_36px_rgb(0_0_0/0.20)]">
          {{-- Decorative accent --}}
          <div
            class="pointer-events-none absolute -bottom-16 -left-16 h-36 w-36 rounded-full border border-[rgb(var(--color-copper)/0.12)]">
          </div>

          <div class="relative flex flex-1 flex-col p-5">
            <div class="mb-5 flex items-start justify-between gap-4">
              <div
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))]">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                  aria-hidden="true">
                  <path
                    d="M3.75 6.75A1.75 1.75 0 0 1 5.5 5h4l2 2h7A1.75 1.75 0 0 1 20.25 8.75v8.75a1.75 1.75 0 0 1-1.75 1.75h-13a1.75 1.75 0 0 1-1.75-1.75V6.75Z"
                    stroke-linejoin="round" />
                </svg>
              </div>

              <span class="gdfh-badge {{ $statusClasses }}">
                {{ $statusLabel }}
              </span>
            </div>

            <div class="flex-1">
              @if ($project->category)
              <p class="mb-2 text-[11px] font-semibold text-[rgb(var(--color-copper))]">
                {{ $project->category }}
              </p>
              @endif

              <h4 class="text-lg font-bold leading-7 text-[rgb(var(--color-text-primary))]">
                <a href="{{ route('projects.show', $project) }}"
                  class="transition hover:text-[rgb(var(--color-copper))]">
                  {{ $project->title }}
                </a>
              </h4>

              <p class="mt-3 line-clamp-2 text-sm leading-6 text-[rgb(var(--color-text-secondary))]">
                {{ $project->description }}
              </p>
            </div>

            <div class="mt-6 flex items-center justify-between gap-3 border-t border-[rgb(var(--color-border))] pt-4">
              <div class="flex items-center gap-2 text-xs text-[rgb(var(--color-text-secondary))]">
                @if ($project->visibility === 'private')
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                  aria-hidden="true">
                  <rect x="5" y="10" width="14" height="10" rx="2" />
                  <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                </svg>
                @else
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                  aria-hidden="true">
                  <circle cx="12" cy="12" r="9" />
                  <path
                    d="M3 12h18M12 3c2.2 2.4 3.3 5.4 3.3 9S14.2 18.6 12 21M12 3C9.8 5.4 8.7 8.4 8.7 12S9.8 18.6 12 21" />
                </svg>
                @endif

                <span>{{ $visibilityLabel }}</span>
              </div>

              <div class="flex items-center gap-2">
                <a href="{{ route('projects.edit', $project) }}"
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[rgb(var(--color-border))] text-[rgb(var(--color-text-secondary))] transition hover:border-[rgb(var(--color-copper)/0.5)] hover:bg-[rgb(var(--color-copper-soft))] hover:text-[rgb(var(--color-copper))]"
                  title="تعديل المشروع" aria-label="تعديل {{ $project->title }}">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    aria-hidden="true">
                    <path d="m14.5 5.5 4 4M4 20l3.5-.8L19 7.7a1.4 1.4 0 0 0 0-2l-.7-.7a1.4 1.4 0 0 0-2 0L4.8 16.5 4 20Z"
                      stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </a>

                <a href="{{ route('projects.show', $project) }}"
                  class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold text-[rgb(var(--color-text-primary))] transition hover:bg-[rgb(var(--color-surface-soft))]">
                  <span>فتح المشروع</span>

                  <svg class="h-3.5 w-3.5 rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.8" aria-hidden="true">
                    <path d="m9 18 6-6-6-6" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </a>
              </div>
            </div>
          </div>
        </article>

        @if ($loop->last)
      </div>
      @endif

      @empty
      {{-- Empty state --}}
      <section
        class="relative overflow-hidden rounded-2xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] px-6 py-16 text-center sm:py-20">
        <div
          class="pointer-events-none absolute left-1/2 top-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rounded-full border border-[rgb(var(--color-copper)/0.08)]">
        </div>

        <div
          class="pointer-events-none absolute left-1/2 top-1/2 h-44 w-44 -translate-x-1/2 -translate-y-1/2 rounded-full border border-[rgb(var(--color-copper)/0.10)]">
        </div>

        <div class="relative mx-auto max-w-md">
          <div
            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))]">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
              aria-hidden="true">
              <path
                d="M3.75 6.75A1.75 1.75 0 0 1 5.5 5h4l2 2h7A1.75 1.75 0 0 1 20.25 8.75v8.75a1.75 1.75 0 0 1-1.75 1.75h-13a1.75 1.75 0 0 1-1.75-1.75V6.75Z"
                stroke-linejoin="round" />
              <path d="M12 11v5M9.5 13.5h5" stroke-linecap="round" />
            </svg>
          </div>

          <h3 class="mt-5 text-lg font-bold text-[rgb(var(--color-text-primary))]">
            ابدأ أول مشروع لك
          </h3>

          <p class="mx-auto mt-2 max-w-sm text-sm leading-7 text-[rgb(var(--color-text-secondary))]">
            لا توجد لديك مشاريع حتى الآن. أنشئ مشروعًا جديدًا وابدأ
            بتنظيم العمل والمهام والفريق من مساحة واحدة.
          </p>

          <a href="{{ route('projects.create') }}" class="gdfh-btn gdfh-btn-brand mt-6">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
              aria-hidden="true">
              <path d="M12 5v14M5 12h14" stroke-linecap="round" />
            </svg>

            إنشاء مشروع
          </a>
        </div>
      </section>
      @endforelse

      {{-- Pagination --}}
      @if ($projects->hasPages())
      <div class="mt-7 border-t border-[rgb(var(--color-border))] pt-6">
        {{ $projects->links() }}
      </div>
      @endif

    </div>
  </div>
</x-app-layout>
