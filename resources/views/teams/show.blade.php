<x-app-layout>
  @php
  $typeLabels = [
  'permanent' => 'دائم',
  'project_based' => 'قائم على مشروع',
  ];

  $visibilityLabels = [
  'private' => 'خاص',
  'public' => 'عام',
  ];

  $roleLabels = [
  'owner' => 'مالك',
  'admin' => 'مدير',
  'member' => 'عضو',
  'viewer' => 'مشاهد',
  ];

  $statusLabels = [
  'active' => 'نشط',
  'pending' => 'قيد الانتظار',
  'suspended' => 'موقوف',
  'left' => 'غادر',
  ];

  $taskStatusLabels = [
  'todo' => 'قيد الانتظار',
  'in_progress' => 'قيد التنفيذ',
  'in_review' => 'قيد المراجعة',
  'completed' => 'مكتمل',
  'cancelled' => 'ملغي',
  ];

  $taskPriorityLabels = [
  'low' => 'منخفضة',
  'medium' => 'متوسطة',
  'high' => 'عالية',
  'urgent' => 'عاجلة',
  ];

  $membersCount = $team->memberships->count();
  $projectsCount = $team->projects->count();
  $totalTasksCount = $team->tasks->count();
  $openTasksCount = $team->tasks->whereIn('status', ['todo', 'in_progress', 'in_review'])->count();
  $completedTasksCount = $team->tasks->where('status', 'completed')->count();
  $overdueTasksCount = $team->tasks->filter(fn($t) => $t->status !== 'completed' && $t->due_at && $t->due_at->isPast())->count();
  @endphp

  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="min-w-0">
        <div class="flex items-center gap-2 text-xs font-medium text-[rgb(var(--color-text-secondary))]">
          <a href="{{ route('teams.index') }}" class="transition hover:text-[rgb(var(--color-copper))]">
            الفرق
          </a>

          <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6" />
          </svg>

          <span class="truncate text-[rgb(var(--color-text-primary))]">
            {{ $team->name }}
          </span>
        </div>

        <h2 class="mt-1 truncate text-xl font-bold text-[rgb(var(--color-text-primary))]">
          {{ $team->name }}
        </h2>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('teams.index') }}" class="gdfh-btn gdfh-btn-secondary">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6" />
          </svg>

          <span>الفرق</span>
        </a>

        <a href="{{ route('teams.edit', $team) }}" class="gdfh-btn gdfh-btn-brand">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M13.5 6.5l4 4M4 20l4.5-1 10-10a2.8 2.8 0 00-4-4l-10 10L4 20z" />
          </svg>

          تعديل الفريق
        </a>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
    <div class="mx-auto max-w-7xl space-y-8">
      @if (session('success'))
      <div class="flex items-start gap-3 rounded-xl border border-[rgb(var(--color-success)/0.25)] bg-[rgb(var(--color-success)/0.08)] px-4 py-3" role="alert">
        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[rgb(var(--color-success)/0.12)] text-[rgb(var(--color-success))]">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </div>

        <div class="min-w-0">
          <p class="text-sm font-semibold text-[rgb(var(--color-text-primary))]">
            تمت العملية بنجاح
          </p>

          <p class="mt-0.5 text-xs leading-6 text-[rgb(var(--color-text-secondary))]">
            {{ session('success') }}
          </p>
        </div>
      </div>
      @endif

      @if ($errors->any())
      <div class="flex items-start gap-3 rounded-xl border border-[rgb(var(--color-error)/0.30)] bg-[rgb(var(--color-error)/0.08)] p-4">
        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[rgb(var(--color-error)/0.12)] text-[rgb(var(--color-error))]">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 9v4m0 4h.01M10.3 3.8L2.6 17.1A2 2 0 004.3 20h15.4a2 2 0 001.7-2.9L13.7 3.8a2 2 0 00-3.4 0z" />
          </svg>
        </div>

        <div>
          <p class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
            يوجد بعض الحقول التي تحتاج إلى مراجعة
          </p>

          <ul class="mt-2 space-y-1 text-sm text-[rgb(var(--color-error))]">
            @foreach ($errors->all() as $error)
            <li>• {{ $error }}</li>
            @endforeach
          </ul>
        </div>
      </div>
      @endif

      {{-- Header Banner Card --}}
      <section class="gdfh-card relative overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-1" style="background: linear-gradient(90deg, rgb(var(--color-copper)), rgb(var(--color-mineral)));"></div>

        <div class="p-5 sm:p-7 lg:p-8">
          <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center gap-2">
                <span class="gdfh-badge bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))]">
                  {{ $typeLabels[$team->type] ?? ucfirst($team->type) }}
                </span>

                <span class="gdfh-badge bg-[rgb(var(--color-mineral-soft))] text-[rgb(var(--color-mineral))]">
                  {{ $visibilityLabels[$team->visibility] ?? ucfirst($team->visibility) }}
                </span>
              </div>

              <h1 class="mt-5 break-words text-2xl font-bold tracking-tight text-[rgb(var(--color-text-primary))] sm:text-3xl lg:text-4xl">
                {{ $team->name }}
              </h1>

              <p class="mt-4 max-w-3xl whitespace-pre-line text-sm leading-8 text-[rgb(var(--color-text-secondary))] sm:text-base">
                {{ $team->description ?: 'لا يوجد وصف إضافي لهذا الفريق بعد.' }}
              </p>
            </div>

            <div class="w-full shrink-0 rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface-soft))] p-4 lg:w-72">
              <div class="mb-4 flex items-center justify-center">
                @if ($team->logo_path)
                <img src="{{ Storage::disk('public')->url($team->logo_path) }}" alt="{{ $team->name }}" class="h-24 w-24 rounded-2xl object-cover ring-2 ring-[rgb(var(--color-copper-soft))]">
                @else
                <div class="flex h-24 w-24 items-center justify-center rounded-2xl bg-[rgb(var(--color-copper-soft))] text-2xl font-bold text-[rgb(var(--color-copper))] ring-2 ring-[rgb(var(--color-copper-soft))]">
                  {{ mb_strtoupper(mb_substr($team->name, 0, 1)) }}
                </div>
                @endif
              </div>

              <p class="text-xs font-semibold text-[rgb(var(--color-text-secondary))]">
                مالك الفريق
              </p>

              <div class="mt-3 flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[rgb(var(--color-copper-soft))] text-sm font-bold text-[rgb(var(--color-copper))]">
                  {{ mb_strtoupper(mb_substr($team->owner?->name ?? 'ف', 0, 1)) }}
                </div>

                <div class="min-w-0">
                  <p class="truncate text-sm font-bold text-[rgb(var(--color-text-primary))]">
                    {{ $team->owner?->name ?? 'غير متاح' }}
                  </p>

                  <p dir="ltr" class="mt-0.5 truncate text-left text-xs text-[rgb(var(--color-text-secondary))]">
                    {{ $team->owner?->email ?? '—' }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {{-- Metrics Section --}}
      <section class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <div class="gdfh-card p-4 text-center">
          <span class="block text-xs font-medium text-[rgb(var(--color-text-secondary))]">الأعضاء</span>
          <span class="mt-1 block text-2xl font-bold text-[rgb(var(--color-text-primary))]">{{ $membersCount }}</span>
        </div>

        <div class="gdfh-card p-4 text-center">
          <span class="block text-xs font-medium text-[rgb(var(--color-text-secondary))]">المشاريع المرتبطة</span>
          <span class="mt-1 block text-2xl font-bold text-[rgb(var(--color-text-primary))]">{{ $projectsCount }}</span>
        </div>

        <div class="gdfh-card p-4 text-center">
          <span class="block text-xs font-medium text-[rgb(var(--color-text-secondary))]">إجمالي المهام</span>
          <span class="mt-1 block text-2xl font-bold text-[rgb(var(--color-text-primary))]">{{ $totalTasksCount }}</span>
        </div>

        <div class="gdfh-card p-4 text-center">
          <span class="block text-xs font-medium text-[rgb(var(--color-text-secondary))]">المهام المفتوحة</span>
          <span class="mt-1 block text-2xl font-bold text-[rgb(var(--color-copper))]">{{ $openTasksCount }}</span>
        </div>

        <div class="gdfh-card p-4 text-center">
          <span class="block text-xs font-medium text-[rgb(var(--color-text-secondary))]">المهام المكتملة</span>
          <span class="mt-1 block text-2xl font-bold text-[rgb(var(--color-success))]">{{ $completedTasksCount }}</span>
        </div>

        <div class="gdfh-card p-4 text-center">
          <span class="block text-xs font-medium text-[rgb(var(--color-text-secondary))]">المهام المتأخرة</span>
          <span class="mt-1 block text-2xl font-bold text-[rgb(var(--color-error))]">{{ $overdueTasksCount }}</span>
        </div>
      </section>

      {{-- Main Grid Content --}}
      <div class="grid gap-8 lg:grid-cols-3">

        {{-- Left 2 Columns: Projects & Tasks --}}
        <div class="space-y-8 lg:col-span-2">

          {{-- Linked Projects Section --}}
          <section class="gdfh-card overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-[rgb(var(--color-border))] p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
              <div>
                <h2 class="text-base font-bold text-[rgb(var(--color-text-primary))]">
                  المشاريع المرتبطة بالفريق
                </h2>
                <p class="mt-0.5 text-xs text-[rgb(var(--color-text-secondary))]">
                  المشاريع التي ينتمي إليها هذا الفريق ومستودع المهام.
                </p>
              </div>

              @if (isset($availableProjects) && $availableProjects->isNotEmpty())
              <form method="POST" action="" class="flex items-center gap-2" id="attachProjectForm">
                @csrf
                <select id="project_select" class="gdfh-input py-1.5 text-xs min-w-[180px]">
                  <option value="">اختر مشروعًا لربطه...</option>
                  @foreach ($availableProjects as $availableProject)
                  <option value="{{ route('teams.projects.attach', [$team, $availableProject]) }}">
                    {{ $availableProject->title }}
                  </option>
                  @endforeach
                </select>
                <button type="button" onclick="if(document.getElementById('project_select').value){ var f=document.getElementById('attachProjectForm'); f.action=document.getElementById('project_select').value; f.submit(); }" class="gdfh-btn gdfh-btn-brand py-1.5 text-xs">
                  ربط المشروع
                </button>
              </form>
              @endif
            </div>

            <div class="divide-y divide-[rgb(var(--color-border))]">
              @forelse ($team->projects as $project)
              <div class="flex items-center justify-between p-5 transition hover:bg-[rgb(var(--color-surface-soft)/0.4)]">
                <div class="min-w-0">
                  <a href="{{ route('projects.show', $project) }}" class="text-sm font-bold text-[rgb(var(--color-text-primary))] transition hover:text-[rgb(var(--color-copper))]">
                    {{ $project->title }}
                  </a>
                  <p class="mt-1 line-clamp-1 text-xs text-[rgb(var(--color-text-secondary))]">
                    {{ $project->description ?: 'لا يوجد وصف للمشروع.' }}
                  </p>
                </div>

                <div class="flex items-center gap-3">
                  <a href="{{ route('projects.show', $project) }}" class="gdfh-btn gdfh-btn-secondary py-1 text-xs">
                    عرض
                  </a>

                  <form method="POST" action="{{ route('teams.projects.detach', [$team, $project]) }}" onsubmit="return confirm('هل تريد إزالة ربط هذا المشروع بالفريق؟')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="gdfh-btn py-1 text-xs text-[rgb(var(--color-error))] hover:bg-[rgb(var(--color-error)/0.1)]">
                      إلغاء الربط
                    </button>
                  </form>
                </div>
              </div>
              @empty
              <div class="p-8 text-center text-xs text-[rgb(var(--color-text-secondary))]">
                لا يوجد مشاريع مرتبطة بهذا الفريق حاليًا.
              </div>
              @endforelse
            </div>
          </section>

          {{-- Team Tasks Section --}}
          <section class="gdfh-card overflow-hidden">
            <div class="border-b border-[rgb(var(--color-border))] p-5 sm:p-6">
              <h2 class="text-base font-bold text-[rgb(var(--color-text-primary))]">
                مهام الفريق
              </h2>
              <p class="mt-0.5 text-xs text-[rgb(var(--color-text-secondary))]">
                أحدث المهام المسندة إلى هذا الفريق عبر مختلف المشاريع المرتبطة.
              </p>
            </div>

            <div class="divide-y divide-[rgb(var(--color-border))]">
              @forelse ($team->tasks as $task)
              <div class="flex flex-col gap-3 p-5 transition hover:bg-[rgb(var(--color-surface-soft)/0.4)] sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0 flex-1">
                  <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}" class="text-sm font-bold text-[rgb(var(--color-text-primary))] transition hover:text-[rgb(var(--color-copper))]">
                      {{ $task->title }}
                    </a>

                    <span class="gdfh-badge bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))]">
                      {{ $taskStatusLabels[$task->status] ?? $task->status }}
                    </span>

                    @if ($task->project)
                    <span class="gdfh-badge bg-[rgb(var(--color-mineral-soft))] text-[rgb(var(--color-mineral))]">
                      {{ $task->project->title }}
                    </span>
                    @endif
                  </div>

                  <div class="mt-2 flex flex-wrap items-center gap-4 text-xs text-[rgb(var(--color-text-secondary))]">
                    @if ($task->assignee)
                    <span>المكلف: <strong>{{ $task->assignee->name }}</strong></span>
                    @endif

                    @if ($task->due_at)
                    <span>تاريخ الاستحقاق: <strong>{{ $task->due_at->format('Y/m/d') }}</strong></span>
                    @endif
                  </div>
                </div>

                <div class="self-end sm:self-center">
                  <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}" class="gdfh-btn gdfh-btn-secondary py-1 text-xs">
                    عرض المهمة
                  </a>
                </div>
              </div>
              @empty
              <div class="p-8 text-center text-xs text-[rgb(var(--color-text-secondary))]">
                لا توجد مهام مسندة لهذا الفريق حتى الآن.
              </div>
              @endforelse
            </div>
          </section>

        </div>

        {{-- Right Column: Members & Actions --}}
        <div class="space-y-8">

          {{-- Team Members Section --}}
          <section class="gdfh-card overflow-hidden">
            <div class="border-b border-[rgb(var(--color-border))] p-5">
              <h2 class="text-base font-bold text-[rgb(var(--color-text-primary))]">
                أعضاء الفريق ({{ $membersCount }})
              </h2>
              <p class="mt-0.5 text-xs text-[rgb(var(--color-text-secondary))]">
                قائمة بأعضاء الفريق وأدوارهم.
              </p>
            </div>

            <div class="divide-y divide-[rgb(var(--color-border))]">
              @forelse ($team->memberships as $membership)
              <div class="p-4 flex items-center justify-between">
                <div class="min-w-0">
                  <p class="text-xs font-bold text-[rgb(var(--color-text-primary))]">
                    {{ $membership->user?->name ?? 'مستخدم غير معروف' }}
                  </p>
                  <p dir="ltr" class="mt-0.5 truncate text-left text-[11px] text-[rgb(var(--color-text-secondary))]">
                    {{ $membership->user?->email ?? '—' }}
                  </p>
                </div>

                <span class="gdfh-badge bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))] text-[11px]">
                  {{ $roleLabels[$membership->role] ?? $membership->role }}
                </span>
              </div>
              @empty
              <div class="p-6 text-center text-xs text-[rgb(var(--color-text-secondary))]">
                لا يوجد أعضاء في الفريق حاليًا.
              </div>
              @endforelse
            </div>
          </section>

          {{-- Danger Zone Section --}}
          <section class="overflow-hidden rounded-xl border border-[rgb(var(--color-error)/0.25)] bg-[rgb(var(--color-error)/0.06)] p-5">
            <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">منطقة الخطر</h3>
            <p class="mt-1 text-xs leading-5 text-[rgb(var(--color-text-secondary))]">
              حذف الفريق إجراء نهائي ولا يمكن التراجع عنه.
            </p>
            <form method="POST" action="{{ route('teams.destroy', $team) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا الفريق؟')" class="mt-4">
              @csrf
              @method('DELETE')
              <button type="submit" class="w-full gdfh-btn bg-[rgb(var(--color-error))] text-white hover:brightness-95 py-2 text-xs font-semibold">
                حذف الفريق
              </button>
            </form>
          </section>

        </div>

      </div>

    </div>
  </div>
</x-app-layout>
