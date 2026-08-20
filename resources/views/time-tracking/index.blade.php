<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[rgb(var(--color-text-primary))]">
          تتبع الوقت وسجلات العمل (Time Tracking & Worklogs)
        </h2>
        <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">
          مؤقت مباشر وسجلات العمل اليومية والأسبوعية للمشاريع والمهام.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <span class="gdfh-badge text-xs font-bold" style="background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));">
          ساعات هذا الأسبوع: {{ $analytics['weekly_hours'] }}h
        </span>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-8">
    <div class="mx-auto max-w-7xl space-y-8">

      {{-- Flash Success Message --}}
      @if (session('success'))
      <div class="flex items-start gap-3 rounded-xl border p-4" style="border-color: rgb(var(--color-success) / 0.30); background-color: rgb(var(--color-success) / 0.08);">
        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" style="background-color: rgb(var(--color-success) / 0.12); color: rgb(var(--color-success));">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5l4 4L19 7" /></svg>
        </div>
        <div>
          <p class="text-sm font-bold text-[rgb(var(--color-text-primary))]">تمت العملية بنجاح</p>
          <p class="mt-1 text-sm" style="color: rgb(var(--color-success));">{{ session('success') }}</p>
        </div>
      </div>
      @endif

      {{-- 1. Live Timer Section --}}
      <section class="gdfh-card p-6 space-y-6">
        <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">المؤقت المباشر (Live Timer)</h3>

        @if ($activeTimer)
        {{-- Active Timer Card --}}
        <div class="p-6 rounded-2xl border border-[rgb(var(--color-border))] flex flex-col md:flex-row items-center justify-between gap-6" style="background-color: rgb(var(--color-surface-soft)/0.4);">
          <div class="space-y-2 text-center md:text-start">
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
              <span class="gdfh-badge text-xs font-bold {{ $activeTimer->isRunning() ? 'bg-emerald-500/10 text-emerald-500 animate-pulse' : 'bg-amber-500/10 text-amber-500' }}">
                {{ $activeTimer->isRunning() ? 'يعمل الآن (Running)' : 'متوقف مؤقتاً (Paused)' }}
              </span>
              <span class="text-xs font-bold text-[rgb(var(--color-copper))]">{{ $activeTimer->project?->title }}</span>
            </div>

            <h4 class="text-base font-bold text-[rgb(var(--color-text-primary))]">
              {{ $activeTimer->task?->title ?? 'عمل على المشروع بدون مهمة محدودة' }}
            </h4>

            @if ($activeTimer->notes)
            <p class="text-xs text-[rgb(var(--color-text-secondary))]">{{ $activeTimer->notes }}</p>
            @endif
          </div>

          {{-- Live Duration & Controls --}}
          <div class="flex items-center gap-4">
            <div class="text-3xl font-extrabold font-mono tracking-wider text-[rgb(var(--color-text-primary))]">
              {{ $activeTimer->formattedDuration() }}
            </div>

            <div class="flex items-center gap-2">
              @if ($activeTimer->isRunning())
              <form method="POST" action="{{ route('time-tracking.pause', $activeTimer) }}">
                @csrf
                <button type="submit" class="gdfh-btn text-xs py-2 px-4 bg-amber-500/10 text-amber-500 hover:bg-amber-500/20 font-bold">
                  إيقاف مؤقت
                </button>
              </form>
              @else
              <form method="POST" action="{{ route('time-tracking.resume', $activeTimer) }}">
                @csrf
                <button type="submit" class="gdfh-btn gdfh-btn-brand text-xs py-2 px-4 font-bold">
                  استئناف
                </button>
              </form>
              @endif

              <form method="POST" action="{{ route('time-tracking.stop', $activeTimer) }}">
                @csrf
                <button type="submit" class="gdfh-btn text-xs py-2 px-4 bg-red-500/10 text-red-500 hover:bg-red-500/20 font-bold">
                  إيقاف وحفظ
                </button>
              </form>
            </div>
          </div>
        </div>

        @else

        {{-- Start New Timer Form --}}
        <form method="POST" action="{{ route('time-tracking.start') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4 items-end">
          @csrf
          
          <div>
            <label class="block text-xs font-bold text-[rgb(var(--color-text-primary))] mb-1">المشروع</label>
            <select name="project_id" required class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))]">
              <option value="">اختر المشروع...</option>
              @foreach ($projects as $proj)
              <option value="{{ $proj->id }}">{{ $proj->title }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-[rgb(var(--color-text-primary))] mb-1">المهمة (اختياري)</label>
            <select name="task_id" class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))]">
              <option value="">بدون مهمة مخصصة...</option>
              @foreach ($tasks as $tsk)
              <option value="{{ $tsk->id }}">{{ $tsk->title }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-[rgb(var(--color-text-primary))] mb-1">ملاحظات النشاط</label>
            <input type="text" name="notes" placeholder="ما الذي تعمل عليه الآن؟" class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))]">
          </div>

          <div>
            <button type="submit" class="w-full gdfh-btn gdfh-btn-brand text-xs py-2.5 flex items-center justify-center gap-1.5">
              <svg class="h-4 w-4 fill-current shrink-0" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
              <span>تشغيل المؤقت المباشر</span>
            </button>
          </div>
        </form>
        @endif
      </section>

      {{-- 2. Analytics Summary Grid --}}
      <section class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-6">
        <div class="gdfh-card p-4 space-y-1">
          <span class="text-xs text-[rgb(var(--color-text-secondary))]">ساعات اليوم</span>
          <div class="text-2xl font-bold text-[rgb(var(--color-copper))]">{{ $analytics['today_hours'] }}h</div>
        </div>

        <div class="gdfh-card p-4 space-y-1">
          <span class="text-xs text-[rgb(var(--color-text-secondary))]">ساعات الأسبوع</span>
          <div class="text-2xl font-bold text-[rgb(var(--color-text-primary))]">{{ $analytics['weekly_hours'] }}h</div>
        </div>

        <div class="gdfh-card p-4 space-y-1">
          <span class="text-xs text-[rgb(var(--color-text-secondary))]">ساعات الشهر</span>
          <div class="text-2xl font-bold text-emerald-500">{{ $analytics['monthly_hours'] }}h</div>
        </div>

        <div class="gdfh-card p-4 space-y-1">
          <span class="text-xs text-[rgb(var(--color-text-secondary))]">إجمالي الساعات</span>
          <div class="text-2xl font-bold text-[rgb(var(--color-text-primary))]">{{ $analytics['total_hours'] }}h</div>
        </div>

        <div class="gdfh-card p-4 space-y-1">
          <span class="text-xs text-[rgb(var(--color-text-secondary))]">نسبة الساعات القابلة للدفع</span>
          <div class="text-2xl font-bold text-[rgb(var(--color-copper))]">{{ $analytics['billable_percentage'] }}%</div>
        </div>

        <div class="gdfh-card p-4 space-y-1">
          <span class="text-xs text-[rgb(var(--color-text-secondary))]">متوسط المهمة</span>
          <div class="text-2xl font-bold text-[rgb(var(--color-text-primary))]">{{ $analytics['avg_task_duration_minutes'] }}m</div>
        </div>
      </section>

      {{-- 3. Manual Entry & Recent Worklogs --}}
      <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        
        {{-- Manual Form --}}
        <div class="gdfh-card p-6 space-y-4">
          <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">إدخال يدوي (Manual Worklog)</h3>

          <form method="POST" action="{{ route('time-tracking.manual') }}" class="space-y-4 text-xs">
            @csrf

            <div>
              <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">المشروع</label>
              <select name="project_id" required class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))]">
                <option value="">اختر المشروع...</option>
                @foreach ($projects as $proj)
                <option value="{{ $proj->id }}">{{ $proj->title }}</option>
                @endforeach
              </select>
            </div>

            <div>
              <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">المهمة</label>
              <select name="task_id" class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))]">
                <option value="">بدون مهمة...</option>
                @foreach ($tasks as $tsk)
                <option value="{{ $tsk->id }}">{{ $tsk->title }}</option>
                @endforeach
              </select>
            </div>

            <div>
              <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">المدة (بالدقائق)</label>
              <input type="number" name="duration_minutes" min="1" max="1440" required placeholder="مثال: 60" class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))]">
            </div>

            <div>
              <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">التاريخ</label>
              <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))]">
            </div>

            <div>
              <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">ملاحظات</label>
              <textarea name="notes" rows="2" placeholder="تفاصيل إنجاز العمل..." class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))]"></textarea>
            </div>

            <button type="submit" class="w-full gdfh-btn gdfh-btn-secondary text-xs py-2.5">
              + إضافة سجّل عمل يدوي
            </button>
          </form>
        </div>

        {{-- Worklogs History Table --}}
        <div class="gdfh-card overflow-hidden lg:col-span-2 space-y-0">
          <div class="border-b border-[rgb(var(--color-border))] p-5 flex items-center justify-between">
            <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">سجلات العمل الأخيرة (Worklogs History)</h3>
          </div>

          <div class="divide-y divide-[rgb(var(--color-border))]">
            @forelse ($worklogs as $log)
            <div class="p-4 flex items-center justify-between gap-4 hover:bg-[rgb(var(--color-surface-soft)/0.4)] transition">
              <div class="min-w-0 space-y-1">
                <div class="flex items-center gap-2">
                  <span class="text-xs font-bold text-[rgb(var(--color-text-primary))] truncate">{{ $log->project?->title }}</span>
                  @if ($log->task)
                  <span class="text-[11px] text-[rgb(var(--color-text-secondary))]">· {{ $log->task->title }}</span>
                  @endif
                </div>
                @if ($log->notes)
                <p class="text-xs text-[rgb(var(--color-text-secondary))] truncate">{{ $log->notes }}</p>
                @endif
                <div class="text-[10px] text-[rgb(var(--color-text-secondary))]">
                  {{ $log->created_at->format('Y-m-d H:i') }} · {{ $log->is_manual ? 'إدخال يدوي' : 'مؤقت مباشر' }}
                </div>
              </div>

              <div class="flex items-center gap-3 shrink-0">
                <span class="text-sm font-bold font-mono text-[rgb(var(--color-copper))]">
                  {{ $log->formattedDuration() }}
                </span>

                <form method="POST" action="{{ route('time-tracking.destroy', $log) }}" onsubmit="return confirm('هل تريد حذف هذا السجل؟')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="gdfh-btn text-xs py-1 px-2 bg-red-500/10 text-red-500 hover:bg-red-500/20">
                    حذف
                  </button>
                </form>
              </div>
            </div>
            @empty
            <div class="p-12 text-center text-xs text-[rgb(var(--color-text-secondary))]">لا توجد سجلات عمل مسجلة حتى الآن.</div>
            @endforelse
          </div>

          @if ($worklogs->hasPages())
          <div class="border-t border-[rgb(var(--color-border))] p-4">
            {{ $worklogs->links() }}
          </div>
          @endif
        </div>

      </section>

    </div>
  </div>
</x-app-layout>
