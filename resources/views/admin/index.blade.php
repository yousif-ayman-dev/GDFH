<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <p class="text-xs font-bold text-[rgb(var(--color-copper))]">لوحة الإدارة التنفيذية العليا</p>
        <h2 class="mt-1 text-2xl font-black text-[rgb(var(--color-text-primary))]">مؤشرات الأداء الرئيسية والتحليل الإداري</h2>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('admin.users') }}" class="gdfh-btn gdfh-btn-secondary text-xs px-3 py-2 font-bold">إدارة المستخدمين</a>
        <a href="{{ route('admin.projects') }}" class="gdfh-btn gdfh-btn-secondary text-xs px-3 py-2 font-bold">إدارة المشاريع</a>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-8">
    <div class="mx-auto max-w-7xl space-y-8">

      {{-- Flash Messages --}}
      @if (session('success'))
      <div class="flex items-center gap-3 p-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 text-xs font-semibold">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
      </div>
      @endif

      {{-- Admin Welcome Card --}}
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 rounded-2xl border border-[rgb(var(--color-copper)/0.30)] bg-gradient-to-r from-[rgb(var(--color-copper-soft))] to-transparent shadow-sm">
        <div class="flex items-center gap-4">
          <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[rgb(var(--color-copper))] text-white shadow-md">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          </div>
          <div>
            <h1 class="text-base font-bold text-[rgb(var(--color-text-primary))]">نظام الرقابة والتحليلات الحية — Tasker Admin Engine</h1>
            <p class="text-xs text-[rgb(var(--color-text-secondary))] mt-0.5">بيانات النظام مستخرجة مباشرة ومحسوبة حياً من قاعدة البيانات الحقيقية.</p>
          </div>
        </div>

        <div class="flex items-center gap-3 self-end md:self-center text-xs font-bold text-[rgb(var(--color-text-secondary))] bg-[rgb(var(--color-surface))] px-4 py-2 rounded-xl border border-[rgb(var(--color-border))]">
          <span>معدل نمو الجدد (30 يوم):</span>
          <span class="text-emerald-500 font-extrabold">+{{ $stats['user_growth_30_days'] }} مستخدم</span>
        </div>
      </div>

      {{-- Section 1: User & Platform Core KPIs --}}
      <section class="space-y-4">
        <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))] flex items-center gap-2">
          <svg class="h-4 w-4 text-[rgb(var(--color-copper))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          <span>مؤشرات المستخدمين والمنصة</span>
        </h3>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
          <div class="gdfh-card p-5 space-y-2">
            <span class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">إجمالي المستخدمين</span>
            <div class="text-2xl font-black text-[rgb(var(--color-text-primary))]">{{ number_format($stats['total_users']) }}</div>
            <div class="text-[11px] text-[rgb(var(--color-text-secondary))] flex items-center justify-between">
              <span>نشطون: {{ $stats['active_users'] }}</span>
              <span class="text-emerald-500 font-bold">موثقون: {{ $stats['verified_users'] }}</span>
            </div>
          </div>

          <div class="gdfh-card p-5 space-y-2">
            <span class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">العملاء (Clients)</span>
            <div class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ number_format($stats['total_clients']) }}</div>
            <div class="text-[11px] text-[rgb(var(--color-text-secondary))]">أصحاب مشاريع مسجلون</div>
          </div>

          <div class="gdfh-card p-5 space-y-2">
            <span class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">المستقلون (Freelancers)</span>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($stats['total_freelancers']) }}</div>
            <div class="text-[11px] text-[rgb(var(--color-text-secondary))]">منفذو الخدمات والتخصصات</div>
          </div>

          <div class="gdfh-card p-5 space-y-2">
            <span class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">إجمالي المشاريع</span>
            <div class="text-2xl font-black text-purple-600 dark:text-purple-400">{{ number_format($stats['total_projects']) }}</div>
            <div class="text-[11px] text-[rgb(var(--color-text-secondary))]">نشطة: {{ $stats['active_projects'] }}</div>
          </div>

          <div class="gdfh-card p-5 space-y-2">
            <span class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">نسبة إنجاز المشاريع</span>
            <div class="text-2xl font-black text-[rgb(var(--color-copper))]">{{ $stats['completion_rate'] }}%</div>
            <div class="text-[11px] text-[rgb(var(--color-text-secondary))]">مكتملة: {{ $stats['completed_projects'] }} / {{ $stats['total_projects'] }}</div>
          </div>
        </div>
      </section>

      {{-- Section 2: Financial KPIs & Monetary Overview --}}
      <section class="space-y-4">
        <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))] flex items-center gap-2">
          <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span>التحليل والحياد المالي (Financial Analytics & Revenues)</span>
        </h3>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {{-- Platform Revenue / Commission Card --}}
          <div class="gdfh-card p-6 border-emerald-500/30 bg-emerald-500/5 space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400">إجمالي عمولة المنصة المقدرة</span>
              <span class="gdfh-badge bg-emerald-500/20 text-emerald-600 text-[10px] font-bold">
                {{ round(($stats['commission_rate'] ?? 0.10) * 100, 1) }}% Rate (Configurable)
              </span>
            </div>
            <div class="text-3xl font-black text-emerald-600 dark:text-emerald-300">${{ number_format($stats['platform_commission'], 2) }}</div>
            <p class="text-[11px] text-[rgb(var(--color-text-secondary))] leading-relaxed">
              صافي عمولة المنصة المحسوبة من مبالغ العقود المنجزة والمسلمة للمستقلين بناءً على النسبة القابلة للتعديل.
            </p>
          </div>

          {{-- Total Contract & Escrow Value Card --}}
          <div class="gdfh-card p-6 space-y-3">
            <span class="text-xs font-bold text-[rgb(var(--color-text-primary))]">إجمالي مبالغ العقود المسجلة</span>
            <div class="text-3xl font-black text-[rgb(var(--color-text-primary))]">${{ number_format($stats['total_contract_value'], 2) }}</div>
            <div class="flex items-center justify-between text-xs pt-1 border-t border-[rgb(var(--color-border))]">
              <span class="text-[rgb(var(--color-text-secondary))]">مبالغ الضمان (Escrow):</span>
              <span class="font-extrabold text-amber-500">${{ number_format($stats['escrow_held'], 2) }}</span>
            </div>
          </div>

          {{-- Project Budget & Average Value Card --}}
          <div class="gdfh-card p-6 space-y-3">
            <span class="text-xs font-bold text-[rgb(var(--color-text-primary))]">إجمالي ميزانيات المشاريع</span>
            <div class="text-3xl font-black text-[rgb(var(--color-copper))]">${{ number_format($stats['total_project_value'], 2) }}</div>
            <div class="flex items-center justify-between text-xs pt-1 border-t border-[rgb(var(--color-border))]">
              <span class="text-[rgb(var(--color-text-secondary))]">متوسط ميزانية المشروع:</span>
              <span class="font-extrabold text-[rgb(var(--color-text-primary))]">${{ number_format($stats['avg_project_value'], 2) }}</span>
            </div>
          </div>
        </div>
      </section>

      {{-- Section 3: Visual Growth Trends & Category Analysis --}}
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Category Breakdown --}}
        <section class="gdfh-card p-6 space-y-4">
          <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">أكثر تخصصات المشاريع طلبًا (Top Categories)</h3>
          <div class="space-y-3">
            @forelse ($topCategories as $cat)
            @php
            $percentage = $stats['total_projects'] > 0 ? round(($cat->count / $stats['total_projects']) * 100) : 0;
            @endphp
            <div class="space-y-1 text-xs">
              <div class="flex items-center justify-between">
                <span class="font-bold text-[rgb(var(--color-text-primary))]">{{ $cat->category }}</span>
                <span class="text-[rgb(var(--color-text-secondary))] font-mono">{{ $cat->count }} مشروع ({{ $percentage }}%)</span>
              </div>
              <div class="h-2 w-full overflow-hidden rounded-full bg-[rgb(var(--color-surface-soft))]">
                <div class="h-full bg-[rgb(var(--color-copper))] rounded-full" style="width: {{ max(5, $percentage) }}%;"></div>
              </div>
            </div>
            @empty
            <div class="text-center py-6 text-xs text-[rgb(var(--color-text-secondary))]">لا توجد مشاريع مبرمجة بتصنيفات محددة بعد.</div>
            @endforelse
          </div>
        </section>

        {{-- Monthly Growth Trends --}}
        <section class="gdfh-card p-6 space-y-4">
          <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">نمو تسجيل المستخدمين والمشاريع (آخر 6 أشهر)</h3>
          <div class="grid grid-cols-6 gap-2 items-end h-40 pt-4 border-b border-[rgb(var(--color-border))] pb-2">
            @foreach ($monthlyRegistrations as $item)
            @php
            $maxVal = max(1, $monthlyRegistrations->max('count'));
            $heightPct = round(($item['count'] / $maxVal) * 100);
            @endphp
            <div class="flex flex-col items-center gap-1 h-full justify-end group">
              <span class="text-[10px] font-bold text-[rgb(var(--color-copper))]">{{ $item['count'] }}</span>
              <div class="w-full bg-[rgb(var(--color-copper-soft))] hover:bg-[rgb(var(--color-copper))] transition-all rounded-t" style="height: {{ max(10, $heightPct) }}%;"></div>
              <span class="text-[9px] text-[rgb(var(--color-text-secondary))] truncate w-full text-center">{{ $item['month'] }}</span>
            </div>
            @endforeach
          </div>
        </section>

      </div>

      {{-- Section 4: Recent Activity Tables --}}
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Recent Users Table --}}
        <section class="gdfh-card overflow-hidden">
          <div class="flex items-center justify-between px-5 py-4 border-b border-[rgb(var(--color-border))]">
            <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">أحدث الحسابات المسجلة</h3>
            <a href="{{ route('admin.users') }}" class="text-xs font-semibold text-[rgb(var(--color-copper))] hover:underline">إدارة المستخدمين</a>
          </div>
          <div class="divide-y divide-[rgb(var(--color-border)/0.5)]">
            @forelse ($recentUsers as $u)
            <div class="flex items-center gap-3 px-5 py-3 text-xs">
              <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))] font-bold text-xs">
                {{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="font-bold text-[rgb(var(--color-text-primary))] truncate">{{ $u->name }}</div>
                <div class="text-[10px] text-[rgb(var(--color-text-secondary))] truncate dir-ltr text-right">{{ $u->email }}</div>
              </div>
              <div class="flex items-center gap-2">
                <span class="gdfh-badge text-[10px] {{ $u->isFreelancer() ? 'bg-emerald-500/10 text-emerald-500' : 'bg-blue-500/10 text-blue-500' }}">
                  {{ $u->account_type }}
                </span>
                <span class="text-[10px] text-[rgb(var(--color-text-secondary))]">{{ $u->created_at->diffForHumans() }}</span>
              </div>
            </div>
            @empty
            <div class="p-6 text-center text-xs text-[rgb(var(--color-text-secondary))]">لا يوجد مستخدمون بعد.</div>
            @endforelse
          </div>
        </section>

        {{-- Recent Projects Table --}}
        <section class="gdfh-card overflow-hidden">
          <div class="flex items-center justify-between px-5 py-4 border-b border-[rgb(var(--color-border))]">
            <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">أحدث المشاريع المضافة</h3>
            <a href="{{ route('admin.projects') }}" class="text-xs font-semibold text-[rgb(var(--color-copper))] hover:underline">إدارة المشاريع</a>
          </div>
          <div class="divide-y divide-[rgb(var(--color-border)/0.5)]">
            @forelse ($recentProjects as $p)
            <div class="flex items-center gap-3 px-5 py-3 text-xs">
              <div class="flex-1 min-w-0 space-y-0.5">
                <a href="{{ route('projects.show', $p) }}" class="font-bold text-[rgb(var(--color-text-primary))] hover:text-[rgb(var(--color-copper))] truncate block">
                  {{ $p->title }}
                </a>
                <div class="text-[10px] text-[rgb(var(--color-text-secondary))] flex items-center gap-2">
                  <span>المالك: <strong>{{ $p->owner?->name }}</strong></span>
                  <span>• {{ $p->created_at->diffForHumans() }}</span>
                </div>
              </div>
              <span class="gdfh-badge text-[10px]" style="background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));">
                {{ $p->status }}
              </span>
            </div>
            @empty
            <div class="p-6 text-center text-xs text-[rgb(var(--color-text-secondary))]">لا توجد مشاريع مضافة بعد.</div>
            @endforelse
          </div>
        </section>

      </div>

    </div>
  </div>
</x-app-layout>

