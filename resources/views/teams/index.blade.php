<x-app-layout>
  @php
  $typeLabels = [
  'engineering' => 'هندسة',
  'design' => 'تصميم',
  'product' => 'منتج',
  'marketing' => 'تسويق',
  'operations' => 'عمليات',
  'general' => 'عام',
  ];

  $visibilityLabels = [
  'private' => 'خاص',
  'public' => 'عام',
  'internal' => 'داخلي',
  ];
  @endphp

  <x-slot name="header">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <div class="mb-2 flex items-center gap-2 text-xs font-semibold text-[rgb(var(--color-copper))]">
          <span class="h-px w-6 bg-[rgb(var(--color-copper))]"></span>
          <span>الإدارة</span>
        </div>

        <h2 class="text-xl font-bold text-[rgb(var(--color-text-primary))]">
          الفرق
        </h2>

        <p class="mt-1 text-sm text-[rgb(var(--color-text-secondary))]">
          تابع الفرق التي تديرها أو تعمل معها، وانظم إلى العمل بكفاءة.
        </p>
      </div>

      <a href="{{ route('teams.create') }}" class="gdfh-btn gdfh-btn-brand w-full sm:w-auto">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
          aria-hidden="true">
          <path d="M12 5v14M5 12h14" stroke-linecap="round" />
        </svg>

        <span>إنشاء فريق</span>
      </a>
    </div>
  </x-slot>

  <div class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <div class="mx-auto max-w-7xl">

      @if (session('success'))
      <div class="mb-6 flex items-start gap-3 rounded-xl border border-[rgb(var(--color-success)/0.25)] bg-[rgb(var(--color-success)/0.08)] px-4 py-3"
        role="alert">
        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[rgb(var(--color-success)/0.12)] text-[rgb(var(--color-success))]">
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

      <section
        class="mb-7 flex flex-col gap-5 border-b border-[rgb(var(--color-border))] pb-7 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-2xl">
          <div class="mb-3 flex items-center gap-3">
            <span class="h-px w-7 bg-[rgb(var(--color-copper))]"></span>

            <span class="text-xs font-semibold text-[rgb(var(--color-copper))]">
              إدارة الفرق
            </span>
          </div>

          <h3 class="text-2xl font-bold tracking-tight text-[rgb(var(--color-text-primary))] sm:text-3xl">
            فرقك في مكان واحد
          </h3>

          <p class="mt-3 max-w-xl text-sm leading-7 text-[rgb(var(--color-text-secondary))]">
            أنشئ فرقًا منظمة، تابع أعضائها، وواصل العمل داخل إطار واضح ومشترك.
          </p>
        </div>

        <a href="{{ route('teams.create') }}" class="gdfh-btn gdfh-btn-brand w-full sm:w-auto">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            aria-hidden="true">
            <path d="M12 5v14M5 12h14" stroke-linecap="round" />
          </svg>

          <span>إنشاء فريق</span>
        </a>
      </section>

      <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <h3 class="text-base font-bold text-[rgb(var(--color-text-primary))]">
            جميع الفرق
          </h3>

          <span class="inline-flex min-w-7 items-center justify-center rounded-full bg-[rgb(var(--color-copper-soft))] px-2 py-1 text-xs font-bold text-[rgb(var(--color-copper))]">
            {{ $teams->total() }}
          </span>
        </div>

        @if ($teams->total() > 0)
        <p class="text-xs text-[rgb(var(--color-text-secondary))]">
          عرض
          {{ $teams->firstItem() }}
          –
          {{ $teams->lastItem() }}
          من
          {{ $teams->total() }}
          فريق
        </p>
        @endif
      </div>

      @forelse ($teams as $team)
      @if ($loop->first)
      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @endif

        @php
        $typeLabel = $typeLabels[$team->type] ?? ucfirst($team->type);
        $visibilityLabel = $visibilityLabels[$team->visibility] ?? ucfirst($team->visibility);
        @endphp

        <article class="group relative flex min-h-[250px] flex-col overflow-hidden rounded-2xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] transition duration-200 hover:-translate-y-0.5 hover:border-[rgb(var(--color-copper)/0.55)] hover:shadow-[0_14px_36px_rgb(0_0_0/0.06)] dark:hover:shadow-[0_14px_36px_rgb(0_0_0/0.20)]">
          <div class="pointer-events-none absolute -bottom-16 -left-16 h-36 w-36 rounded-full border border-[rgb(var(--color-copper)/0.12)]">
          </div>

          <div class="relative flex flex-1 flex-col p-5">
            <div class="mb-5 flex items-start justify-between gap-4">
              <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))]">
                @if ($team->logo_path)
                <img src="{{ Storage::disk('public')->url($team->logo_path) }}" alt="{{ $team->name }}" class="h-11 w-11 rounded-xl object-cover">
                @else
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[rgb(var(--color-copper-soft))] text-sm font-bold text-[rgb(var(--color-copper))]">
                  {{ mb_strtoupper(mb_substr($team->name, 0, 1)) }}
                </div>
                @endif
              </div>

              <span class="gdfh-badge bg-[rgb(var(--color-mineral-soft))] text-[rgb(var(--color-mineral))]">
                {{ $visibilityLabel }}
              </span>
            </div>

            <div class="flex-1">
              <p class="mb-2 text-[11px] font-semibold text-[rgb(var(--color-copper))]">
                {{ $typeLabel }}
              </p>

              <h4 class="text-lg font-bold leading-7 text-[rgb(var(--color-text-primary))]">
                <a href="{{ route('teams.show', $team) }}" class="transition hover:text-[rgb(var(--color-copper))]">
                  {{ $team->name }}
                </a>
              </h4>

              <p class="mt-3 line-clamp-2 text-sm leading-6 text-[rgb(var(--color-text-secondary))]">
                {{ $team->description ?: 'لا توجد وصف إضافي لهذا الفريق بعد.' }}
              </p>
            </div>

            <div class="mt-6 flex items-center justify-between gap-3 border-t border-[rgb(var(--color-border))] pt-4">
              <div class="flex items-center gap-2 text-xs text-[rgb(var(--color-text-secondary))]">
                @if ($team->visibility === 'private')
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                  aria-hidden="true">
                  <rect x="5" y="10" width="14" height="10" rx="2" />
                  <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                </svg>
                @else
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                  aria-hidden="true">
                  <circle cx="12" cy="12" r="9" />
                  <path d="M3 12h18M12 3c2.2 2.4 3.3 5.4 3.3 9S14.2 18.6 12 21M12 3C9.8 5.4 8.7 8.4 8.7 12S9.8 18.6 12 21" />
                </svg>
                @endif

                <span>{{ $visibilityLabel }}</span>
              </div>

              <div class="flex items-center gap-2">
                <a href="{{ route('teams.edit', $team) }}"
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[rgb(var(--color-border))] text-[rgb(var(--color-text-secondary))] transition hover:border-[rgb(var(--color-copper)/0.5)] hover:bg-[rgb(var(--color-copper-soft))] hover:text-[rgb(var(--color-copper))]"
                  title="تعديل الفريق" aria-label="تعديل {{ $team->name }}">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                    aria-hidden="true">
                    <path d="m14.5 5.5 4 4M4 20l3.5-.8L19 7.7a1.4 1.4 0 0 0 0-2l-.7-.7a1.4 1.4 0 0 0-2 0L4.8 16.5 4 20Z"
                      stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </a>

                <a href="{{ route('teams.show', $team) }}"
                  class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold text-[rgb(var(--color-text-primary))] transition hover:bg-[rgb(var(--color-surface-soft))]">
                  <span>عرض التفاصيل</span>

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
      <section class="relative overflow-hidden rounded-2xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] px-6 py-16 text-center sm:py-20">
        <div class="pointer-events-none absolute left-1/2 top-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rounded-full border border-[rgb(var(--color-copper)/0.08)]">
        </div>

        <div class="pointer-events-none absolute left-1/2 top-1/2 h-44 w-44 -translate-x-1/2 -translate-y-1/2 rounded-full border border-[rgb(var(--color-copper)/0.10)]">
        </div>

        <div class="relative mx-auto max-w-md">
          <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))]">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
              aria-hidden="true">
              <path d="M7 4.5h10a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-11a2 2 0 0 1 2-2Z"
                stroke-linejoin="round" />
              <path d="M8 8h8M8 11.5h5" stroke-linecap="round" />
            </svg>
          </div>

          <h3 class="mt-5 text-lg font-bold text-[rgb(var(--color-text-primary))]">
            ابدأ أول فريق لك
          </h3>

          <p class="mx-auto mt-2 max-w-sm text-sm leading-7 text-[rgb(var(--color-text-secondary))]">
            لا توجد فرق حتى الآن. أنشئ فريقًا جديدًا وابدأ بتنظيم العمل مع أعضاءك من مكان واحد.
          </p>

          <a href="{{ route('teams.create') }}" class="gdfh-btn gdfh-btn-brand mt-6">
            إنشاء فريق
          </a>
        </div>
      </section>
      @endforelse

      @if ($teams->hasPages())
      <div class="mt-8 flex justify-center">
        <div class="gdfh-card overflow-hidden">
          <div class="w-full p-2">
            {{ $teams->links() }}
          </div>
        </div>
      </div>
      @endif

    </div>
  </div>
</x-app-layout>
