<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[rgb(var(--color-text-primary))]">
          سوق المستقلين والخدمات البرمجية (Enterprise Marketplace)
        </h2>
        <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">
          استكشف الخبراء، الخدمات المجهزة، والفرص المشاريعية المتاحة في المنصة.
        </p>
      </div>

      {{-- Tab Navigation Buttons --}}
      <div class="flex items-center gap-1 rounded-xl bg-[rgb(var(--color-surface-soft))] p-1 border border-[rgb(var(--color-border))]">
        <a href="{{ route('marketplace.index', array_merge(request()->query(), ['tab' => 'services'])) }}"
          class="gdfh-btn text-xs py-1.5 px-3 {{ $tab === 'services' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
          🛍️ الخدمات (Services)
        </a>
        <a href="{{ route('marketplace.index', array_merge(request()->query(), ['tab' => 'freelancers'])) }}"
          class="gdfh-btn text-xs py-1.5 px-3 {{ $tab === 'freelancers' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
          👨‍💻 المستقلون (Freelancers)
        </a>
        <a href="{{ route('marketplace.index', array_merge(request()->query(), ['tab' => 'projects'])) }}"
          class="gdfh-btn text-xs py-1.5 px-3 {{ $tab === 'projects' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
          💼 المشاريع المفتوحة (Jobs)
        </a>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-6">
    <div class="mx-auto max-w-7xl space-y-6">

      {{-- Search & Filter Toolbar --}}
      <form method="GET" action="{{ route('marketplace.index') }}" class="gdfh-card p-4 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        <input type="hidden" name="tab" value="{{ $tab }}">

        {{-- Search Input --}}
        <div class="relative flex-1 max-w-md">
          <svg class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[rgb(var(--color-text-secondary))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="ابحث باسم الخدمة، المستقل، أو المهارة..." class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] ps-9 pe-4 py-2 text-xs text-[rgb(var(--color-text-primary))] placeholder:text-[rgb(var(--color-text-secondary))] focus:border-[rgb(var(--color-copper))] focus:outline-none focus:ring-1 focus:ring-[rgb(var(--color-copper))]">
        </div>

        <div class="flex items-center gap-3 text-xs">
          <button type="submit" class="gdfh-btn gdfh-btn-brand text-xs py-2 px-4">
            بحث وتصفية
          </button>
          @if (!empty($filters['search']))
          <a href="{{ route('marketplace.index', ['tab' => $tab]) }}" class="text-xs font-bold text-[rgb(var(--color-copper))] hover:underline">إعادة ضبط</a>
          @endif
        </div>
      </form>

      {{-- Tab 1: Services Directory Grid --}}
      @if ($tab === 'services')
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($services as $service)
        <div class="gdfh-card overflow-hidden flex flex-col justify-between hover:shadow-lg transition border border-[rgb(var(--color-border))]">
          <div class="p-6 space-y-3">
            <div class="flex items-center justify-between gap-2">
              <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-copper))]">
                {{ $service->category }}
              </span>
              <span class="text-xs font-bold text-amber-500 flex items-center gap-1">
                ★ {{ number_format($service->rating, 1) }}
              </span>
            </div>

            <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))] line-clamp-2">
              <a href="{{ route('marketplace.services.show', $service) }}" class="hover:text-[rgb(var(--color-copper))]">
                {{ $service->title }}
              </a>
            </h3>

            <p class="text-xs text-[rgb(var(--color-text-secondary))] line-clamp-2 leading-relaxed">
              {{ $service->description }}
            </p>
          </div>

          <div class="p-4 border-t border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface-soft)/0.4)] flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[rgb(var(--color-copper-soft))] text-[10px] font-bold text-[rgb(var(--color-copper))]">
                {{ mb_substr($service->user?->name ?? 'م', 0, 1) }}
              </span>
              <a href="{{ route('marketplace.freelancers.show', $service->user) }}" class="text-xs font-bold text-[rgb(var(--color-text-primary))] hover:underline">
                {{ $service->user?->name }}
              </a>
            </div>

            <div class="text-end">
              <span class="text-xs text-[rgb(var(--color-text-secondary))]">تبدأ من</span>
              <div class="text-sm font-extrabold text-[rgb(var(--color-copper))]">${{ number_format($service->price, 0) }}</div>
            </div>
          </div>
        </div>
        @empty
        <div class="p-12 text-center text-xs text-[rgb(var(--color-text-secondary))] col-span-full gdfh-card">
          لا توجد خدمات متاحة حالياً وفقاً لمعايير البحث.
        </div>
        @endforelse
      </div>

      @if ($services->hasPages())
      <div class="pt-4">{{ $services->links() }}</div>
      @endif

      {{-- Tab 2: Freelancers Directory Grid --}}
      @elseif ($tab === 'freelancers')
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($freelancers as $freelancer)
        <div class="gdfh-card p-6 flex flex-col justify-between space-y-4 hover:shadow-lg transition">
          <div class="space-y-3">
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[rgb(var(--color-copper-soft))] text-base font-bold text-[rgb(var(--color-copper))]">
                  {{ mb_substr($freelancer->name, 0, 1) }}
                </div>
                <div>
                  <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
                    <a href="{{ route('marketplace.freelancers.show', $freelancer) }}" class="hover:text-[rgb(var(--color-copper))]">
                      {{ $freelancer->name }}
                    </a>
                  </h3>
                  <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">{{ $freelancer->username ? '@'.$freelancer->username : 'مستقل موثق' }}</p>
                </div>
              </div>

              <span class="gdfh-badge text-xs font-bold bg-emerald-500/10 text-emerald-500">
                متاح للعمل
              </span>
            </div>

            <p class="text-xs font-bold text-[rgb(var(--color-copper))]">
              {{ $freelancer->freelancerProfile?->title ?? 'مطور ومهندس برمجيات' }}
            </p>

            <p class="text-xs text-[rgb(var(--color-text-secondary))] line-clamp-2">
              {{ $freelancer->freelancerProfile?->bio ?? 'متخصص في بناء وتطوير الحلول البرمجية عالية الأداء والتطبيقات الذكية.' }}
            </p>
          </div>

          <div class="pt-3 border-t border-[rgb(var(--color-border))] flex items-center justify-between text-xs">
            <div>
              <span class="text-[10px] text-[rgb(var(--color-text-secondary))]">أجر الساعة</span>
              <div class="font-bold text-[rgb(var(--color-text-primary))]">${{ number_format($freelancer->freelancerProfile?->hourly_rate ?? 25, 0) }}/h</div>
            </div>

            <a href="{{ route('marketplace.freelancers.show', $freelancer) }}" class="gdfh-btn gdfh-btn-secondary text-xs py-1 px-3">
              عرض الملف الشخصي
            </a>
          </div>
        </div>
        @empty
        <div class="p-12 text-center text-xs text-[rgb(var(--color-text-secondary))] col-span-full gdfh-card">
          لا يوجد مستقلين مسجلين وفقاً لمعايير البحث.
        </div>
        @endforelse
      </div>

      @if ($freelancers->hasPages())
      <div class="pt-4">{{ $freelancers->links() }}</div>
      @endif

      {{-- Tab 3: Public Jobs Grid --}}
      @else
      <div class="grid grid-cols-1 gap-6">
        @forelse ($projects as $project)
        <div class="gdfh-card p-6 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
          <div class="space-y-2 max-w-2xl">
            <div class="flex items-center gap-2">
              <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-copper))]">
                {{ $project->category ?? 'تطوير البرمجيات' }}
              </span>
              <span class="text-[11px] text-[rgb(var(--color-text-secondary))]">بواسطة {{ $project->owner?->name }}</span>
            </div>

            <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
              <a href="{{ route('projects.show', $project) }}" class="hover:text-[rgb(var(--color-copper))]">
                {{ $project->title }}
              </a>
            </h3>

            <p class="text-xs text-[rgb(var(--color-text-secondary))] line-clamp-2">
              {{ $project->description }}
            </p>
          </div>

          <div class="flex flex-row md:flex-col items-center md:items-end justify-between gap-3 shrink-0">
            <div class="text-start md:text-end">
              <span class="text-[10px] text-[rgb(var(--color-text-secondary))]">الميزانية التقديرية</span>
              <div class="text-sm font-bold text-[rgb(var(--color-copper))]">
                ${{ number_format($project->budget_min ?? 500, 0) }} - ${{ number_format($project->budget_max ?? 2000, 0) }}
              </div>
            </div>

            <a href="{{ route('projects.show', $project) }}" class="gdfh-btn gdfh-btn-brand text-xs py-1.5 px-4">
              عرض تفاصيل المشروع
            </a>
          </div>
        </div>
        @empty
        <div class="p-12 text-center text-xs text-[rgb(var(--color-text-secondary))] gdfh-card">
          لا توجد مشاريع مفتوحة متاحة حالياً.
        </div>
        @endforelse
      </div>

      @if ($projects->hasPages())
      <div class="pt-4">{{ $projects->links() }}</div>
      @endif

      @endif

    </div>
  </div>
</x-app-layout>
