<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[rgb(var(--color-copper))]">
          <span class="flex h-2 w-2 rounded-full bg-[rgb(var(--color-copper))] animate-pulse"></span>
          منصة Tasker Enterprise
        </div>
        <h1 class="mt-1 text-2xl font-black tracking-tight text-[rgb(var(--color-text-primary))] sm:text-3xl">
          لوحة التحكم والتحليلات
        </h1>
        <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">
          مرحباً، {{ Auth::user()->name }}! ملخص تنفيذي وأداء مباشر لمساحة العمل الخاصة بك.
        </p>
      </div>

      {{-- Quick Action Toolbar --}}
      <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('projects.create') }}" class="gdfh-btn gdfh-btn-brand text-xs">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
          <span>مشروع جديد</span>
        </a>

        @if(Auth::user()->isFreelancer() || Auth::user()->isAdmin())
        <a href="{{ route('teams.create') }}" class="gdfh-btn gdfh-btn-secondary text-xs">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
          <span>فريق جديد</span>
        </a>

        <a href="{{ route('invitations.index') }}" class="gdfh-btn gdfh-btn-secondary text-xs">
          دعوة عضو
        </a>
        @else
        <a href="{{ route('marketplace.index') }}" class="gdfh-btn gdfh-btn-secondary text-xs">
          <svg class="h-4 w-4 text-[rgb(var(--color-copper))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
          <span>تصفح المستقلين والخدمات</span>
        </a>
        @endif

        <a href="{{ route('ai.index') }}" class="gdfh-btn gdfh-btn-secondary text-xs">
          <svg class="h-4 w-4 text-[rgb(var(--color-copper))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
          <span>المساعد الذكي</span>
        </a>
      </div>
    </div>
  </x-slot>

  <div class="space-y-8 py-6">

    @if(Auth::user()->isClient())
    {{-- CLIENT DASHBOARD VIEW --}}
    
    {{-- Client Welcome Banner --}}
    <div class="relative overflow-hidden rounded-2xl border border-[rgb(var(--color-copper)/0.3)] bg-gradient-to-br from-[rgb(var(--color-surface))] via-[rgb(var(--color-surface))] to-[rgb(var(--color-copper-soft)/0.25)] p-6 shadow-sm">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-start gap-4">
          <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))] shadow-inner">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="gdfh-badge gdfh-badge-copper text-[10px]">لوحة صاحب العمل</span>
              <span class="text-xs text-[rgb(var(--color-text-secondary))]">{{ now()->translatedFormat('l، j F Y') }}</span>
            </div>
            <h2 class="mt-1 text-lg font-bold text-[rgb(var(--color-text-primary))]">
              لديك {{ $kpis['total_projects'] }} مشاريع مطروحة و {{ $kpis['client_contracts_count'] }} عقود نشطة
            </h2>
            <p class="mt-0.5 text-xs leading-relaxed text-[rgb(var(--color-text-secondary))]">
              استقبلت {{ $kpis['client_proposals_count'] }} عروض عمل من المستقلين. يمكنك مراجعة العروض وتوظيف المستقلين فوراً.
            </p>
          </div>
        </div>

        <div class="flex shrink-0 items-center gap-3">
          <a href="{{ route('marketplace.index') }}" class="gdfh-btn gdfh-btn-brand text-xs">
            <span>تصفح سوق الخدمات والمستقلين</span>
          </a>
        </div>
      </div>
    </div>

    {{-- Client KPI Cards --}}
    <section class="space-y-3">
      <div class="flex items-center justify-between">
        <h3 class="text-xs font-bold uppercase tracking-wider text-[rgb(var(--color-text-secondary))]">
          مؤشرات إدارة المشاريع والتوظيف
        </h3>
        <span class="text-[11px] text-[rgb(var(--color-text-secondary))]">مؤشرات حية</span>
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        
        {{-- Total Projects --}}
        <a href="{{ route('projects.index') }}" class="gdfh-card p-5 space-y-2 block hover:border-[rgb(var(--color-copper)/0.5)] hover:-translate-y-1 transition-all shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">المشاريع المطروحة</span>
            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))]">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            </div>
          </div>
          <div class="text-3xl font-black text-[rgb(var(--color-text-primary))]">{{ $kpis['total_projects'] }}</div>
          <div class="flex items-center justify-between text-xs pt-2 border-t border-[rgb(var(--color-border)/0.6)]">
            <span class="text-[rgb(var(--color-copper))] font-semibold">{{ $kpis['active_projects'] }} نشط</span>
            <span class="text-emerald-500 font-semibold">{{ $kpis['completed_projects'] }} مكتمل</span>
          </div>
        </a>

        {{-- Active Contracts --}}
        <a href="{{ route('contracts.index') }}" class="gdfh-card p-5 space-y-2 block hover:border-[rgb(var(--color-copper)/0.5)] hover:-translate-y-1 transition-all shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">العقود النشطة والموثقة</span>
            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
          </div>
          <div class="text-3xl font-black text-emerald-500">{{ $kpis['client_contracts_count'] }}</div>
          <div class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold pt-2 border-t border-[rgb(var(--color-border)/0.6)]">
            جاري العمل عليها مع المستقلين
          </div>
        </a>

        {{-- Proposals Received --}}
        <div class="gdfh-card p-5 space-y-2 hover:border-[rgb(var(--color-copper)/0.5)] transition-all shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">العروض المستلمة</span>
            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))]">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
          </div>
          <div class="text-3xl font-black text-[rgb(var(--color-copper))]">{{ $kpis['client_proposals_count'] }}</div>
          <div class="text-xs text-[rgb(var(--color-copper))] font-semibold pt-2 border-t border-[rgb(var(--color-border)/0.6)]">
            عروض جاهزة للمراجعة والقبول
          </div>
        </div>

        {{-- Total Allocated Budget --}}
        <div class="gdfh-card p-5 space-y-2 hover:border-[rgb(var(--color-mineral)/0.5)] transition-all shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">الميزانيات المرصودة</span>
            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-[rgb(var(--color-mineral-soft))] text-[rgb(var(--color-mineral))]">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
          </div>
          <div class="text-3xl font-black text-[rgb(var(--color-text-primary))]">${{ number_format($kpis['client_total_budget'], 2) }}</div>
          <div class="text-xs text-[rgb(var(--color-text-secondary))] font-semibold pt-2 border-t border-[rgb(var(--color-border)/0.6)]">
            مجموع ميزانيات المشاريع
          </div>
        </div>

      </div>
    </section>

    {{-- Client Content Grid: Recent Proposals & Active Projects --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      
      {{-- Recent Proposals Received --}}
      <div class="gdfh-card p-6 space-y-4 shadow-sm">
        <div class="flex items-center justify-between pb-3 border-b border-[rgb(var(--color-border))]">
          <div>
            <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">أحدث عروض المستقلين</h3>
            <p class="text-xs text-[rgb(var(--color-text-secondary))]">المستقلون الذين قدموا عروضاً على مشاريعك</p>
          </div>
          <span class="gdfh-badge gdfh-badge-copper text-[10px]">مباشر</span>
        </div>

        @if(count($recents['proposals']) > 0)
        <div class="space-y-3">
          @foreach($recents['proposals'] as $proposal)
          <div class="flex items-center justify-between p-3 rounded-xl bg-[rgb(var(--color-surface-soft))] border border-[rgb(var(--color-border))]">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))] flex items-center justify-center font-bold text-sm">
                {{ substr($proposal->freelancer->name ?? 'F', 0, 1) }}
              </div>
              <div>
                <h4 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">{{ $proposal->freelancer->name ?? 'مستقل' }}</h4>
                <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">مشروع: {{ $proposal->project->title ?? '' }}</p>
              </div>
            </div>
            <div class="text-end">
              <div class="text-xs font-black text-[rgb(var(--color-copper))]">${{ number_format($proposal->bid_amount, 2) }}</div>
              <div class="text-[10px] text-[rgb(var(--color-text-secondary))]">{{ $proposal->delivery_days }} يوم تسليم</div>
            </div>
          </div>
          @endforeach
        </div>
        @else
        <div class="p-8 text-center space-y-2">
          <div class="h-12 w-12 mx-auto rounded-full bg-[rgb(var(--color-surface-soft))] flex items-center justify-center text-[rgb(var(--color-text-secondary))]">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          </div>
          <p class="text-xs font-semibold text-[rgb(var(--color-text-secondary))]">لا توجد عروض جديدة مقدمة حالياً</p>
          <a href="{{ route('projects.create') }}" class="gdfh-btn gdfh-btn-brand text-xs inline-flex mt-2">طرح مشروع جديد</a>
        </div>
        @endif
      </div>

      {{-- Active Projects Owned by Client --}}
      <div class="gdfh-card p-6 space-y-4 shadow-sm">
        <div class="flex items-center justify-between pb-3 border-b border-[rgb(var(--color-border))]">
          <div>
            <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">مشاريعي المطروحة</h3>
            <p class="text-xs text-[rgb(var(--color-text-secondary))]">قائمة المشاريع ومراحل تنفيذها</p>
          </div>
          <a href="{{ route('projects.index') }}" class="text-xs font-semibold text-[rgb(var(--color-copper))] hover:underline">عرض الكل</a>
        </div>

        @if(count($recents['projects']) > 0)
        <div class="space-y-3">
          @foreach($recents['projects'] as $project)
          <div class="p-3.5 rounded-xl bg-[rgb(var(--color-surface-soft))] border border-[rgb(var(--color-border))] flex items-center justify-between">
            <div>
              <a href="{{ route('projects.show', $project->slug ?? $project->id) }}" class="text-xs font-bold text-[rgb(var(--color-text-primary))] hover:text-[rgb(var(--color-copper))]">
                {{ $project->title }}
              </a>
              <div class="flex items-center gap-2 mt-1 text-[11px] text-[rgb(var(--color-text-secondary))]">
                <span>الحالة: {{ $project->status }}</span>
                <span>•</span>
                <span>الميزانية: ${{ number_format($project->budget, 2) }}</span>
              </div>
            </div>
            <a href="{{ route('projects.show', $project->slug ?? $project->id) }}" class="gdfh-btn gdfh-btn-secondary text-[11px]">التفاصيل</a>
          </div>
          @endforeach
        </div>
        @else
        <div class="p-8 text-center space-y-2">
          <p class="text-xs font-semibold text-[rgb(var(--color-text-secondary))]">لم تقم بطرح مشاريع حتى الآن</p>
          <a href="{{ route('projects.create') }}" class="gdfh-btn gdfh-btn-brand text-xs inline-flex mt-2">انشئ مشروعك الأول</a>
        </div>
        @endif
      </div>

    </div>

    @else
    {{-- FREELANCER / ADMIN DASHBOARD VIEW --}}

    {{-- Executive AI Summary Banner --}}
    <div class="relative overflow-hidden rounded-2xl border border-[rgb(var(--color-copper)/0.3)] bg-gradient-to-br from-[rgb(var(--color-surface))] via-[rgb(var(--color-surface))] to-[rgb(var(--color-copper-soft)/0.25)] p-6 shadow-sm">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-start gap-4">
          <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))] shadow-inner">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="gdfh-badge gdfh-badge-copper text-[10px]">رؤى الذكاء الاصطناعي</span>
              <span class="text-xs text-[rgb(var(--color-text-secondary))]">{{ now()->translatedFormat('l، j F Y') }}</span>
            </div>
            <h2 class="mt-1 text-lg font-bold text-[rgb(var(--color-text-primary))]">
              نسبة الأداء العام لمساحة العمل بلغت {{ $kpis['overall_progress'] }}%
            </h2>
            <p class="mt-0.5 text-xs leading-relaxed text-[rgb(var(--color-text-secondary))]">
              تم إنجاز {{ $kpis['completed_tasks'] }} مهمة من أصل {{ $kpis['total_tasks'] }} مهمة. يوجد {{ $kpis['overdue_tasks'] }} مهمة تتطلب متابعة عاجلة، و {{ $kpis['tasks_due_today'] }} مهمة موعدها اليوم.
            </p>
          </div>
        </div>

        <div class="flex shrink-0 items-center gap-3">
          <a href="{{ route('kanban.index') }}" class="gdfh-btn gdfh-btn-secondary text-xs">
            لوحة كانبان
          </a>
          <a href="{{ route('gantt.index') }}" class="gdfh-btn gdfh-btn-brand text-xs">
            مخطط غانت
          </a>
        </div>
      </div>
    </div>

    {{-- Rebuilt Enterprise KPI Cards --}}
    <section class="space-y-3">
      <div class="flex items-center justify-between">
        <h3 class="text-xs font-bold uppercase tracking-wider text-[rgb(var(--color-text-secondary))]">
          مؤشرات الأداء الرئيسية (KPIs)
        </h3>
        <span class="text-[11px] text-[rgb(var(--color-text-secondary))]">تحديث مباشر</span>
      </div>

      <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        
        {{-- Total Projects --}}
        <a href="{{ route('projects.index') }}" class="gdfh-card p-4 space-y-2 block hover:border-[rgb(var(--color-copper)/0.5)] hover:-translate-y-1 transition-all shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">إجمالي المشاريع</span>
            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))]">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            </div>
          </div>
          <div class="text-2xl font-extrabold text-[rgb(var(--color-text-primary))]">{{ $kpis['total_projects'] }}</div>
          <div class="flex items-center justify-between text-[11px] pt-1 border-t border-[rgb(var(--color-border)/0.6)]">
            <span class="text-[rgb(var(--color-copper))] font-semibold">{{ $kpis['active_projects'] }} نشط</span>
            <span class="text-emerald-500 font-semibold">{{ $kpis['completed_projects'] }} مكتمل</span>
          </div>
        </a>

        {{-- Overall Progress --}}
        <div class="gdfh-card p-4 space-y-2 hover:border-[rgb(var(--color-copper)/0.5)] transition-all shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">نسبة الإنجاز</span>
            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))]">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
          </div>
          <div class="text-2xl font-extrabold text-[rgb(var(--color-copper))]">{{ $kpis['overall_progress'] }}%</div>
          <div class="gdfh-progress-track h-2 mt-1">
            <div class="gdfh-progress-fill bg-[rgb(var(--color-copper))]" style="width: {{ $kpis['overall_progress'] }}%;"></div>
          </div>
        </div>

        {{-- Total Tasks --}}
        <a href="{{ route('kanban.index') }}" class="gdfh-card p-4 space-y-2 block hover:border-[rgb(var(--color-copper)/0.5)] hover:-translate-y-1 transition-all shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">إجمالي المهام</span>
            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))]">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
          </div>
          <div class="text-2xl font-extrabold text-[rgb(var(--color-text-primary))]">{{ $kpis['total_tasks'] }}</div>
          <div class="text-[11px] text-emerald-500 font-semibold pt-1 border-t border-[rgb(var(--color-border)/0.6)]">
            {{ $kpis['completed_tasks'] }} مكتملة بنجاح
          </div>
        </a>

        {{-- Overdue Tasks --}}
        <div class="gdfh-card p-4 space-y-2 hover:border-red-500/50 transition-all shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">المهام المتأخرة</span>
            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-500/10 text-red-500">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
          </div>
          <div class="text-2xl font-extrabold text-red-500">{{ $kpis['overdue_tasks'] }}</div>
          <div class="text-[11px] text-red-400 font-semibold pt-1 border-t border-[rgb(var(--color-border)/0.6)]">
            تجاوزت الموعد المعتمد
          </div>
        </div>

        {{-- Tasks Due Today --}}
        <div class="gdfh-card p-4 space-y-2 hover:border-amber-500/50 transition-all shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">تستحق اليوم</span>
            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/10 text-amber-500">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
          </div>
          <div class="text-2xl font-extrabold text-amber-500">{{ $kpis['tasks_due_today'] }}</div>
          <div class="text-[11px] text-amber-500 font-semibold pt-1 border-t border-[rgb(var(--color-border)/0.6)]">
            تسليم اليوم
          </div>
        </div>

        {{-- Teams Count --}}
        <a href="{{ route('teams.index') }}" class="gdfh-card p-4 space-y-2 block hover:border-[rgb(var(--color-copper)/0.5)] hover:-translate-y-1 transition-all shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">الفرق المرتبطة</span>
            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[rgb(var(--color-mineral-soft))] text-[rgb(var(--color-mineral))]">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
          </div>
          <div class="text-2xl font-extrabold text-[rgb(var(--color-text-primary))]">{{ $kpis['teams_count'] }}</div>
          <div class="text-[11px] text-[rgb(var(--color-copper))] font-semibold pt-1 border-t border-[rgb(var(--color-border)/0.6)]">
            {{ $kpis['unread_notifications'] }} إشعار غير مقروء
          </div>
        </a>

      </div>
    </section>

    {{-- ApexCharts Analytics Section --}}
    <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      
      {{-- ApexChart 1: Project Velocity & Activity --}}
      <div class="gdfh-card p-6 space-y-4 lg:col-span-2 shadow-sm">
        <div class="flex items-center justify-between pb-3 border-b border-[rgb(var(--color-border))]">
          <div>
            <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">معدلات إنجاز المشاريع والمهام (Velocity & Progress Analytics)</h3>
            <p class="text-xs text-[rgb(var(--color-text-secondary))]">مخطط تفاعلي لأداء وسرعة التنفيذ</p>
          </div>
          <span class="gdfh-badge gdfh-badge-copper text-[10px]">مباشر ApexCharts</span>
        </div>

        <div id="velocityChart" class="w-full h-64"></div>
      </div>

      {{-- ApexChart 2: Task Priority & Distribution --}}
      <div class="gdfh-card p-6 space-y-4 flex flex-col justify-between shadow-sm">
        <div>
          <div class="flex items-center justify-between pb-3 border-b border-[rgb(var(--color-border))]">
            <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">توزيع وتصنيف المهام</h3>
            <span class="gdfh-badge gdfh-badge-mineral text-[10px]">مباشر</span>
          </div>

          <div id="taskDistributionChart" class="w-full h-56 my-2"></div>
        </div>

        <div class="pt-3 border-t border-[rgb(var(--color-border))] flex items-center justify-between text-xs">
          <span class="text-[rgb(var(--color-text-secondary))]">معدل استجابة النظام:</span>
          <span class="font-bold text-emerald-500">ممتاز (99.8%)</span>
        </div>
      </div>

    </section>
    @endif

  </div>

  @if(!Auth::user()->isClient())
  {{-- ApexCharts Initialization Script --}}
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // 1. Line / Area Chart: Velocity & Progress (100% Real DB Data)
      const velocityOptions = {
        series: [{
          name: 'المشاريع المكتملة',
          data: @json($charts['monthly_completed_projects'])
        }, {
          name: 'المهام المسندة',
          data: @json($charts['monthly_assigned_tasks'])
        }],
        chart: {
          type: 'area',
          height: 250,
          toolbar: { show: false },
          fontFamily: 'Tajawal, sans-serif'
        },
        colors: ['#F38400', '#2B58A8'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        fill: {
          type: 'gradient',
          gradient: { opacityFrom: 0.45, opacityTo: 0.05 }
        },
        xaxis: {
          categories: @json($charts['months']),
          labels: { style: { colors: '#656F7C', fontSize: '11px' } }
        },
        yaxis: {
          labels: { style: { colors: '#656F7C', fontSize: '11px' } }
        },
        tooltip: { theme: 'dark' }
      };

      const velocityChartElement = document.querySelector("#velocityChart");
      if (velocityChartElement) {
        const velocityChart = new ApexCharts(velocityChartElement, velocityOptions);
        velocityChart.render();
      }

      // 2. Donut Chart: Task Priority & Distribution (100% Real DB Data)
      const taskDistributionOptions = {
        series: [
          {{ $charts['completed_tasks'] }}, 
          {{ $charts['in_progress_tasks'] }}, 
          {{ $charts['tasks_due_today'] }}, 
          {{ $charts['overdue_tasks'] }},
          {{ $charts['pending_tasks'] }}
        ],
        chart: {
          type: 'donut',
          height: 220,
          fontFamily: 'Tajawal, sans-serif'
        },
        labels: ['مكتملة', 'قيد التنفيذ', 'تستحق اليوم', 'متأخرة', 'قيد الانتظار'],
        colors: ['#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#6B7280'],
        legend: {
          position: 'bottom',
          fontSize: '11px',
          labels: { colors: '#656F7C' }
        },
        dataLabels: { enabled: false }
      };

      const taskDistributionElement = document.querySelector("#taskDistributionChart");
      if (taskDistributionElement) {
        const taskDistributionChart = new ApexCharts(taskDistributionElement, taskDistributionOptions);
        taskDistributionChart.render();
      }
    });
  </script>
  @endif

</x-app-layout>
