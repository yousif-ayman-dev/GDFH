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
          class="gdfh-btn text-xs py-1.5 px-3 flex items-center gap-1.5 {{ $tab === 'services' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
          <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119.993z"/></svg>
          <span>الخدمات (Services)</span>
        </a>
        <a href="{{ route('marketplace.index', array_merge(request()->query(), ['tab' => 'freelancers'])) }}"
          class="gdfh-btn text-xs py-1.5 px-3 flex items-center gap-1.5 {{ $tab === 'freelancers' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
          <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <span>المستقلون (Freelancers)</span>
        </a>
        <a href="{{ route('marketplace.index', array_merge(request()->query(), ['tab' => 'projects'])) }}"
          class="gdfh-btn text-xs py-1.5 px-3 flex items-center gap-1.5 {{ $tab === 'projects' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
          <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
          <span>المشاريع المفتوحة (Jobs)</span>
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
                <svg class="h-3.5 w-3.5 text-amber-500 fill-amber-500 inline" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> {{ number_format($service->rating, 1) }}
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
