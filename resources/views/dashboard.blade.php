<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[rgb(var(--color-text-primary))]">
          لوحة التحكم والتحليلات (Enterprise Dashboard)
        </h2>
        <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">
          مرحباً بك، {{ Auth::user()->name }}! ملخص شامل وأداء مباشر لمساحة العمل الخاصة بك.
        </p>
      </div>

      {{-- Quick Actions Toolbar --}}
      <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('projects.create') }}" class="gdfh-btn gdfh-btn-brand text-xs">
          + مشروع جديد
        </a>
        <a href="{{ route('teams.create') }}" class="gdfh-btn gdfh-btn-secondary text-xs">
          + فريق جديد
        </a>
        <a href="{{ route('invitations.index') }}" class="gdfh-btn gdfh-btn-secondary text-xs">
          دعوة عضو
        </a>
        <a href="{{ route('notifications.index') }}" class="gdfh-btn gdfh-btn-secondary text-xs relative">
          الإشعارات
          @if ($kpis['unread_notifications'] > 0)
          <span class="ms-1.5 flex h-4 px-1.5 items-center justify-center rounded-full text-[10px] font-bold bg-[rgb(var(--color-copper))] text-[#1b1511]">
            {{ $kpis['unread_notifications'] }}
          </span>
          @endif
        </a>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-8">
    <div class="mx-auto max-w-7xl space-y-8">

      {{-- 1. KPI Cards Grid (10 Metrics) --}}
      <section class="space-y-3">
        <h3 class="text-xs font-bold uppercase tracking-wider text-[rgb(var(--color-text-secondary))]">مؤشرات الأداء الرئيسية (KPIs)</h3>
        
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-5">
          {{-- Total Projects --}}
          <div class="gdfh-card p-4 space-y-1">
            <span class="text-xs text-[rgb(var(--color-text-secondary))]">إجمالي المشاريع</span>
            <div class="text-2xl font-bold text-[rgb(var(--color-text-primary))]">{{ $kpis['total_projects'] }}</div>
            <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">مشاريع بيئة العمل</p>
          </div>

          {{-- Active Projects --}}
          <div class="gdfh-card p-4 space-y-1">
            <span class="text-xs text-[rgb(var(--color-text-secondary))]">المشاريع النشطة</span>
            <div class="text-2xl font-bold text-[rgb(var(--color-copper))]">{{ $kpis['active_projects'] }}</div>
            <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">قيد التنفيذ والمراجعة</p>
          </div>

          {{-- Completed Projects --}}
          <div class="gdfh-card p-4 space-y-1">
            <span class="text-xs text-[rgb(var(--color-text-secondary))]">المشاريع المكتملة</span>
            <div class="text-2xl font-bold text-emerald-500">{{ $kpis['completed_projects'] }}</div>
            <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">مكتملة بالكامل</p>
          </div>

          {{-- Total Tasks --}}
          <div class="gdfh-card p-4 space-y-1">
            <span class="text-xs text-[rgb(var(--color-text-secondary))]">إجمالي المهام</span>
            <div class="text-2xl font-bold text-[rgb(var(--color-text-primary))]">{{ $kpis['total_tasks'] }}</div>
            <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">في كل المشاريع</p>
          </div>

          {{-- Completed Tasks --}}
          <div class="gdfh-card p-4 space-y-1">
            <span class="text-xs text-[rgb(var(--color-text-secondary))]">المهام المكتملة</span>
            <div class="text-2xl font-bold text-emerald-500">{{ $kpis['completed_tasks'] }}</div>
            <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">مكتملة بنجاح</p>
          </div>

          {{-- Overdue Tasks --}}
          <div class="gdfh-card p-4 space-y-1">
            <span class="text-xs text-[rgb(var(--color-text-secondary))]">المهام المتأخرة</span>
            <div class="text-2xl font-bold text-red-500">{{ $kpis['overdue_tasks'] }}</div>
            <p class="text-[11px] text-red-400 font-medium">تجاوزت الموعد</p>
          </div>

          {{-- Tasks Due Today --}}
          <div class="gdfh-card p-4 space-y-1">
            <span class="text-xs text-[rgb(var(--color-text-secondary))]">تستحق اليوم</span>
            <div class="text-2xl font-bold text-amber-500">{{ $kpis['tasks_due_today'] }}</div>
            <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">موعدها اليوم</p>
          </div>

          {{-- Teams Count --}}
          <div class="gdfh-card p-4 space-y-1">
            <span class="text-xs text-[rgb(var(--color-text-secondary))]">الفرق المرتبطة</span>
            <div class="text-2xl font-bold text-[rgb(var(--color-text-primary))]">{{ $kpis['teams_count'] }}</div>
            <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">فريق عمل</p>
          </div>

          {{-- Unread Notifications --}}
          <div class="gdfh-card p-4 space-y-1">
            <span class="text-xs text-[rgb(var(--color-text-secondary))]">إشعارات غير مقروءة</span>
            <div class="text-2xl font-bold text-[rgb(var(--color-copper))]">{{ $kpis['unread_notifications'] }}</div>
            <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">تتطلب الانتباه</p>
          </div>

          {{-- Overall Progress --}}
          <div class="gdfh-card p-4 space-y-1">
            <span class="text-xs text-[rgb(var(--color-text-secondary))]">نسبة الإنجاز العام</span>
            <div class="text-2xl font-bold text-[rgb(var(--color-copper))]">{{ $kpis['overall_progress'] }}%</div>
            <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">معدل التقدّم الكلي</p>
          </div>
        </div>
      </section>

      {{-- 2. Analytics Widgets --}}
      <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        
        {{-- Project & Task Progress Widget --}}
        <div class="gdfh-card p-6 space-y-6 lg:col-span-2">
          <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">تحليلات ومعدلات الإنجاز (Analytics)</h3>

          <div class="space-y-4">
            <div>
              <div class="flex items-center justify-between text-xs mb-1.5">
                <span class="font-bold text-[rgb(var(--color-text-primary))]">معدل اكتمال المشاريع</span>
                <span class="font-bold text-[rgb(var(--color-copper))]">{{ $analytics['project_completion_rate'] }}%</span>
              </div>
              <div class="h-2.5 w-full overflow-hidden rounded-full bg-[rgb(var(--color-surface-soft))]">
                <div class="h-full rounded-full transition-all duration-500 bg-[rgb(var(--color-copper))]" style="width: {{ $analytics['project_completion_rate'] }}%;"></div>
              </div>
            </div>

            <div>
              <div class="flex items-center justify-between text-xs mb-1.5">
                <span class="font-bold text-[rgb(var(--color-text-primary))]">معدل اكتمال المهام</span>
                <span class="font-bold text-emerald-500">{{ $analytics['task_completion_rate'] }}%</span>
              </div>
              <div class="h-2.5 w-full overflow-hidden rounded-full bg-[rgb(var(--color-surface-soft))]">
                <div class="h-full rounded-full transition-all duration-500 bg-emerald-500" style="width: {{ $analytics['task_completion_rate'] }}%;"></div>
              </div>
            </div>
          </div>

          <div class="pt-4 border-t border-[rgb(var(--color-border))] grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
            <div>
              <span class="text-[rgb(var(--color-text-secondary))]">الأنشطة (آخر 7 أيام)</span>
              <p class="mt-1 text-base font-bold text-[rgb(var(--color-text-primary))]">{{ $analytics['recent_activities_count'] }} إجراء</p>
            </div>
            <div>
              <span class="text-[rgb(var(--color-text-secondary))]">معدل المهام المكتملة</span>
              <p class="mt-1 text-base font-bold text-emerald-500">{{ $kpis['completed_tasks'] }} من {{ $kpis['total_tasks'] }}</p>
            </div>
            <div>
              <span class="text-[rgb(var(--color-text-secondary))]">المهام الحرجة المتأخرة</span>
              <p class="mt-1 text-base font-bold text-red-500">{{ $kpis['overdue_tasks'] }} مهمة</p>
            </div>
          </div>
        </div>

        {{-- Team Summary Widget --}}
        <div class="gdfh-card p-6 space-y-4">
          <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">ملخص الفرق (Team Summary)</h3>

          <div class="divide-y divide-[rgb(var(--color-border))]">
            @forelse ($analytics['teams_summary'] as $teamSummary)
            <div class="py-3 flex items-center justify-between gap-3 text-xs">
              <div>
                <a href="{{ route('teams.show', $teamSummary) }}" class="font-bold text-[rgb(var(--color-text-primary))] hover:text-[rgb(var(--color-copper))]">
                  {{ $teamSummary->name }}
                </a>
                <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">{{ $teamSummary->members_count }} عضو · {{ $teamSummary->projects_count }} مشروع</p>
              </div>
              <span class="gdfh-badge text-[10px]" style="background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));">نشط</span>
            </div>
            @empty
            <div class="py-6 text-center text-xs text-[rgb(var(--color-text-secondary))]">لا توجد فرق مرتبطة بعد.</div>
            @endforelse
          </div>
        </div>

      </section>

      {{-- 3. Recent Sections Grid (Projects & Tasks) --}}
      <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">

        {{-- Recent Projects --}}
        <div class="gdfh-card overflow-hidden">
          <div class="border-b border-[rgb(var(--color-border))] p-5 flex items-center justify-between">
            <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">أحدث المشاريع (Recent Projects)</h3>
            <a href="{{ route('projects.index') }}" class="text-xs font-bold text-[rgb(var(--color-copper))] hover:underline">عرض الكل</a>
          </div>

          <div class="divide-y divide-[rgb(var(--color-border))]">
            @forelse ($recents['projects'] as $project)
            <div class="p-4 flex items-center justify-between gap-3 hover:bg-[rgb(var(--color-surface-soft)/0.5)] transition">
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
              <span class="gdfh-badge text-[11px]" style="background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));">
                {{ $project->status }}
              </span>
            </div>
            @empty
            <div class="p-6 text-center text-xs text-[rgb(var(--color-text-secondary))]">لا توجد مشاريع مضافة حديثاً.</div>
            @endforelse
          </div>
        </div>

        {{-- Recent Tasks --}}
        <div class="gdfh-card overflow-hidden">
          <div class="border-b border-[rgb(var(--color-border))] p-5 flex items-center justify-between">
            <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">أحدث المهام (Recent Tasks)</h3>
          </div>

          <div class="divide-y divide-[rgb(var(--color-border))]">
            @forelse ($recents['tasks'] as $task)
            <div class="p-4 flex items-center justify-between gap-3 hover:bg-[rgb(var(--color-surface-soft)/0.5)] transition">
              <div class="min-w-0 space-y-1">
                <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}" class="text-xs font-bold text-[rgb(var(--color-text-primary))] hover:text-[rgb(var(--color-copper))] truncate block">
                  {{ $task->title }}
                </a>
                <div class="flex items-center gap-3 text-[11px] text-[rgb(var(--color-text-secondary))]">
                  <span>المشروع: {{ $task->project?->title }}</span>
                  <span>المسند إليه: {{ $task->assignee?->name ?? 'غير معين' }}</span>
                </div>
              </div>
              <span class="gdfh-badge text-[11px] bg-gray-500/10 text-gray-600">
                {{ $task->status }}
              </span>
            </div>
            @empty
            <div class="p-6 text-center text-xs text-[rgb(var(--color-text-secondary))]">لا توجد مهام مضافة حديثاً.</div>
            @endforelse
          </div>
        </div>

      </section>

      {{-- 4. Recent Activities, Comments & Attachments --}}
      <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Recent Activities --}}
        <div class="gdfh-card overflow-hidden">
          <div class="border-b border-[rgb(var(--color-border))] p-4">
            <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">أحدث الأنشطة (Activities)</h3>
          </div>
          <div class="divide-y divide-[rgb(var(--color-border))] p-4 space-y-3">
            @forelse ($recents['activities'] as $act)
            <div class="text-xs space-y-1">
              <div class="flex items-center justify-between">
                <span class="font-bold text-[rgb(var(--color-text-primary))]">{{ $act->user?->name ?? 'النظام' }}</span>
                <span class="text-[10px] text-[rgb(var(--color-text-secondary))]">{{ $act->created_at->diffForHumans() }}</span>
              </div>
              <p class="text-[rgb(var(--color-text-secondary))] text-[11px] leading-4">{{ $act->description }}</p>
            </div>
            @empty
            <div class="text-center text-xs text-[rgb(var(--color-text-secondary))] py-4">لا توجد أنشطة مؤخراً.</div>
            @endforelse
          </div>
        </div>

        {{-- Recent Comments --}}
        <div class="gdfh-card overflow-hidden">
          <div class="border-b border-[rgb(var(--color-border))] p-4">
            <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">أحدث النقاشات (Comments)</h3>
          </div>
          <div class="divide-y divide-[rgb(var(--color-border))] p-4 space-y-3">
            @forelse ($recents['comments'] as $comm)
            <div class="text-xs space-y-1">
              <div class="flex items-center justify-between">
                <span class="font-bold text-[rgb(var(--color-text-primary))]">{{ $comm->user?->name }}</span>
                <span class="text-[10px] text-[rgb(var(--color-text-secondary))]">{{ $comm->created_at->diffForHumans() }}</span>
              </div>
              <p class="text-[rgb(var(--color-text-secondary))] text-[11px] line-clamp-2 leading-4">{{ $comm->body ?: $comm->content }}</p>
            </div>
            @empty
            <div class="text-center text-xs text-[rgb(var(--color-text-secondary))] py-4">لا توجد تعليقات مؤخراً.</div>
            @endforelse
          </div>
        </div>

        {{-- Recent Attachments --}}
        <div class="gdfh-card overflow-hidden">
          <div class="border-b border-[rgb(var(--color-border))] p-4">
            <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">أحدث المرفقات (Attachments)</h3>
          </div>
          <div class="divide-y divide-[rgb(var(--color-border))] p-4 space-y-3">
            @forelse ($recents['attachments'] as $att)
            <div class="text-xs flex items-center justify-between gap-2">
              <div class="min-w-0">
                <p class="font-bold text-[rgb(var(--color-text-primary))] truncate text-[11px]">{{ $att->original_name }}</p>
                <span class="text-[10px] text-[rgb(var(--color-text-secondary))]">{{ $att->formattedSize() }} · {{ $att->created_at->diffForHumans() }}</span>
              </div>
              <a href="{{ route('attachments.download', $att) }}" class="gdfh-btn gdfh-btn-secondary text-[10px] py-1 px-2 shrink-0">تنزيل</a>
            </div>
            @empty
            <div class="text-center text-xs text-[rgb(var(--color-text-secondary))] py-4">لا توجد ملفات مرفقة مؤخراً.</div>
            @endforelse
          </div>
        </div>

      </section>

    </div>
  </div>
</x-app-layout>
