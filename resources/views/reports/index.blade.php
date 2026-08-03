<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[rgb(var(--color-text-primary))]">
          التقارير وتحليلات الإنتاجية (Enterprise Reports & Analytics)
        </h2>
        <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">
          تحليلات شامِلة لأداء المشاريع، إنتاجية الفريق، ومعدلات اكتمال المهام.
        </p>
      </div>

      {{-- Export Buttons (UI Only) --}}
      <div class="flex items-center gap-2">
        <button type="button" onclick="alert('جاري تجهيز تقرير PDF للتنزيل...')" class="gdfh-btn gdfh-btn-secondary text-xs py-1.5 px-3">
          📥 تصدير PDF
        </button>
        <button type="button" onclick="alert('جاري تصدير بيانات CSV...')" class="gdfh-btn gdfh-btn-secondary text-xs py-1.5 px-3">
          📊 تصدير CSV
        </button>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-8">
    <div class="mx-auto max-w-7xl space-y-8">

      {{-- Filter Toolbar --}}
      <form method="GET" action="{{ route('reports.index') }}" class="gdfh-card p-4 space-y-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 items-end text-xs">
          
          <div>
            <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">من تاريخ</label>
            <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2 text-xs text-[rgb(var(--color-text-primary))]">
          </div>

          <div>
            <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">إلى تاريخ</label>
            <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2 text-xs text-[rgb(var(--color-text-primary))]">
          </div>

          <div>
            <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">المشروع</label>
            <select name="project_id" class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2 text-xs text-[rgb(var(--color-text-primary))]">
              <option value="">جميع المشاريع</option>
              @foreach ($user_projects as $proj)
              <option value="{{ $proj->id }}" {{ ($filters['project_id'] ?? '') == $proj->id ? 'selected' : '' }}>{{ $proj->title }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">الفريق</label>
            <select name="team_id" class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2 text-xs text-[rgb(var(--color-text-primary))]">
              <option value="">جميع الفرق</option>
              @foreach ($user_teams as $tm)
              <option value="{{ $tm->id }}" {{ ($filters['team_id'] ?? '') == $tm->id ? 'selected' : '' }}>{{ $tm->name }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">الحالة</label>
            <select name="status" class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2 text-xs text-[rgb(var(--color-text-primary))]">
              <option value="">جميع الحالات</option>
              <option value="open" {{ ($filters['status'] ?? '') === 'open' ? 'selected' : '' }}>مفتوح / نشط</option>
              <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>مكتمل</option>
            </select>
          </div>

          <div class="flex items-center gap-2">
            <button type="submit" class="w-full gdfh-btn gdfh-btn-brand text-xs py-2">
              تطبيق الفلترة
            </button>
            <a href="{{ route('reports.index') }}" class="gdfh-btn gdfh-btn-secondary text-xs py-2 px-3">
              إعادة ضبط
            </a>
          </div>
        </div>
      </form>

      {{-- 1. KPI Cards Grid --}}
      <section class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-5">
        
        {{-- Productivity Score --}}
        <div class="gdfh-card p-4 space-y-1 border-s-4 border-s-[rgb(var(--color-copper))]">
          <span class="text-xs text-[rgb(var(--color-text-secondary))]">مؤشر الإنتاجية (Score)</span>
          <div class="text-2xl font-bold text-[rgb(var(--color-copper))]">{{ $kpis['productivity_score'] }} / 100</div>
          <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">تقييم أداء الشامل</p>
        </div>

        {{-- Completion Rate --}}
        <div class="gdfh-card p-4 space-y-1">
          <span class="text-xs text-[rgb(var(--color-text-secondary))]">معدل اكتمال المهام</span>
          <div class="text-2xl font-bold text-emerald-500">{{ $kpis['completion_rate'] }}%</div>
          <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">{{ $kpis['completed_tasks'] }} من {{ $kpis['total_tasks'] }} مهمة</p>
        </div>

        {{-- Avg Completion Days --}}
        <div class="gdfh-card p-4 space-y-1">
          <span class="text-xs text-[rgb(var(--color-text-secondary))]">متوسط زمن الإنجاز</span>
          <div class="text-2xl font-bold text-[rgb(var(--color-text-primary))]">{{ $kpis['avg_completion_days'] }} يوم</div>
          <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">من تاريخ الإنشاء للتسليم</p>
        </div>

        {{-- Tracked Hours --}}
        <div class="gdfh-card p-4 space-y-1">
          <span class="text-xs text-[rgb(var(--color-text-secondary))]">إجمالي ساعات العمل</span>
          <div class="text-2xl font-bold text-[rgb(var(--color-copper))]">{{ $kpis['total_tracked_hours'] }}h</div>
          <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">ساعات مسجلة</p>
        </div>

        {{-- Total Activities & Interaction --}}
        <div class="gdfh-card p-4 space-y-1">
          <span class="text-xs text-[rgb(var(--color-text-secondary))]">إجمالي الأنشطة</span>
          <div class="text-2xl font-bold text-[rgb(var(--color-text-primary))]">{{ $kpis['total_activities'] }}</div>
          <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">{{ $kpis['total_comments'] }} تعليق · {{ $kpis['total_attachments'] }} مرفق</p>
        </div>

      </section>

      {{-- 2. Structured Charts Section --}}
      <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        
        {{-- Task Status Breakdown --}}
        <div class="gdfh-card p-6 space-y-4">
          <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">توزيع حالات المهام (Status Distribution)</h3>

          <div class="space-y-3">
            @foreach ($charts['pie_chart']['labels'] as $index => $label)
            @php
            $cnt = $charts['pie_chart']['data'][$index];
            $pct = $kpis['total_tasks'] > 0 ? round(($cnt / $kpis['total_tasks']) * 100) : 0;
            @endphp
            <div>
              <div class="flex items-center justify-between text-xs mb-1">
                <span class="font-bold text-[rgb(var(--color-text-primary))]">{{ $label }}</span>
                <span class="text-[rgb(var(--color-text-secondary))]">{{ $cnt }} مهمة ({{ $pct }}%)</span>
              </div>
              <div class="h-2 w-full rounded-full bg-[rgb(var(--color-surface-soft))] overflow-hidden">
                <div class="h-full rounded-full bg-[rgb(var(--color-copper))]" style="width: {{ $pct }}%;"></div>
              </div>
            </div>
            @endforeach
          </div>
        </div>

        {{-- Weekly Task Trend --}}
        <div class="gdfh-card p-6 space-y-4">
          <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">الاتجاه الأسبوعي لإنجاز المهام (Weekly Trend)</h3>

          <div class="space-y-3 pt-2">
            @foreach ($charts['line_chart']['labels'] as $index => $weekLabel)
            @php
            $weekCount = $charts['line_chart']['data'][$index];
            @endphp
            <div class="flex items-center justify-between text-xs border-b border-[rgb(var(--color-border))] pb-2">
              <span class="font-bold text-[rgb(var(--color-text-primary))]">{{ $weekLabel }}</span>
              <span class="gdfh-badge text-xs font-bold bg-emerald-500/10 text-emerald-500">
                {{ $weekCount }} مهام مكتملة
              </span>
            </div>
            @endforeach
          </div>
        </div>

      </section>

      {{-- 3. Leaderboard & Team Performance --}}
      <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- User Leaderboard --}}
        <div class="gdfh-card overflow-hidden space-y-0">
          <div class="border-b border-[rgb(var(--color-border))] p-5">
            <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">قائمة المتصدرين والأداء الفردي (User Leaderboard)</h3>
          </div>

          <div class="divide-y divide-[rgb(var(--color-border))]">
            @forelse ($reports['user_leaderboard'] as $ub)
            <div class="p-4 flex items-center justify-between gap-3 text-xs">
              <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[rgb(var(--color-copper-soft))] text-xs font-bold text-[rgb(var(--color-copper))]">
                  {{ mb_substr($ub['user']->name, 0, 1) }}
                </div>
                <div>
                  <h4 class="font-bold text-[rgb(var(--color-text-primary))]">{{ $ub['user']->name }}</h4>
                  <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">{{ $ub['completed_tasks'] }} مهمة مكتملة من إجمالي {{ $ub['total_tasks'] }}</p>
                </div>
              </div>
              <span class="gdfh-badge text-xs font-bold" style="background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));">
                إنجاز: {{ $ub['completion_rate'] }}%
              </span>
            </div>
            @empty
            <div class="p-6 text-center text-xs text-[rgb(var(--color-text-secondary))]">لا توجد بيانات مستخدمين متاحة.</div>
            @endforelse
          </div>
        </div>

        {{-- Team Performance Table --}}
        <div class="gdfh-card overflow-hidden space-y-0">
          <div class="border-b border-[rgb(var(--color-border))] p-5">
            <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">أداء فرق العمل (Team Performance)</h3>
          </div>

          <div class="divide-y divide-[rgb(var(--color-border))]">
            @forelse ($reports['teams'] as $tm)
            <div class="p-4 flex items-center justify-between gap-3 text-xs">
              <div>
                <a href="{{ route('teams.show', $tm) }}" class="font-bold text-[rgb(var(--color-text-primary))] hover:text-[rgb(var(--color-copper))]">
                  {{ $tm->name }}
                </a>
                <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">{{ $tm->members_count }} أعضاء · {{ $tm->projects_count }} مشاريع</p>
              </div>
              <span class="gdfh-badge text-xs font-bold bg-gray-500/10 text-gray-600">نشط</span>
            </div>
            @empty
            <div class="p-6 text-center text-xs text-[rgb(var(--color-text-secondary))]">لا توجد فرق مرتبطة.</div>
            @endforelse
          </div>
        </div>

      </section>

    </div>
  </div>
</x-app-layout>
