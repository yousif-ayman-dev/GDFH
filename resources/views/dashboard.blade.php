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

        <a href="{{ route('teams.create') }}" class="gdfh-btn gdfh-btn-secondary text-xs">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
          <span>فريق جديد</span>
        </a>

        <a href="{{ route('invitations.index') }}" class="gdfh-btn gdfh-btn-secondary text-xs">
          دعوة عضو
        </a>

        <a href="{{ route('ai.index') }}" class="gdfh-btn gdfh-btn-secondary text-xs">
          <svg class="h-4 w-4 text-[rgb(var(--color-copper))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
          <span>المساعد الذكي</span>
        </a>
      </div>
    </div>
  </x-slot>

  <div class="space-y-8 py-6">

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

        {{-- Teams Summary Card --}}
        <div class="pt-3 border-t border-[rgb(var(--color-border))] space-y-3">
          <div class="flex items-center justify-between">
            <h4 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">ملخص الفرق (Team Summary)</h4>
            <a href="{{ route('teams.index') }}" class="text-[11px] font-semibold text-[rgb(var(--color-copper))] hover:underline">عرض الكل</a>
          </div>

          <div class="divide-y divide-[rgb(var(--color-border)/0.6)]">
            @forelse ($analytics['teams_summary'] as $teamSummary)
            <div class="py-2 flex items-center justify-between gap-3 text-xs">
              <div class="flex items-center gap-2 min-w-0">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[rgb(var(--color-mineral-soft))] text-[rgb(var(--color-mineral))] text-[11px] font-bold">
                  {{ mb_substr($teamSummary->name, 0, 1) }}
                </div>
                <div class="min-w-0">
                  <a href="{{ route('teams.show', $teamSummary) }}" class="font-bold text-[rgb(var(--color-text-primary))] hover:text-[rgb(var(--color-copper))] truncate block">
                    {{ $teamSummary->name }}
                  </a>
                  <p class="text-[10px] text-[rgb(var(--color-text-secondary))]">{{ $teamSummary->members_count }} عضو · {{ $teamSummary->projects_count }} مشروع</p>
                </div>
              </div>
              <span class="gdfh-badge gdfh-badge-mineral text-[10px]">نشط</span>
            </div>
            @empty
            <div class="py-3 text-center text-xs text-[rgb(var(--color-text-secondary))]">لا توجد فرق مرتبطة بعد.</div>
            @endforelse
          </div>
        </div>

      </div>

    </section>

    {{-- Recent Projects & Recent Tasks Grid --}}
    <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">

      {{-- Recent Projects --}}
      <div class="gdfh-card overflow-hidden shadow-sm">
        <div class="border-b border-[rgb(var(--color-border))] p-4 sm:px-6 flex items-center justify-between bg-[rgb(var(--color-surface-soft)/0.2)]">
          <div class="flex items-center gap-2">
            <svg class="h-4 w-4 text-[rgb(var(--color-copper))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">أحدث المشاريع (Recent Projects)</h3>
          </div>
          <a href="{{ route('projects.index') }}" class="text-xs font-semibold text-[rgb(var(--color-copper))] hover:underline">عرض الكل</a>
        </div>

        <div class="divide-y divide-[rgb(var(--color-border))]">
          @forelse ($recents['projects'] as $project)
          <div class="p-4 sm:px-6 flex items-center justify-between gap-4 hover:bg-[rgb(var(--color-surface-soft)/0.5)] transition">
            <div class="min-w-0 space-y-1">
              <a href="{{ route('projects.show', $project) }}" class="text-xs font-bold text-[rgb(var(--color-text-primary))] hover:text-[rgb(var(--color-copper))] truncate block">
                {{ $project->title }}
              </a>
              <div class="flex items-center gap-2 text-[11px] text-[rgb(var(--color-text-secondary))]">
                <span>المالك: {{ $project->owner?->name }}</span>
                @if ($project->team)
                <span>· الفريق: {{ $project->team->name }}</span>
                @endif
              </div>
            </div>
            <span class="gdfh-badge gdfh-badge-copper text-[10px] shrink-0">
              {{ $project->status }}
            </span>
          </div>
          @empty
          <div class="gdfh-empty-state m-6">
            <p class="text-xs text-[rgb(var(--color-text-secondary))]">لا توجد مشاريع مضافة حديثاً.</p>
            <a href="{{ route('projects.create') }}" class="mt-2 text-xs font-bold text-[rgb(var(--color-copper))] hover:underline">+ مشروع جديد</a>
          </div>
          @endforelse
        </div>
      </div>

      {{-- Recent Tasks --}}
      <div class="gdfh-card overflow-hidden shadow-sm">
        <div class="border-b border-[rgb(var(--color-border))] p-4 sm:px-6 flex items-center justify-between bg-[rgb(var(--color-surface-soft)/0.2)]">
          <div class="flex items-center gap-2">
            <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">أحدث المهام (Recent Tasks)</h3>
          </div>
          <a href="{{ route('kanban.index') }}" class="text-xs font-semibold text-[rgb(var(--color-copper))] hover:underline">كانبان</a>
        </div>

        <div class="divide-y divide-[rgb(var(--color-border))]">
          @forelse ($recents['tasks'] as $task)
          <div class="p-4 sm:px-6 flex items-center justify-between gap-4 hover:bg-[rgb(var(--color-surface-soft)/0.5)] transition">
            <div class="min-w-0 space-y-1">
              <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}" class="text-xs font-bold text-[rgb(var(--color-text-primary))] hover:text-[rgb(var(--color-copper))] truncate block">
                {{ $task->title }}
              </a>
              <div class="flex items-center gap-3 text-[11px] text-[rgb(var(--color-text-secondary))]">
                <span>المشروع: {{ $task->project?->title }}</span>
                <span>المسند إليه: {{ $task->assignee?->name ?? 'غير معين' }}</span>
              </div>
            </div>
            <span class="gdfh-badge text-[10px] bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))] border border-[rgb(var(--color-border))] shrink-0">
              {{ $task->status }}
            </span>
          </div>
          @empty
          <div class="gdfh-empty-state m-6">
            <p class="text-xs text-[rgb(var(--color-text-secondary))]">لا توجد مهام مضافة حديثاً.</p>
          </div>
          @endforelse
        </div>
      </div>

    </section>

    {{-- Recent Activity Feed, Discussions & Attachments --}}
    <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">

      {{-- Recent Activity Feed --}}
      <div class="gdfh-card overflow-hidden shadow-sm">
        <div class="border-b border-[rgb(var(--color-border))] p-4 flex items-center justify-between bg-[rgb(var(--color-surface-soft)/0.2)]">
          <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">سجل الأنشطة (Activity Feed)</h3>
          <span class="text-[10px] text-[rgb(var(--color-text-secondary))]">مباشر</span>
        </div>
        <div class="divide-y divide-[rgb(var(--color-border))] p-4 space-y-3">
          @forelse ($recents['activities'] as $act)
          <div class="text-xs space-y-1 pt-2 first:pt-0">
            <div class="flex items-center justify-between">
              <span class="font-bold text-[rgb(var(--color-text-primary))]">{{ $act->user?->name ?? 'النظام' }}</span>
              <span class="text-[10px] text-[rgb(var(--color-text-secondary))]">{{ $act->created_at->diffForHumans() }}</span>
            </div>
            <p class="text-[rgb(var(--color-text-secondary))] text-[11px] leading-4">{{ $act->description }}</p>
          </div>
          @empty
          <div class="text-center text-xs text-[rgb(var(--color-text-secondary))] py-6">لا توجد أنشطة مؤخراً.</div>
          @endforelse
        </div>
      </div>

      {{-- Recent Comments --}}
      <div class="gdfh-card overflow-hidden shadow-sm">
        <div class="border-b border-[rgb(var(--color-border))] p-4 flex items-center justify-between bg-[rgb(var(--color-surface-soft)/0.2)]">
          <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">النقاشات (Discussions)</h3>
        </div>
        <div class="divide-y divide-[rgb(var(--color-border))] p-4 space-y-3">
          @forelse ($recents['comments'] as $comm)
          <div class="text-xs space-y-1 pt-2 first:pt-0">
            <div class="flex items-center justify-between">
              <span class="font-bold text-[rgb(var(--color-text-primary))]">{{ $comm->user?->name }}</span>
              <span class="text-[10px] text-[rgb(var(--color-text-secondary))]">{{ $comm->created_at->diffForHumans() }}</span>
            </div>
            <p class="text-[rgb(var(--color-text-secondary))] text-[11px] line-clamp-2 leading-4">{{ $comm->body ?: $comm->content }}</p>
          </div>
          @empty
          <div class="text-center text-xs text-[rgb(var(--color-text-secondary))] py-6">لا توجد تعليقات مؤخراً.</div>
          @endforelse
        </div>
      </div>

      {{-- Recent Attachments --}}
      <div class="gdfh-card overflow-hidden shadow-sm">
        <div class="border-b border-[rgb(var(--color-border))] p-4 flex items-center justify-between bg-[rgb(var(--color-surface-soft)/0.2)]">
          <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">الملفات والمرفقات (Files)</h3>
        </div>
        <div class="divide-y divide-[rgb(var(--color-border))] p-4 space-y-3">
          @forelse ($recents['attachments'] as $att)
          <div class="text-xs flex items-center justify-between gap-2 pt-2 first:pt-0">
            <div class="min-w-0">
              <p class="font-bold text-[rgb(var(--color-text-primary))] truncate text-[11px]">{{ $att->original_name }}</p>
              <span class="text-[10px] text-[rgb(var(--color-text-secondary))]">{{ $att->formattedSize() }} · {{ $att->created_at->diffForHumans() }}</span>
            </div>
            <a href="{{ route('attachments.download', $att) }}" class="gdfh-btn gdfh-btn-secondary text-[10px] py-1 px-2 shrink-0">تنزيل</a>
          </div>
          @empty
          <div class="text-center text-xs text-[rgb(var(--color-text-secondary))] py-6">لا توجد ملفات مرفقة مؤخراً.</div>
          @endforelse
        </div>
      </div>

    </section>

  </div>

  {{-- ApexCharts Client Script Initializer --}}
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // 1. Velocity Area Chart
      const velocityOptions = {
        series: [{
          name: 'إكتمال المشاريع (%)',
          data: [20, 35, 45, 60, {{ $analytics['project_completion_rate'] }}]
        }, {
          name: 'إكتمال المهام (%)',
          data: [15, 30, 50, 70, {{ $analytics['task_completion_rate'] }}]
        }],
        chart: {
          type: 'area',
          height: 240,
          toolbar: { show: false },
          fontFamily: 'Alexandria, Inter, sans-serif'
        },
        colors: ['#2B58A8', '#F38400'],
        fill: {
          type: 'gradient',
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.45,
            opacityTo: 0.05,
            stops: [0, 90, 100]
          }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2.5 },
        xaxis: {
          categories: ['الأسبوع 1', 'الأسبوع 2', 'الأسبوع 3', 'الأسبوع 4', 'الحالي']
        },
        yaxis: { max: 100 }
      };

      const velocityChart = new ApexCharts(document.querySelector("#velocityChart"), velocityOptions);
      velocityChart.render();

      // 2. Task Distribution Donut Chart
      const taskDistOptions = {
        series: [{{ $kpis['completed_tasks'] }}, {{ $kpis['overdue_tasks'] }}, {{ $kpis['tasks_due_today'] }}, {{ max(0, $kpis['total_tasks'] - $kpis['completed_tasks'] - $kpis['overdue_tasks']) }}],
        chart: {
          type: 'donut',
          height: 220,
          fontFamily: 'Alexandria, Inter, sans-serif'
        },
        labels: ['مكتملة', 'متأخرة', 'تستحق اليوم', 'قيد التنفيذ'],
        colors: ['#10b981', '#ef4444', '#F38400', '#2B58A8'],
        legend: { position: 'bottom', fontSize: '11px' },
        dataLabels: { enabled: false }
      };

      const taskDistChart = new ApexCharts(document.querySelector("#taskDistributionChart"), taskDistOptions);
      taskDistChart.render();
    });
  </script>

</x-app-layout>
