<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[rgb(var(--color-text-primary))]">
          بوابة البحث الشاملة (Unified Search Portal)
        </h2>
        <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">
          ابحث في المشاريع، المهام، الفرق، خدمات السوق، وبروفايلات المستقلين.
        </p>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-6">
    <div class="mx-auto max-w-6xl space-y-6">

      {{-- Search Bar Form --}}
      <form method="GET" action="{{ route('search.index') }}" class="gdfh-card p-4 sm:p-6 space-y-4">
        <div class="relative flex items-center">
          <svg class="pointer-events-none absolute start-4 top-1/2 h-5 w-5 -translate-y-1/2 text-[rgb(var(--color-copper))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8" />
            <line x1="21" y1="21" x2="16.65" y2="16.65" />
          </svg>

          <input type="text" name="q" value="{{ $query }}" placeholder="اكتب كلمة البحث (اسم مشروع، مهمة، فريق، خدمة، أو مستقل)..." autofocus class="w-full rounded-2xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] ps-12 pe-28 py-3.5 text-sm text-[rgb(var(--color-text-primary))] placeholder:text-[rgb(var(--color-text-secondary))] focus:border-[rgb(var(--color-copper))] focus:outline-none focus:ring-1 focus:ring-[rgb(var(--color-copper))] shadow-sm">

          <button type="submit" class="absolute end-2 gdfh-btn gdfh-btn-brand text-xs py-2 px-5 font-bold shadow-sm">
            بحث شامل
          </button>
        </div>

        {{-- Entity Filter Tabs --}}
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 border-t border-[rgb(var(--color-border))] pt-4 text-xs">
          <a href="{{ route('search.index', ['q' => $query, 'type' => 'all']) }}"
            class="gdfh-btn text-xs py-1.5 px-3 flex items-center gap-1.5 rounded-xl whitespace-nowrap {{ $type === 'all' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
            <span>🌐 الكل</span>
            <span class="ms-1 px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-white/20">{{ $counts['total'] }}</span>
          </a>

          <a href="{{ route('search.index', ['q' => $query, 'type' => 'projects']) }}"
            class="gdfh-btn text-xs py-1.5 px-3 flex items-center gap-1.5 rounded-xl whitespace-nowrap {{ $type === 'projects' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
            <span>📁 المشاريع</span>
            <span class="ms-1 px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-white/20">{{ $counts['projects'] }}</span>
          </a>

          <a href="{{ route('search.index', ['q' => $query, 'type' => 'tasks']) }}"
            class="gdfh-btn text-xs py-1.5 px-3 flex items-center gap-1.5 rounded-xl whitespace-nowrap {{ $type === 'tasks' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
            <span>📑 المهام</span>
            <span class="ms-1 px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-white/20">{{ $counts['tasks'] }}</span>
          </a>

          <a href="{{ route('search.index', ['q' => $query, 'type' => 'teams']) }}"
            class="gdfh-btn text-xs py-1.5 px-3 flex items-center gap-1.5 rounded-xl whitespace-nowrap {{ $type === 'teams' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
            <span>👥 الفرق</span>
            <span class="ms-1 px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-white/20">{{ $counts['teams'] }}</span>
          </a>

          <a href="{{ route('search.index', ['q' => $query, 'type' => 'services']) }}"
            class="gdfh-btn text-xs py-1.5 px-3 flex items-center gap-1.5 rounded-xl whitespace-nowrap {{ $type === 'services' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
            <span>🛒 الخدمات</span>
            <span class="ms-1 px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-white/20">{{ $counts['services'] }}</span>
          </a>

          <a href="{{ route('search.index', ['q' => $query, 'type' => 'freelancers']) }}"
            class="gdfh-btn text-xs py-1.5 px-3 flex items-center gap-1.5 rounded-xl whitespace-nowrap {{ $type === 'freelancers' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
            <span>🧑‍💻 المستقلون</span>
            <span class="ms-1 px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-white/20">{{ $counts['freelancers'] }}</span>
          </a>
        </div>
      </form>

      {{-- Search Results Body --}}
      @if (empty($query))
      <div class="gdfh-card p-12 text-center space-y-4">
        <div class="flex h-16 w-16 mx-auto items-center justify-center rounded-2xl bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))]">
          <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8" />
            <line x1="21" y1="21" x2="16.65" y2="16.65" />
          </svg>
        </div>
        <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">ابدأ البحث في المنصة</h3>
        <p class="text-xs text-[rgb(var(--color-text-secondary))] max-w-md mx-auto leading-relaxed">
          أدخل مصطلح البحث في الحقل أعلاه لاستكشاف المشاريع، المهام، أعضاء الفرق، خدمات السوق البرمجية، وخبرات المستقلين.
        </p>
      </div>
      @elseif ($counts['total'] === 0)
      <div class="gdfh-card p-12 text-center space-y-3">
        <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">لا توجد نتائج مطابقة لـ "{{ $query }}"</h3>
        <p class="text-xs text-[rgb(var(--color-text-secondary))]">
          جرّب البحث باستخدام كلمات مفتاحية أخرى أو اختر تصنيفاً مختلفاً من التبويبات أعلاه.
        </p>
      </div>
      @else
      <div class="space-y-6">

        {{-- 1. Projects Results --}}
        @if (($type === 'all' || $type === 'projects') && count($results['projects']) > 0)
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))] uppercase tracking-wider flex items-center gap-2">
              <span>📁 المشاريع</span>
              <span class="px-2 py-0.5 rounded-full text-[10px] bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-copper))] font-bold">{{ count($results['projects']) }}</span>
            </h3>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($results['projects'] as $project)
            <div class="gdfh-card p-4 space-y-2 border border-[rgb(var(--color-border))] hover:shadow-md transition">
              <div class="flex items-center justify-between gap-2">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-copper))] uppercase">
                  {{ $project->code ?? 'PROJ' }}
                </span>
                <span class="text-[10px] font-bold text-[rgb(var(--color-text-secondary))]">
                  {{ $project->status }}
                </span>
              </div>
              <h4 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">
                <a href="{{ route('projects.show', $project) }}" class="hover:text-[rgb(var(--color-copper))]">
                  {{ $project->title }}
                </a>
              </h4>
              <p class="text-[11px] text-[rgb(var(--color-text-secondary))] line-clamp-2">
                {{ $project->description }}
              </p>
              <div class="pt-2 border-t border-[rgb(var(--color-border))] flex items-center justify-between text-[10px] text-[rgb(var(--color-text-secondary))]">
                <span>المالك: {{ $project->owner?->name }}</span>
                <a href="{{ route('projects.show', $project) }}" class="font-bold text-[rgb(var(--color-copper))] hover:underline">عرض المشروع ←</a>
              </div>
            </div>
            @endforeach
          </div>
        </div>
        @endif

        {{-- 2. Tasks Results --}}
        @if (($type === 'all' || $type === 'tasks') && count($results['tasks']) > 0)
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))] uppercase tracking-wider flex items-center gap-2">
              <span>📑 المهام</span>
              <span class="px-2 py-0.5 rounded-full text-[10px] bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-copper))] font-bold">{{ count($results['tasks']) }}</span>
            </h3>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($results['tasks'] as $task)
            <div class="gdfh-card p-4 space-y-2 border border-[rgb(var(--color-border))] hover:shadow-md transition">
              <div class="flex items-center justify-between gap-2">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-500/10 text-blue-500">
                  {{ $task->status }}
                </span>
                <span class="text-[10px] font-bold text-amber-500">
                  {{ $task->priority }}
                </span>
              </div>
              <h4 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">
                <a href="{{ route('projects.show', $task->project) }}" class="hover:text-[rgb(var(--color-copper))]">
                  {{ $task->title }}
                </a>
              </h4>
              <p class="text-[11px] text-[rgb(var(--color-text-secondary))] line-clamp-2">
                {{ $task->description }}
              </p>
              <div class="pt-2 border-t border-[rgb(var(--color-border))] flex items-center justify-between text-[10px] text-[rgb(var(--color-text-secondary))]">
                <span>المشروع: {{ $task->project?->title }}</span>
                <a href="{{ route('projects.show', $task->project) }}" class="font-bold text-[rgb(var(--color-copper))] hover:underline">عرض المهمة ←</a>
              </div>
            </div>
            @endforeach
          </div>
        </div>
        @endif

        {{-- 3. Teams Results --}}
        @if (($type === 'all' || $type === 'teams') && count($results['teams']) > 0)
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))] uppercase tracking-wider flex items-center gap-2">
              <span>👥 الفرق</span>
              <span class="px-2 py-0.5 rounded-full text-[10px] bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-copper))] font-bold">{{ count($results['teams']) }}</span>
            </h3>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($results['teams'] as $team)
            <div class="gdfh-card p-4 space-y-2 border border-[rgb(var(--color-border))] hover:shadow-md transition">
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-copper))]">
                  {{ $team->members_count }} أعضاء
                </span>
              </div>
              <h4 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">
                <a href="{{ route('teams.show', $team) }}" class="hover:text-[rgb(var(--color-copper))]">
                  {{ $team->name }}
                </a>
              </h4>
              <p class="text-[11px] text-[rgb(var(--color-text-secondary))] line-clamp-2">
                {{ $team->description }}
              </p>
              <div class="pt-2 border-t border-[rgb(var(--color-border))] flex items-center justify-between text-[10px] text-[rgb(var(--color-text-secondary))]">
                <span>القائد: {{ $team->owner?->name }}</span>
                <a href="{{ route('teams.show', $team) }}" class="font-bold text-[rgb(var(--color-copper))] hover:underline">صفحة الفريق ←</a>
              </div>
            </div>
            @endforeach
          </div>
        </div>
        @endif

        {{-- 4. Marketplace Services Results --}}
        @if (($type === 'all' || $type === 'services') && count($results['services']) > 0)
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))] uppercase tracking-wider flex items-center gap-2">
              <span>🛒 خدمات السوق</span>
              <span class="px-2 py-0.5 rounded-full text-[10px] bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-copper))] font-bold">{{ count($results['services']) }}</span>
            </h3>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($results['services'] as $service)
            <div class="gdfh-card p-4 space-y-2 border border-[rgb(var(--color-border))] hover:shadow-md transition">
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-copper))]">
                  {{ $service->category }}
                </span>
                <span class="text-xs font-extrabold text-[rgb(var(--color-copper))]">${{ number_format($service->price, 0) }}</span>
              </div>
              <h4 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">
                <a href="{{ route('marketplace.services.show', $service) }}" class="hover:text-[rgb(var(--color-copper))]">
                  {{ $service->title }}
                </a>
              </h4>
              <p class="text-[11px] text-[rgb(var(--color-text-secondary))] line-clamp-2">
                {{ $service->description }}
              </p>
              <div class="pt-2 border-t border-[rgb(var(--color-border))] flex items-center justify-between text-[10px] text-[rgb(var(--color-text-secondary))]">
                <span>المستقل: {{ $service->user?->name }}</span>
                <a href="{{ route('marketplace.services.show', $service) }}" class="font-bold text-[rgb(var(--color-copper))] hover:underline">عرض الخدمة ←</a>
              </div>
            </div>
            @endforeach
          </div>
        </div>
        @endif

        {{-- 5. Freelancers Results --}}
        @if (($type === 'all' || $type === 'freelancers') && count($results['freelancers']) > 0)
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))] uppercase tracking-wider flex items-center gap-2">
              <span>🧑‍💻 المستقلون والخبراء</span>
              <span class="px-2 py-0.5 rounded-full text-[10px] bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-copper))] font-bold">{{ count($results['freelancers']) }}</span>
            </h3>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($results['freelancers'] as $freelancer)
            <div class="gdfh-card p-4 space-y-2 border border-[rgb(var(--color-border))] hover:shadow-md transition">
              <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                  @if ($freelancer->avatar_url)
                  <img src="{{ $freelancer->avatar_url }}" alt="{{ $freelancer->name }}" class="h-8 w-8 rounded-full object-cover border border-slate-600">
                  @else
                  <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[rgb(var(--color-copper-soft))] text-xs font-bold text-[rgb(var(--color-copper))]">
                    {{ mb_substr($freelancer->name, 0, 1) }}
                  </div>
                  @endif
                  <div>
                    <h4 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">{{ $freelancer->name }}</h4>
                    <p class="text-[10px] text-[rgb(var(--color-text-secondary))]">{{ $freelancer->freelancerProfile?->title ?? 'مستقل برمجي' }}</p>
                  </div>
                </div>
                <span class="text-xs font-bold text-[rgb(var(--color-text-primary))]">${{ number_format($freelancer->freelancerProfile?->hourly_rate ?? 25, 0) }}/h</span>
              </div>
              <p class="text-[11px] text-[rgb(var(--color-text-secondary))] line-clamp-2">
                {{ $freelancer->freelancerProfile?->bio }}
              </p>
              <div class="pt-2 border-t border-[rgb(var(--color-border))] flex items-center justify-end text-[10px]">
                <a href="{{ route('marketplace.freelancers.show', $freelancer) }}" class="font-bold text-[rgb(var(--color-copper))] hover:underline">عرض البروفايل ←</a>
              </div>
            </div>
            @endforeach
          </div>
        </div>
        @endif

      </div>
      @endif

    </div>
  </div>
</x-app-layout>
