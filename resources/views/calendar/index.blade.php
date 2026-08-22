<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[rgb(var(--color-text-primary))]">
          التقويم والمواعيد (Enterprise Calendar)
        </h2>
        <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">
          متابعة وإدارة مواعيد المشاريع والمهام والأحداث الخاصة بجداول تفاعلية.
        </p>
      </div>

      <div class="flex items-center gap-3">
        {{-- View Switcher Buttons --}}
        <div class="flex items-center gap-1 rounded-xl bg-[rgb(var(--color-surface-soft))] p-1 border border-[rgb(var(--color-border))]">
          <a href="{{ route('calendar.index', array_merge(request()->query(), ['view' => 'month'])) }}"
            class="gdfh-btn text-xs py-1 px-3 {{ $currentView === 'month' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
            الشهر (Month)
          </a>
          <a href="{{ route('calendar.index', array_merge(request()->query(), ['view' => 'week'])) }}"
            class="gdfh-btn text-xs py-1 px-3 {{ $currentView === 'week' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
            الأسبوع (Week)
          </a>
          <a href="{{ route('calendar.index', array_merge(request()->query(), ['view' => 'agenda'])) }}"
            class="gdfh-btn text-xs py-1 px-3 {{ $currentView === 'agenda' ? 'gdfh-btn-brand shadow-sm' : 'bg-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]' }}">
            الأجندة (Agenda)
          </a>
        </div>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-6"
       x-data="{
         showModal: false,
         isEditing: false,
         eventForm: {
           id: null,
           title: '',
           description: '',
           start_at: '',
           end_at: '',
           color: 'copper',
           project_id: '',
           location: ''
         },
         openCreateModal(dateStr = '') {
           this.isEditing = false;
           const dateVal = dateStr || new Date().toISOString().slice(0, 10);
           this.eventForm = {
             id: null,
             title: '',
             description: '',
             start_at: `${dateVal}T09:00`,
             end_at: '',
             color: 'copper',
             project_id: '',
             location: ''
           };
           this.showModal = true;
         },
         openEditModal(evt) {
           this.isEditing = true;
           this.eventForm = {
             id: evt.db_id,
             title: evt.title,
             description: evt.description || '',
             start_at: evt.datetime ? evt.datetime.slice(0, 16) : '',
             end_at: evt.end_at ? evt.end_at.replace(' ', 'T') : '',
             color: evt.color_category || 'copper',
             project_id: evt.related_project ? evt.related_project.id : '',
             location: evt.location || ''
           };
           this.showModal = true;
         }
       }">
    <div class="mx-auto max-w-7xl space-y-6">

      {{-- Success Flash Alert --}}
      @if (session('success'))
      <div class="flex items-center justify-between p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-bold">
        <span>{{ session('success') }}</span>
      </div>
      @endif

      {{-- Filters & Navigation Bar --}}
      <div class="gdfh-card p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        {{-- Month / Week Navigators --}}
        <div class="flex items-center gap-3">
          @if ($currentView === 'month')
          <a href="{{ route('calendar.index', array_merge(request()->query(), ['month' => $prev_month])) }}" class="gdfh-btn gdfh-btn-secondary text-xs px-2.5 py-1">
            &rarr; الشهر السابق
          </a>
          <span class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
            {{ $current_month->locale('ar')->translatedFormat('F Y') }}
          </span>
          <a href="{{ route('calendar.index', array_merge(request()->query(), ['month' => $next_month])) }}" class="gdfh-btn gdfh-btn-secondary text-xs px-2.5 py-1">
            الشهر التالي &larr;
          </a>
          @elseif ($currentView === 'week')
          <a href="{{ route('calendar.index', array_merge(request()->query(), ['week_start' => $prev_week])) }}" class="gdfh-btn gdfh-btn-secondary text-xs px-2.5 py-1">
            &rarr; الأسبوع السابق
          </a>
          <span class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
            {{ $start_date->format('d M') }} - {{ $end_date->format('d M Y') }}
          </span>
          <a href="{{ route('calendar.index', array_merge(request()->query(), ['week_start' => $next_week])) }}" class="gdfh-btn gdfh-btn-secondary text-xs px-2.5 py-1">
            الأسبوع التالي &larr;
          </a>
          @else
          <span class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
            جدول المواعيد القادمة
          </span>
          @endif
        </div>

        {{-- Filter Toggles & New Event Action --}}
        <div class="flex flex-wrap items-center gap-3">
          <form method="GET" action="{{ route('calendar.index') }}" class="flex flex-wrap items-center gap-3 text-xs">
            <input type="hidden" name="view" value="{{ $currentView }}">

            <select name="type" onchange="this.form.submit()" class="gdfh-btn text-xs py-1 px-2.5 bg-[rgb(var(--color-surface))] text-[rgb(var(--color-text-primary))]">
              <option value="all" {{ ($filters['type'] ?? 'all') === 'all' ? 'selected' : '' }}>جميع المصادر</option>
              <option value="project" {{ ($filters['type'] ?? '') === 'project' ? 'selected' : '' }}>المشاريع فقط</option>
              <option value="task" {{ ($filters['type'] ?? '') === 'task' ? 'selected' : '' }}>المهام فقط</option>
              <option value="custom" {{ ($filters['type'] ?? '') === 'custom' ? 'selected' : '' }}>الأحداث الخاصة فقط</option>
            </select>

            <label class="flex items-center gap-1.5 cursor-pointer">
              <input type="checkbox" name="assigned_to_me" value="1" onchange="this.form.submit()" {{ !empty($filters['assigned_to_me']) ? 'checked' : '' }} class="rounded border-gray-300 text-[rgb(var(--color-copper))] focus:ring-[rgb(var(--color-copper))]">
              <span class="text-[rgb(var(--color-text-primary))]">مهامي فقط</span>
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer">
              <input type="checkbox" name="overdue" value="1" onchange="this.form.submit()" {{ !empty($filters['overdue']) ? 'checked' : '' }} class="rounded border-gray-300 text-red-500 focus:ring-red-500">
              <span class="text-red-500 font-bold">المتأخرة فقط</span>
            </label>
          </form>

          <button type="button" @click="openCreateModal()" class="gdfh-btn gdfh-btn-brand text-xs py-1 px-3 flex items-center gap-1.5 shadow-sm">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span>حدث جديد</span>
          </button>
        </div>

      </div>

      {{-- VIEW 1: MONTH VIEW GRID --}}
      @if ($currentView === 'month')
      <div class="gdfh-card overflow-hidden">
        {{-- Weekday Headers --}}
        <div class="grid grid-cols-7 border-b border-[rgb(var(--color-border))] text-center text-xs font-bold text-[rgb(var(--color-text-secondary))] bg-[rgb(var(--color-surface-soft)/0.5)]">
          <div class="py-2.5">الأحد</div>
          <div class="py-2.5">الإثنين</div>
          <div class="py-2.5">الثلاثاء</div>
          <div class="py-2.5">الأربعاء</div>
          <div class="py-2.5">الخميس</div>
          <div class="py-2.5">الجمعة</div>
          <div class="py-2.5">السبت</div>
        </div>

        {{-- Days Grid --}}
        <div class="grid grid-cols-7 auto-rows-fr divide-x divide-y divide-[rgb(var(--color-border))] rtl:divide-x-reverse min-h-[500px]">
          @foreach ($days as $day)
          <div class="p-2 min-h-[100px] flex flex-col justify-between transition {{ $day['is_current_month'] ? 'bg-[rgb(var(--color-surface))]' : 'bg-[rgb(var(--color-surface-soft)/0.3)] text-gray-400' }} {{ $day['is_today'] ? 'ring-2 ring-inset ring-[rgb(var(--color-copper))]' : '' }}">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold {{ $day['is_today'] ? 'flex h-6 w-6 items-center justify-center rounded-full bg-[rgb(var(--color-copper))] text-white' : 'text-[rgb(var(--color-text-primary))]' }}">
                {{ $day['day_number'] }}
              </span>
              <div class="flex items-center gap-1">
                <button type="button" @click="openCreateModal('{{ $day['date'] }}')" title="إضافة حدث في هذا اليوم" class="text-gray-400 hover:text-[rgb(var(--color-copper))] text-xs font-bold">
                  +
                </button>
                @if ($day['events']->count() > 0)
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))]">
                  {{ $day['events']->count() }}
                </span>
                @endif
              </div>
            </div>

            {{-- Day Events Badges --}}
            <div class="mt-2 space-y-1 overflow-y-auto max-h-[80px]">
              @foreach ($day['events']->take(3) as $event)
              @if (!empty($event['is_editable']) && !empty($event['db_id']))
              <button type="button" @click="openEditModal({{ json_encode($event) }})" title="{{ $event['title'] }}" class="w-full text-start truncate rounded px-1.5 py-0.5 text-[10px] font-bold transition hover:opacity-80 block"
                style="{{ $event['color_category'] === 'blue' ? 'background-color: rgba(59, 130, 246, 0.15); color: #3b82f6;' : ($event['color_category'] === 'red' ? 'background-color: rgba(239, 68, 68, 0.15); color: #ef4444;' : ($event['color_category'] === 'emerald' ? 'background-color: rgba(16, 185, 129, 0.15); color: #10b981;' : 'background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));')) }}">
                📌 {{ $event['title'] }}
              </button>
              @else
              <a href="{{ $event['url'] }}" title="{{ $event['title'] }}" class="block truncate rounded px-1.5 py-0.5 text-[10px] font-bold transition hover:opacity-80"
                style="{{ $event['color_category'] === 'blue' ? 'background-color: rgba(59, 130, 246, 0.15); color: #3b82f6;' : ($event['color_category'] === 'red' ? 'background-color: rgba(239, 68, 68, 0.15); color: #ef4444;' : ($event['color_category'] === 'emerald' ? 'background-color: rgba(16, 185, 129, 0.15); color: #10b981;' : 'background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));')) }}">
                {{ $event['title'] }}
              </a>
              @endif
              @endforeach
              @if ($day['events']->count() > 3)
              <span class="text-[9px] text-[rgb(var(--color-text-secondary))] block text-center">+ {{ $day['events']->count() - 3 }} المزيد</span>
              @endif
            </div>
          </div>
          @endforeach
        </div>
      </div>
      @endif

      {{-- VIEW 2: WEEK VIEW --}}
      @if ($currentView === 'week')
      <div class="gdfh-card overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-7 divide-y md:divide-y-0 md:divide-x divide-[rgb(var(--color-border))] rtl:divide-x-reverse min-h-[450px]">
          @foreach ($days as $day)
          <div class="p-4 space-y-3 flex flex-col min-h-[150px] {{ $day['is_today'] ? 'bg-[rgb(var(--color-copper-soft)/0.15)]' : '' }}">
            <div class="border-b border-[rgb(var(--color-border))] pb-2 flex items-center justify-between">
              <div>
                <span class="block text-xs font-bold text-[rgb(var(--color-text-secondary))]">{{ $day['day_name'] }}</span>
                <span class="text-sm font-bold {{ $day['is_today'] ? 'text-[rgb(var(--color-copper))]' : 'text-[rgb(var(--color-text-primary))]' }}">{{ $day['day_number'] }}</span>
              </div>
              <button type="button" @click="openCreateModal('{{ $day['date'] }}')" title="إضافة حدث" class="text-xs font-bold text-gray-400 hover:text-[rgb(var(--color-copper))]">
                +
              </button>
            </div>

            <div class="space-y-2 flex-1">
              @forelse ($day['events'] as $event)
              <div class="p-2.5 rounded-lg border border-[rgb(var(--color-border))] space-y-1 transition hover:shadow-sm" style="background-color: rgb(var(--color-surface));">
                <div class="flex items-center justify-between">
                  <span class="text-[10px] font-bold px-1.5 py-0.5 rounded" style="{{ $event['color_category'] === 'red' ? 'background-color: rgba(239, 68, 68, 0.15); color: #ef4444;' : 'background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));' }}">
                    {{ $event['type'] === 'custom_event' ? 'حدث خاص' : $event['type'] }}
                  </span>
                  @if (!empty($event['is_editable']) && !empty($event['db_id']))
                  <button type="button" @click="openEditModal({{ json_encode($event) }})" class="text-[10px] text-[rgb(var(--color-copper))] font-bold hover:underline">تعديل</button>
                  @endif
                </div>

                @if (!empty($event['is_editable']) && !empty($event['db_id']))
                <button type="button" @click="openEditModal({{ json_encode($event) }})" class="block w-full text-start text-xs font-bold text-[rgb(var(--color-text-primary))] hover:text-[rgb(var(--color-copper))] truncate">
                  📌 {{ $event['title'] }}
                </button>
                @else
                <a href="{{ $event['url'] }}" class="block text-xs font-bold text-[rgb(var(--color-text-primary))] hover:text-[rgb(var(--color-copper))] truncate">
                  {{ $event['title'] }}
                </a>
                @endif
              </div>
              @empty
              <div class="text-[11px] text-[rgb(var(--color-text-secondary))] text-center py-4">لا توجد مواعيد</div>
              @endforelse
            </div>
          </div>
          @endforeach
        </div>
      </div>
      @endif

      {{-- VIEW 3: AGENDA VIEW --}}
      @if ($currentView === 'agenda')
      <div class="gdfh-card overflow-hidden">
        <div class="divide-y divide-[rgb(var(--color-border))]">
          @forelse ($agenda as $date => $dateEvents)
          <div class="p-5 space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-[rgb(var(--color-copper))]">{{ \Carbon\Carbon::parse($date)->locale('ar')->translatedFormat('l, d F Y') }}</span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))] font-bold">{{ $dateEvents->count() }} مواعيد</span>
              </div>
              <button type="button" @click="openCreateModal('{{ $date }}')" class="gdfh-btn gdfh-btn-secondary text-xs py-1 px-2.5">
                + إضافة حدث
              </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              @foreach ($dateEvents as $event)
              <div class="gdfh-card p-3 flex items-center justify-between gap-3">
                <div class="min-w-0 space-y-1">
                  <div class="flex items-center gap-2">
                    <span class="gdfh-badge text-[10px]" style="{{ $event['color_category'] === 'red' ? 'background-color: rgba(239, 68, 68, 0.15); color: #ef4444;' : 'background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));' }}">
                      {{ $event['type'] === 'custom_event' ? 'حدث خاص' : $event['type'] }}
                    </span>

                    @if (!empty($event['is_editable']) && !empty($event['db_id']))
                    <button type="button" @click="openEditModal({{ json_encode($event) }})" class="text-xs font-bold text-[rgb(var(--color-text-primary))] hover:text-[rgb(var(--color-copper))] truncate">
                      📌 {{ $event['title'] }}
                    </button>
                    @else
                    <a href="{{ $event['url'] }}" class="text-xs font-bold text-[rgb(var(--color-text-primary))] hover:text-[rgb(var(--color-copper))] truncate">
                      {{ $event['title'] }}
                    </a>
                    @endif
                  </div>

                  @if ($event['description'])
                  <p class="text-[11px] text-[rgb(var(--color-text-secondary))] line-clamp-1">
                    {{ $event['description'] }}
                  </p>
                  @endif

                  <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">
                    المشروع: {{ $event['related_project']?->title ?? 'عام' }}
                  </p>
                </div>

                @if (!empty($event['is_editable']) && !empty($event['db_id']))
                <button type="button" @click="openEditModal({{ json_encode($event) }})" class="gdfh-btn gdfh-btn-secondary text-xs py-1 px-2.5">
                  تعديل
                </button>
                @else
                <a href="{{ $event['url'] }}" class="gdfh-btn gdfh-btn-secondary text-xs py-1 px-2.5">عرض</a>
                @endif
              </div>
              @endforeach
            </div>
          </div>
          @empty
          <div class="p-12 text-center text-xs text-[rgb(var(--color-text-secondary))]">لا توجد مواعيد مضافة في الأجندة حالياً.</div>
          @endforelse
        </div>
      </div>
      @endif

    </div>

    {{-- Create / Edit Event Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
        <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showModal = false"></div>

        <div x-show="showModal" x-transition class="relative transform overflow-hidden rounded-2xl bg-[rgb(var(--color-surface))] border border-[rgb(var(--color-border))] text-start shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg p-6 space-y-4">
          
          <div class="flex items-center justify-between border-b border-[rgb(var(--color-border))] pb-3">
            <h3 class="text-base font-bold text-[rgb(var(--color-text-primary))]" x-text="isEditing ? 'تعديل الحدث' : 'إضافة حدث جديد إلى التقويم'"></h3>
            <button type="button" @click="showModal = false" class="text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <form method="POST" :action="isEditing ? `/calendar/events/${eventForm.id}` : '{{ route('calendar.events.store') }}'" class="space-y-4 text-xs">
            @csrf
            <template x-if="isEditing">
              <input type="hidden" name="_method" value="PUT">
            </template>

            <div>
              <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">عنوان الحدث *</label>
              <input type="text" name="title" x-model="eventForm.title" required placeholder="مثال: اجتماع فريق التطوير أو مراجعة التصاميم..." class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:border-[rgb(var(--color-copper))] focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">تاريخ ووقت البداية *</label>
                <input type="datetime-local" name="start_at" x-model="eventForm.start_at" required class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2 text-xs text-[rgb(var(--color-text-primary))]">
              </div>

              <div>
                <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">تاريخ ووقت النهاية</label>
                <input type="datetime-local" name="end_at" x-model="eventForm.end_at" class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2 text-xs text-[rgb(var(--color-text-primary))]">
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">ربط بمشروع (اختياري)</label>
                <select name="project_id" x-model="eventForm.project_id" class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2 text-xs text-[rgb(var(--color-text-primary))]">
                  <option value="">حدث عام (غير مرتبط بمشروع)</option>
                  @foreach ($user_projects as $proj)
                  <option value="{{ $proj->id }}">{{ $proj->title }}</option>
                  @endforeach
                </select>
              </div>

              <div>
                <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">اللون / التصنيف</label>
                <select name="color" x-model="eventForm.color" class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2 text-xs text-[rgb(var(--color-text-primary))]">
                  <option value="copper">برتقالي (Tasker Copper)</option>
                  <option value="blue">أزرق (Blue)</option>
                  <option value="emerald">أخضر (Emerald)</option>
                  <option value="purple">بنفسجي (Purple)</option>
                  <option value="amber">أصفر (Amber)</option>
                  <option value="red">أحمر (Red)</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">المكان / الرابط (اختياري)</label>
              <input type="text" name="location" x-model="eventForm.location" placeholder="مثال: قاعة الاجتماعات الرئيسية أو رابط Google Meet..." class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2 text-xs text-[rgb(var(--color-text-primary))]">
            </div>

            <div>
              <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">وصف الحدث</label>
              <textarea name="description" x-model="eventForm.description" rows="2" placeholder="أضف تفاصيل إضافية أو أجندة الاجتماع..." class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2 text-xs text-[rgb(var(--color-text-primary))]"></textarea>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-[rgb(var(--color-border))]">
              <div>
                <template x-if="isEditing">
                  <button type="button" @click="if(confirm('هل أنت تأكد من رغبتك في حذف هذا الحدث؟')) { $refs.deleteForm.submit(); }" class="text-xs font-bold text-red-500 hover:underline">حذف الحدث</button>
                </template>
              </div>

              <div class="flex items-center gap-2">
                <button type="button" @click="showModal = false" class="gdfh-btn gdfh-btn-secondary py-1.5 px-3">إلغاء</button>
                <button type="submit" class="gdfh-btn gdfh-btn-brand py-1.5 px-4 font-bold" x-text="isEditing ? 'حفظ التعديلات' : 'إضافة الحدث'"></button>
              </div>
            </div>

          </form>

          <template x-if="isEditing">
            <form x-ref="deleteForm" method="POST" :action="`/calendar/events/${eventForm.id}`" class="hidden">
              @csrf
              <input type="hidden" name="_method" value="DELETE">
            </form>
          </template>

        </div>
      </div>
    </div>

  </div>
</x-app-layout>
