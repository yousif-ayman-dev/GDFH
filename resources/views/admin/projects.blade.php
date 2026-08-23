<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <p class="text-xs font-semibold text-[rgb(var(--color-copper))]">لوحة الإدارة العليا</p>
        <h2 class="mt-1 text-xl font-bold text-[rgb(var(--color-text-primary))]">إدارة مشاريع النظام</h2>
      </div>
      <a href="{{ route('admin.index') }}" class="gdfh-btn gdfh-btn-secondary text-xs px-3 py-1.5">← لوحة المشرف</a>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-6">
    <div class="mx-auto max-w-7xl space-y-6">

      {{-- Search/Filter Bar --}}
      <form method="GET" action="{{ route('admin.projects') }}" class="gdfh-card p-4 flex flex-wrap items-center gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
          placeholder="ابحث باسم المشروع..."
          class="gdfh-input flex-1 min-w-48">

        <select name="status" class="gdfh-input w-44">
          <option value="">جميع الحالات</option>
          <option value="draft" @selected(request('status') === 'draft')>مسودة</option>
          <option value="in_progress" @selected(request('status') === 'in_progress')>قيد التنفيذ</option>
          <option value="on_hold" @selected(request('status') === 'on_hold')>متوقف</option>
          <option value="completed" @selected(request('status') === 'completed')>مكتمل</option>
          <option value="cancelled" @selected(request('status') === 'cancelled')>ملغي</option>
        </select>

        <button type="submit" class="gdfh-btn gdfh-btn-brand text-xs px-4">بحث</button>
        @if (request()->hasAny(['search', 'status']))
        <a href="{{ route('admin.projects') }}" class="gdfh-btn gdfh-btn-secondary text-xs px-3">مسح</a>
        @endif
      </form>

      {{-- Projects Table --}}
      <div class="gdfh-card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-[rgb(var(--color-border))]">
          <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
            جميع المشاريع
            <span class="ms-2 text-xs font-normal text-[rgb(var(--color-text-secondary))]">({{ $projects->total() }} مشروع)</span>
          </h3>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-xs" id="admin-projects-table">
            <thead>
              <tr class="border-b border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface-soft))]">
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">#</th>
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">المشروع</th>
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">المالك</th>
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">الحالة</th>
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">الرؤية</th>
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">تاريخ الإنشاء</th>
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">عرض</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[rgb(var(--color-border)/0.5)]">
              @forelse ($projects as $project)
              @php
              $statusLabels = [
                'draft' => ['label' => 'مسودة', 'class' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'],
                'in_progress' => ['label' => 'قيد التنفيذ', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
                'on_hold' => ['label' => 'متوقف', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'],
                'completed' => ['label' => 'مكتمل', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'],
                'cancelled' => ['label' => 'ملغي', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'],
              ];
              $statusInfo = $statusLabels[$project->status] ?? ['label' => $project->status, 'class' => 'bg-slate-100 text-slate-600'];
              @endphp
              <tr class="hover:bg-[rgb(var(--color-surface-soft))] transition">
                <td class="px-4 py-3 text-[rgb(var(--color-text-secondary))]">{{ $project->id }}</td>
                <td class="px-4 py-3">
                  <div class="font-semibold text-[rgb(var(--color-text-primary))]">{{ $project->title }}</div>
                  <div class="text-[10px] text-[rgb(var(--color-text-secondary))]">{{ $project->slug }}</div>
                </td>
                <td class="px-4 py-3 text-[rgb(var(--color-text-secondary))]">{{ $project->owner?->name ?? '—' }}</td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $statusInfo['class'] }}">
                    {{ $statusInfo['label'] }}
                  </span>
                </td>
                <td class="px-4 py-3 text-[rgb(var(--color-text-secondary))]">
                  {{ $project->visibility === 'public' ? 'عام' : 'خاص' }}
                </td>
                <td class="px-4 py-3 text-[rgb(var(--color-text-secondary))]">{{ $project->created_at->format('Y-m-d') }}</td>
                <td class="px-4 py-3">
                  <a href="{{ route('projects.show', $project) }}"
                    class="text-[10px] font-semibold px-2 py-1 rounded-lg border border-[rgb(var(--color-border))] text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-copper))] hover:border-[rgb(var(--color-copper)/0.4)] transition"
                    target="_blank">
                    عرض
                  </a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="px-4 py-10 text-center text-[rgb(var(--color-text-secondary))]">لا توجد نتائج مطابقة.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if ($projects->hasPages())
        <div class="px-5 py-4 border-t border-[rgb(var(--color-border))]">
          {{ $projects->links() }}
        </div>
        @endif
      </div>

    </div>
  </div>
</x-app-layout>
