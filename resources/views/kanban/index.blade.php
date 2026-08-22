<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[rgb(var(--color-text-primary))]">
          لوحة المهام والتنفيذ (Enterprise Kanban Board)
        </h2>
        <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">
          إدارة وتنظيم وتتبع سريان جميع مهام بيئة العمل عبر الأعمدة التفاعلية.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <span class="gdfh-badge text-xs font-bold" style="background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));">
          إجمالي المهام: {{ $total_count }}
        </span>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-6"
       x-data="{
         isMoving: false,
         async moveTask(taskId, targetStatus) {
           if (this.isMoving) return;
           this.isMoving = true;
           const csrfToken = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
           try {
             const res = await fetch(`/kanban/tasks/${taskId}/status`, {
               method: 'POST',
               headers: {
                 'Content-Type': 'application/json',
                 'X-CSRF-TOKEN': csrfToken,
                 'Accept': 'application/json'
               },
               body: JSON.stringify({ status: targetStatus })
             });
             const data = await res.json();
             if (res.ok && data.success) {
               window.location.reload();
             } else {
               alert(data.message || 'حدث خطأ أثناء نقل المهمة');
             }
           } catch (e) {
             console.error('Kanban Drag Drop Error:', e);
           } finally {
             this.isMoving = false;
           }
         }
       }">
    <div class="mx-auto max-w-7xl space-y-6">

      {{-- Search & Filter Toolbar --}}
      <form method="GET" action="{{ route('kanban.index') }}" class="gdfh-card p-4 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        
        {{-- Search Input --}}
        <div class="relative flex-1 max-w-md">
          <svg class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[rgb(var(--color-text-secondary))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="ابحث باسم المهمة أو الوصف..." class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] ps-9 pe-4 py-2 text-xs text-[rgb(var(--color-text-primary))] placeholder:text-[rgb(var(--color-text-secondary))] focus:border-[rgb(var(--color-copper))] focus:outline-none focus:ring-1 focus:ring-[rgb(var(--color-copper))]">
        </div>

        {{-- Dropdown Filters --}}
        <div class="flex flex-wrap items-center gap-3 text-xs">
          {{-- Project Filter --}}
          <select name="project_id" onchange="this.form.submit()" class="gdfh-btn text-xs py-1.5 px-3 bg-[rgb(var(--color-surface))] text-[rgb(var(--color-text-primary))]">
            <option value="">جميع المشاريع</option>
            @foreach ($user_projects as $proj)
            <option value="{{ $proj->id }}" {{ ($filters['project_id'] ?? '') == $proj->id ? 'selected' : '' }}>{{ $proj->title }}</option>
            @endforeach
          </select>

          {{-- Priority Filter --}}
          <select name="priority" onchange="this.form.submit()" class="gdfh-btn text-xs py-1.5 px-3 bg-[rgb(var(--color-surface))] text-[rgb(var(--color-text-primary))]">
            <option value="">جميع الأولويات</option>
            <option value="urgent" {{ ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' }}>عاجل</option>
            <option value="high" {{ ($filters['priority'] ?? '') === 'high' ? 'selected' : '' }}>عالية</option>
            <option value="medium" {{ ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' }}>متوسطة</option>
            <option value="low" {{ ($filters['priority'] ?? '') === 'low' ? 'selected' : '' }}>منخفضة</option>
          </select>

          {{-- Overdue Checkbox --}}
          <label class="flex items-center gap-1.5 cursor-pointer">
            <input type="checkbox" name="overdue" value="1" onchange="this.form.submit()" {{ !empty($filters['overdue']) ? 'checked' : '' }} class="rounded border-gray-300 text-red-500 focus:ring-red-500">
            <span class="text-red-500 font-bold">المتأخرة فقط</span>
          </label>

          @if (!empty($filters['search']) || !empty($filters['project_id']) || !empty($filters['priority']) || !empty($filters['overdue']))
          <a href="{{ route('kanban.index') }}" class="text-xs font-bold text-[rgb(var(--color-copper))] hover:underline">إعادة ضبط</a>
          @endif
        </div>
      </form>

      {{-- Kanban Columns Grid (4 Columns) --}}
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-start">
        @foreach ($columns as $column)
        <div class="gdfh-card flex flex-col max-h-[800px] overflow-hidden">
          
          {{-- Column Header --}}
          <div class="border-b border-[rgb(var(--color-border))] p-4 flex items-center justify-between bg-[rgb(var(--color-surface-soft)/0.5)]">
            <div class="flex items-center gap-2">
              <span class="h-2.5 w-2.5 rounded-full {{ $column['color'] === 'blue' ? 'bg-blue-500' : ($column['color'] === 'amber' ? 'bg-amber-500' : ($column['color'] === 'emerald' ? 'bg-emerald-500' : 'bg-gray-400')) }}"></span>
              <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">{{ $column['title'] }}</h3>
            </div>
            <span class="flex h-5 px-2 items-center justify-center rounded-full text-[11px] font-bold text-[rgb(var(--color-text-secondary))] bg-[rgb(var(--color-surface-soft))]">
              {{ $column['count'] }}
            </span>
          </div>

          {{-- Column Task List (Drop Target) --}}
          <div class="p-3 space-y-3 overflow-y-auto min-h-[300px] transition-colors rounded-b-xl"
               x-on:dragover.prevent="$el.classList.add('bg-blue-500/10')"
               x-on:dragleave="$el.classList.remove('bg-blue-500/10')"
               x-on:drop.prevent="
                 $el.classList.remove('bg-blue-500/10');
                 const taskId = event.dataTransfer.getData('text/plain');
                 if (taskId) moveTask(taskId, '{{ $column['key'] }}');
               ">
            @forelse ($column['tasks'] as $task)
            <div draggable="true"
                 x-on:dragstart="event.dataTransfer.setData('text/plain', '{{ $task->id }}'); event.dataTransfer.effectAllowed = 'move';"
                 class="p-4 rounded-xl border border-[rgb(var(--color-border))] space-y-3 bg-[rgb(var(--color-surface))] transition hover:shadow-md cursor-grab active:cursor-grabbing">
              
              {{-- Priority & Project Tag --}}
              <div class="flex items-center justify-between gap-2">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded truncate max-w-[140px] bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))]">
                  {{ $task->project?->title }}
                </span>

                <span class="gdfh-badge text-[9px] {{ $task->priority === 'urgent' ? 'bg-red-500/10 text-red-500 font-bold' : ($task->priority === 'high' ? 'bg-amber-500/10 text-amber-500 font-bold' : 'bg-gray-500/10 text-gray-500') }}">
                  {{ $task->priority }}
                </span>
              </div>

              {{-- Task Title & Description --}}
              <div>
                <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}" class="text-xs font-bold text-[rgb(var(--color-text-primary))] hover:text-[rgb(var(--color-copper))] block leading-5">
                  {{ $task->title }}
                </a>
                @if ($task->description)
                <p class="mt-1 text-[11px] text-[rgb(var(--color-text-secondary))] line-clamp-2 leading-4">
                  {{ $task->description }}
                </p>
                @endif
              </div>

              {{-- Assignee & Meta Details --}}
              <div class="pt-2 border-t border-[rgb(var(--color-border))] flex items-center justify-between text-[11px] text-[rgb(var(--color-text-secondary))]">
                <div class="flex items-center gap-1.5 truncate">
                  <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[rgb(var(--color-copper-soft))] text-[9px] font-bold text-[rgb(var(--color-copper))]">
                    {{ mb_substr($task->assignee?->name ?? 'غ', 0, 1) }}
                  </span>
                  <span class="truncate">{{ $task->assignee?->name ?? 'غير مُسند' }}</span>
                </div>

                {{-- Counts & Status Action --}}
                <div class="flex items-center gap-2 shrink-0">
                  @if ($task->comments_count > 0)
                  <span class="flex items-center gap-1"><svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.008v.008H8.625V12zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.008v.008h.008V12zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.008v.008h.008V12c0 3.728-4.03 6.75-9 6.75a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-3.728 4.03-6.75 9-6.75s9 3.022 9 6.75z"/></svg>{{ $task->comments_count }}</span>
                  @endif
                  @if ($task->attachments_count > 0)
                  <span class="flex items-center gap-1"><svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.55 18.32a1.5 1.5 0 01-2.121-2.121l9.9-9.9"/></svg>{{ $task->attachments_count }}</span>
                  @endif
                </div>
              </div>

              {{-- Quick Status Move Controls --}}
              <form method="POST" action="{{ route('kanban.tasks.update-status', $task) }}" class="pt-2">
                @csrf
                <select name="status" onchange="this.form.submit()" class="w-full text-[10px] py-1 px-2 rounded-lg border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))] focus:outline-none">
                  <option value="todo" {{ in_array($task->status, ['todo', 'pending'], true) ? 'selected' : '' }}>نقل إلى: قيد الانتظار</option>
                  <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>نقل إلى: قيد التنفيذ</option>
                  <option value="review" {{ in_array($task->status, ['review', 'in_review'], true) ? 'selected' : '' }}>نقل إلى: قيد المراجعة</option>
                  <option value="done" {{ in_array($task->status, ['done', 'completed'], true) ? 'selected' : '' }}>نقل إلى: مكتملة</option>
                </select>
              </form>

            </div>
            @empty
            <div class="p-8 text-center text-xs text-[rgb(var(--color-text-secondary))] border border-dashed border-[rgb(var(--color-border))] rounded-xl">
              لا توجد مهام في هذا العمود.
            </div>
            @endforelse
          </div>

        </div>
        @endforeach
      </div>

    </div>
  </div>
</x-app-layout>
