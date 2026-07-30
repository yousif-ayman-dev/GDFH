<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="min-w-0">
        <div class="flex items-center gap-2 text-xs font-medium text-[rgb(var(--color-text-secondary))]">
          <a href="{{ route('projects.index') }}" class="transition hover:text-[rgb(var(--color-copper))]">المشاريع</a>
          <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 18l-6-6 6-6" /></svg>
          <a href="{{ route('projects.show', $project) }}" class="transition hover:text-[rgb(var(--color-copper))]">{{ $project->title }}</a>
          <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 18l-6-6 6-6" /></svg>
          <a href="{{ route('projects.tasks.index', $project) }}" class="transition hover:text-[rgb(var(--color-copper))]">المهام</a>
          <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 18l-6-6 6-6" /></svg>
          <span class="truncate text-[rgb(var(--color-text-primary))]">تعديل مهمة</span>
        </div>

        <h2 class="mt-1 truncate text-xl font-bold text-[rgb(var(--color-text-primary))]">
          تعديل المهمة: {{ $task->title }}
        </h2>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="gdfh-btn gdfh-btn-secondary">
          إلغاء
        </a>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
    <div class="mx-auto max-w-3xl">
      @if ($errors->any())
      <div class="mb-6 flex items-start gap-3 rounded-xl border border-[rgb(var(--color-error)/0.30)] bg-[rgb(var(--color-error)/0.08)] p-4">
        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[rgb(var(--color-error)/0.12)] text-[rgb(var(--color-error))]">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M12 9v4m0 4h.01M10.3 3.8L2.6 17.1A2 2 0 004.3 20h15.4a2 2 0 001.7-2.9L13.7 3.8a2 2 0 00-3.4 0z" />
          </svg>
        </div>
        <div>
          <p class="text-sm font-bold text-[rgb(var(--color-text-primary))]">يرجى مراجعة الأخطاء التالية:</p>
          <ul class="mt-2 space-y-1 text-xs text-[rgb(var(--color-error))]">
            @foreach ($errors->all() as $error)
            <li>• {{ $error }}</li>
            @endforeach
          </ul>
        </div>
      </div>
      @endif

      <form method="POST" action="{{ route('projects.tasks.update', [$project, $task]) }}" class="gdfh-card p-6 sm:p-8 space-y-6">
        @csrf
        @method('PATCH')

        <div>
          <label for="title" class="mb-2 block text-xs font-semibold text-[rgb(var(--color-text-primary))]">
            عنوان المهمة <span class="text-red-500">*</span>
          </label>
          <input id="title" name="title" type="text" value="{{ old('title', $task->title) }}" required class="gdfh-input">
          @error('title')
          <p class="mt-1 text-xs text-[rgb(var(--color-error))]">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="description" class="mb-2 block text-xs font-semibold text-[rgb(var(--color-text-primary))]">
            الوصف (اختياري)
          </label>
          <textarea id="description" name="description" rows="4" class="gdfh-input">{{ old('description', $task->description) }}</textarea>
          @error('description')
          <p class="mt-1 text-xs text-[rgb(var(--color-error))]">{{ $message }}</p>
          @enderror
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
          <div>
            <label for="status" class="mb-2 block text-xs font-semibold text-[rgb(var(--color-text-primary))]">
              الحالة
            </label>
            <select id="status" name="status" class="gdfh-input">
              <option value="todo" @selected(old('status', $task->status) === 'todo')>قيد الانتظار</option>
              <option value="in_progress" @selected(old('status', $task->status) === 'in_progress')>قيد التنفيذ</option>
              <option value="in_review" @selected(old('status', $task->status) === 'in_review')>قيد المراجعة</option>
              <option value="completed" @selected(old('status', $task->status) === 'completed')>مكتمل</option>
              <option value="cancelled" @selected(old('status', $task->status) === 'cancelled')>ملغي</option>
            </select>
          </div>

          <div>
            <label for="priority" class="mb-2 block text-xs font-semibold text-[rgb(var(--color-text-primary))]">
              الأولوية
            </label>
            <select id="priority" name="priority" class="gdfh-input">
              <option value="low" @selected(old('priority', $task->priority) === 'low')>منخفضة</option>
              <option value="medium" @selected(old('priority', $task->priority) === 'medium')>متوسطة</option>
              <option value="high" @selected(old('priority', $task->priority) === 'high')>عالية</option>
              <option value="urgent" @selected(old('priority', $task->priority) === 'urgent')>عاجلة</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
          <div>
            <label for="team_id" class="mb-2 block text-xs font-semibold text-[rgb(var(--color-text-primary))]">
              الفريق المرتبط (اختياري)
            </label>
            <select id="team_id" name="team_id" class="gdfh-input">
              <option value="">بدون فريق</option>
              @foreach ($project->teams as $team)
              <option value="{{ $team->id }}" @selected(old('team_id', $task->team_id) == $team->id)>
                {{ $team->name }}
              </option>
              @endforeach
            </select>
            @error('team_id')
            <p class="mt-1 text-xs text-[rgb(var(--color-error))]">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="due_at" class="mb-2 block text-xs font-semibold text-[rgb(var(--color-text-primary))]">
              تاريخ الاستحقاق (اختياري)
            </label>
            <input id="due_at" name="due_at" type="date" value="{{ old('due_at', $task->due_at ? $task->due_at->format('Y-m-d') : '') }}" class="gdfh-input">
            @error('due_at')
            <p class="mt-1 text-xs text-[rgb(var(--color-error))]">{{ $message }}</p>
            @enderror
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-[rgb(var(--color-border))] pt-6">
          <a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="gdfh-btn gdfh-btn-secondary">
            إلغاء
          </a>
          <button type="submit" class="gdfh-btn gdfh-btn-brand">
            حفظ التغييرات
          </button>
        </div>
      </form>
    </div>
  </div>
</x-app-layout>
