<x-app-layout>
  @php
  $statusLabels = [
      'draft' => 'مسودة',
      'open' => 'مفتوح',
      'in_progress' => 'قيد التنفيذ',
      'review' => 'قيد المراجعة',
      'on_hold' => 'متوقف مؤقتًا',
      'completed' => 'مكتمل',
      'cancelled' => 'ملغي',
      'archived' => 'مؤرشف',
  ];

  $visibilityLabels = [
      'private' => 'خاص',
      'marketplace' => 'سوق المشاريع',
      'public' => 'عsubscriptions عام',
  ];

  $taskStatusLabels = [
      'todo' => 'قيد الانتظار',
      'in_progress' => 'جاري العمل',
      'review' => 'قيد المراجعة',
      'in_review' => 'قيد المراجعة',
      'completed' => 'مكتمل',
      'done' => 'مكتمل',
      'cancelled' => 'ملغي',
  ];

  $taskPriorityLabels = [
      'low' => 'منخفضة',
      'medium' => 'متوسطة',
      'high' => 'عالية',
      'urgent' => 'عاجلة',
  ];

  $roleLabels = [
      'owner' => 'مالك',
      'admin' => 'مدير (Admin)',
      'manager' => 'مدير عمل',
      'member' => 'عضو',
      'viewer' => 'مشاهد',
      'project_manager' => 'مدير مشروع',
      'team_leader' => 'قائد فريق',
  ];

  $progress = $project->progress();
  $isLate = $project->isLate();
  $isArchived = $project->isArchived();
  $remainingDays = $project->remainingDays();
  $durationDays = $project->durationDays();
  $targetDueDate = $project->getTargetDueDate();
  @endphp

  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="min-w-0">
        <div class="flex items-center gap-2 text-xs font-medium text-[rgb(var(--color-text-secondary))]">
          <a href="{{ route('projects.index') }}" class="transition hover:text-[rgb(var(--color-copper))]">
            المشاريع
          </a>

          <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6" />
          </svg>

          <span class="truncate text-[rgb(var(--color-text-primary))]">
            {{ $project->title }}
          </span>
        </div>

        <h2 class="mt-1 truncate text-xl font-bold text-[rgb(var(--color-text-primary))]">
          بيئة عمل المشروع (Workspace)
        </h2>
      </div>

      <div class="flex items-center gap-2">
        @can('create', [App\Models\Task::class, $project])
        <a href="{{ route('projects.tasks.create', $project) }}" class="gdfh-btn gdfh-btn-brand text-xs">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" d="M12 5v14M5 12h14" />
          </svg>
          إضافة مهمة جديدة
        </a>
        @endcan

        @can('update', $project)
        <a href="{{ route('projects.edit', $project) }}" class="gdfh-btn gdfh-btn-secondary text-xs">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6.5l4 4M4 20l4.5-1 10-10a2.8 2.8 0 00-4-4l-10 10L4 20z" />
          </svg>
          تعديل
        </a>
        @endcan
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
    <div class="mx-auto max-w-7xl space-y-6">

      {{-- Flash Messages --}}
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

      {{-- Project Header Card --}}
      <section class="gdfh-card relative overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-1" style="background: linear-gradient(90deg, rgb(var(--color-copper)), rgb(var(--color-mineral)));"></div>

        <div class="p-6 sm:p-8">
          <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0 flex-1 space-y-4">
              <div class="flex flex-wrap items-center gap-2">
                {{-- Status Badge --}}
                <span class="gdfh-badge font-bold text-xs" style="background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));">
                  {{ $statusLabels[$project->status] ?? $project->status }}
                </span>

                {{-- Visibility Badge --}}
                <span class="gdfh-badge text-xs" style="background-color: rgb(var(--color-mineral-soft)); color: rgb(var(--color-mineral));">
                  {{ $visibilityLabels[$project->visibility] ?? $project->visibility }}
                </span>

                {{-- Team Badge --}}
                @if ($project->team)
                <a href="{{ route('teams.show', $project->team) }}" class="gdfh-badge bg-amber-500/10 text-amber-600 hover:underline text-xs">
                  الفريق: {{ $project->team->name }}
                </a>
                @endif

                {{-- Late Badge --}}
                @if ($isLate)
                <span class="gdfh-badge bg-red-500/10 text-red-500 font-bold text-xs">
                  متأخر عن الموعد
                </span>
                @endif

                {{-- Archived Badge --}}
                @if ($isArchived)
                <span class="gdfh-badge bg-gray-500/10 text-gray-500 font-bold text-xs">
                  مؤرشف
                </span>
                @endif
              </div>

              <h1 class="text-2xl font-bold tracking-tight text-[rgb(var(--color-text-primary))] sm:text-3xl">
                {{ $project->title }}
              </h1>

              <p class="max-w-3xl whitespace-pre-line text-sm text-[rgb(var(--color-text-secondary))] leading-7">
                {{ $project->description }}
              </p>
            </div>

            {{-- Owner Card --}}
            <div class="w-full shrink-0 rounded-xl border p-4 lg:w-72" style="border-color: rgb(var(--color-border)); background-color: rgb(var(--color-surface-soft));">
              <p class="text-xs font-semibold text-[rgb(var(--color-text-secondary))]">مالك المشروع</p>
              <div class="mt-3 flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[rgb(var(--color-copper-soft))] text-sm font-bold text-[rgb(var(--color-copper))]">
                  {{ mb_strtoupper(mb_substr($project->owner->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                  <p class="truncate text-sm font-bold text-[rgb(var(--color-text-primary))]">{{ $project->owner->name }}</p>
                  <p dir="ltr" class="truncate text-left text-xs text-[rgb(var(--color-text-secondary))]">
                    {{ $project->owner->username ? '@' . $project->owner->username : $project->owner->email }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          {{-- Progress Bar & Timeline Section --}}
          <div class="mt-8 pt-6 border-t border-[rgb(var(--color-border))] space-y-4">
            <div class="flex items-center justify-between gap-4">
              <div>
                <span class="text-xs font-bold text-[rgb(var(--color-text-primary))]">نسبة إنجاز المشروع</span>
                <span class="ms-2 text-xs font-bold text-[rgb(var(--color-copper))]">{{ $progress }}%</span>
              </div>

              <div class="flex items-center gap-4 text-xs text-[rgb(var(--color-text-secondary))]">
                <span>المتبقي: <strong>{{ $remainingDays }} يوم</strong></span>
                <span>إجمالي المدة: <strong>{{ $durationDays }} يوم</strong></span>
              </div>
            </div>

            <div class="h-2.5 w-full overflow-hidden rounded-full bg-[rgb(var(--color-surface-soft))]">
              <div class="h-full rounded-full transition-all duration-500" style="width: {{ $progress }}%; background-color: rgb(var(--color-copper));"></div>
            </div>
          </div>

          {{-- Quick Facts Banner --}}
          <div class="mt-6 grid grid-cols-2 gap-px overflow-hidden rounded-xl border bg-[rgb(var(--color-border))] md:grid-cols-4" style="border-color: rgb(var(--color-border));">
            <div class="bg-[rgb(var(--color-surface))] p-4">
              <span class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">تاريخ البداية</span>
              <p class="mt-1 text-sm font-bold text-[rgb(var(--color-text-primary))]">
                {{ $project->start_date ? $project->start_date->format('Y/m/d') : 'غير محدد' }}
              </p>
            </div>

            <div class="bg-[rgb(var(--color-surface))] p-4">
              <span class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">الموعد النهائي</span>
              <p class="mt-1 text-sm font-bold text-[rgb(var(--color-text-primary))]">
                {{ $targetDueDate ? $targetDueDate->format('Y/m/d') : 'غير محدد' }}
              </p>
            </div>

            <div class="bg-[rgb(var(--color-surface))] p-4">
              <span class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">الميزانية</span>
              <p class="mt-1 text-sm font-bold text-[rgb(var(--color-text-primary))]">
                @if ($project->budget !== null)
                  {{ number_format((float)$project->budget, 2) }} {{ strtoupper($project->currency) }}
                @elseif ($project->budget_min !== null)
                  {{ number_format((float)$project->budget_min, 2) }} {{ strtoupper($project->currency) }}
                @else
                  غير محدودة
                @endif
              </p>
            </div>

            <div class="bg-[rgb(var(--color-surface))] p-4">
              <span class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">المهام الكلية</span>
              <p class="mt-1 text-sm font-bold text-[rgb(var(--color-text-primary))]">
                {{ $project->tasks->count() }} task(s)
              </p>
            </div>
          </div>
        </div>
      </section>

      {{-- Main Content Grid --}}
      <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- Main Column: Tasks & Members --}}
        <div class="space-y-6 xl:col-span-2">

          {{-- Tasks Workspace --}}
          <section class="gdfh-card overflow-hidden">
            <div class="flex items-center justify-between border-b border-[rgb(var(--color-border))] p-5">
              <div>
                <h2 class="text-base font-bold text-[rgb(var(--color-text-primary))]">مهام المشروع</h2>
                <p class="mt-0.5 text-xs text-[rgb(var(--color-text-secondary))]">قائمة جميع المهام وحالات تنفيذها.</p>
              </div>

              @can('create', [App\Models\Task::class, $project])
              <a href="{{ route('projects.tasks.create', $project) }}" class="gdfh-btn gdfh-btn-brand text-xs">
                + إضافة مهمة
              </a>
              @endcan
            </div>

            <div class="divide-y divide-[rgb(var(--color-border))]">
              @forelse ($project->tasks as $task)
              <div class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 hover:bg-[rgb(var(--color-surface-soft)/0.5)] transition">
                <div class="min-w-0 space-y-1">
                  <div class="flex items-center gap-2">
                    <a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="text-sm font-bold text-[rgb(var(--color-text-primary))] hover:text-[rgb(var(--color-copper))] truncate">
                      {{ $task->title }}
                    </a>

                    <span class="gdfh-badge text-[11px]" style="background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));">
                      {{ $taskStatusLabels[$task->status] ?? $task->status }}
                    </span>

                    <span class="gdfh-badge text-[11px] bg-gray-500/10 text-gray-600">
                      {{ $taskPriorityLabels[$task->priority] ?? $task->priority }}
                    </span>
                  </div>

                  @if ($task->description)
                  <p class="text-xs text-[rgb(var(--color-text-secondary))] line-clamp-1">
                    {{ $task->description }}
                  </p>
                  @endif

                  <div class="flex items-center gap-4 text-[11px] text-[rgb(var(--color-text-secondary))]">
                    <span>المسند إليه: <strong>{{ $task->assignee?->name ?? 'غير معين' }}</strong></span>
                    <span>تاريخ الاستحقاق: <strong>{{ $task->due_at ? $task->due_at->format('Y/m/d') : '—' }}</strong></span>
                  </div>
                </div>

                <div class="self-end sm:self-center shrink-0">
                  <a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="gdfh-btn gdfh-btn-secondary text-xs">
                    عرض التفاصيل
                  </a>
                </div>
              </div>
              @empty
              <div class="p-8 text-center space-y-3">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))]">
                  <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2" /><path d="M8 9h8M8 13h5" /></svg>
                </div>
                <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">لا توجد مهام في هذا المشروع بعد</h3>
                <p class="text-xs text-[rgb(var(--color-text-secondary))] max-w-sm mx-auto">ابدأ بإضافة مهام لتنفيذ المشروع وتتبع تقدم الإنجاز تلقائياً.</p>
                @can('create', [App\Models\Task::class, $project])
                <a href="{{ route('projects.tasks.create', $project) }}" class="inline-flex gdfh-btn gdfh-btn-brand text-xs mt-2">
                  إضافة أول مهمة
                </a>
                @endcan
              </div>
              @endforelse
            </div>
          </section>

          {{-- Team & Members Workspace --}}
          <section class="gdfh-card overflow-hidden">
            <div class="border-b border-[rgb(var(--color-border))] p-5">
              <h2 class="text-base font-bold text-[rgb(var(--color-text-primary))]">فريق وأعضاء المشروع</h2>
              <p class="mt-0.5 text-xs text-[rgb(var(--color-text-secondary))]">الأعضاء المشاركون وصلاحياتهم داخل بيئة العمل.</p>
            </div>

            <div class="divide-y divide-[rgb(var(--color-border))]">
              {{-- Project Owner --}}
              <div class="p-4 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                  <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[rgb(var(--color-copper-soft))] text-xs font-bold text-[rgb(var(--color-copper))]">
                    {{ mb_strtoupper(mb_substr($project->owner->name, 0, 1)) }}
                  </div>
                  <div>
                    <p class="text-xs font-bold text-[rgb(var(--color-text-primary))]">{{ $project->owner->name }}</p>
                    <p dir="ltr" class="text-[11px] text-[rgb(var(--color-text-secondary))]">
                      {{ $project->owner->username ? '@' . $project->owner->username : $project->owner->email }}
                    </p>
                  </div>
                </div>
                <span class="gdfh-badge bg-amber-500/10 text-amber-600 text-[11px] font-bold">مالك المشروع</span>
              </div>

              {{-- Team Members --}}
              @if ($project->team)
                @foreach ($project->team->memberships as $membership)
                  @if ($membership->user_id !== $project->owner_id && $membership->user)
                  <div class="p-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                      <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[rgb(var(--color-surface-soft))] text-xs font-bold text-[rgb(var(--color-text-primary))]">
                        {{ mb_strtoupper(mb_substr($membership->user->name, 0, 1)) }}
                      </div>
                      <div>
                        <p class="text-xs font-bold text-[rgb(var(--color-text-primary))]">{{ $membership->user->name }}</p>
                        <p dir="ltr" class="text-[11px] text-[rgb(var(--color-text-secondary))]">
                          {{ $membership->user->username ? '@' . $membership->user->username : $membership->user->email }}
                        </p>
                      </div>
                    </div>
                    <span class="gdfh-badge bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))] text-[11px]">
                      {{ $roleLabels[$membership->role] ?? $membership->role }}
                    </span>
                  </div>
                  @endif
                @endforeach
              @endif
            </div>
          </section>

          {{-- Activity Audit Timeline Section --}}
          <section class="gdfh-card overflow-hidden">
            <div class="border-b border-[rgb(var(--color-border))] p-5">
              <h2 class="text-base font-bold text-[rgb(var(--color-text-primary))]">سجل الأنشطة والأحداث (Activity Timeline)</h2>
              <p class="mt-0.5 text-xs text-[rgb(var(--color-text-secondary))]">سجل زمني لجميع عمليات وإجراءات المشروع التراكمية.</p>
            </div>

            <div class="divide-y divide-[rgb(var(--color-border))] p-5 space-y-4">
              @forelse ($activities as $activity)
              <div class="flex items-start gap-3 text-xs">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))] font-bold text-xs">
                  {{ $activity->user ? mb_strtoupper(mb_substr($activity->user->name, 0, 1)) : 'S' }}
                </div>

                <div class="min-w-0 flex-1 space-y-1">
                  <div class="flex items-center justify-between gap-2">
                    <p class="font-bold text-[rgb(var(--color-text-primary))]">
                      {{ $activity->user?->name ?? 'النظام' }}
                    </p>
                    <span class="text-[11px] text-[rgb(var(--color-text-secondary))]">
                      {{ $activity->created_at->diffForHumans() }}
                    </span>
                  </div>

                  <p class="text-[rgb(var(--color-text-secondary))] leading-5">
                    {{ $activity->description }}
                  </p>
                </div>
              </div>
              @empty
              <div class="p-6 text-center text-xs text-[rgb(var(--color-text-secondary))]">
                لا توجد أنشطة مسجلة لهذا المشروع حتى الآن.
              </div>
              @endforelse
            </div>
          </section>

          {{-- Comments & Discussions Section --}}
          <section class="gdfh-card overflow-hidden" x-data="{ replyingTo: null }">
            <div class="border-b border-[rgb(var(--color-border))] p-5">
              <h2 class="text-base font-bold text-[rgb(var(--color-text-primary))]">النقاشات والتعليقات (Discussions)</h2>
              <p class="mt-0.5 text-xs text-[rgb(var(--color-text-secondary))]">ناقش التفاصيل والقرارات مع أعضاء المشروع (يدعم الإشارة بـ @username).</p>
            </div>

            {{-- New Comment Form --}}
            <div class="p-5 border-b border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface-soft)/0.3)]">
              <form method="POST" action="{{ route('projects.comments.store', $project) }}" class="space-y-3">
                @csrf
                <div>
                  <textarea name="body" required rows="3" placeholder="اكتب تعليقك هنا... يمكنك إشارة شخص بـ @username" class="gdfh-input text-xs w-full"></textarea>
                </div>
                <div class="flex justify-end">
                  <button type="submit" class="gdfh-btn gdfh-btn-brand text-xs">
                    إضافة تعليق
                  </button>
                </div>
              </form>
            </div>

            {{-- Comments List --}}
            <div class="divide-y divide-[rgb(var(--color-border))] p-5 space-y-6">
              @forelse ($project->comments as $comment)
              <div class="space-y-4 text-xs">
                {{-- Main Comment --}}
                <div class="flex items-start gap-3">
                  <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))] font-bold text-xs">
                    {{ mb_strtoupper(mb_substr($comment->user?->name ?? 'U', 0, 1)) }}
                  </div>

                  <div class="min-w-0 flex-1 space-y-1">
                    <div class="flex items-center justify-between gap-2">
                      <div class="flex items-center gap-2">
                        <span class="font-bold text-[rgb(var(--color-text-primary))]">{{ $comment->user?->name }}</span>
                        @if ($comment->user?->username)
                        <span class="text-[11px] font-mono text-[rgb(var(--color-copper))] dir-ltr">{{ '@' . $comment->user->username }}</span>
                        @endif
                      </div>

                      <div class="flex items-center gap-3">
                        <span class="text-[11px] text-[rgb(var(--color-text-secondary))]">
                          {{ $comment->created_at->diffForHumans() }}
                          @if ($comment->isEdited()) <span class="text-amber-500 font-medium">(مُعدل)</span> @endif
                        </span>

                        @can('delete', $comment)
                        <form method="POST" action="{{ route('comments.destroy', $comment) }}" onsubmit="return confirm('هل تريد حذف هذا التعليق؟')">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="text-red-500 hover:underline text-[11px]">حذف</button>
                        </form>
                        @endcan
                      </div>
                    </div>

                    <p class="text-[rgb(var(--color-text-primary))] leading-6 whitespace-pre-line">
                      {{ $comment->body ?: $comment->content }}
                    </p>

                    <div class="pt-1">
                      <button type="button" @click="replyingTo = replyingTo === {{ $comment->id }} ? null : {{ $comment->id }}" class="text-[11px] font-bold text-[rgb(var(--color-copper))] hover:underline">
                        رد على التعليق
                      </button>
                    </div>

                    {{-- Reply Form --}}
                    <div x-show="replyingTo === {{ $comment->id }}" x-cloak class="mt-3 pt-2">
                      <form method="POST" action="{{ route('comments.replies.store', $comment) }}" class="space-y-2">
                        @csrf
                        <textarea name="body" required rows="2" placeholder="اكتب ردك..." class="gdfh-input text-xs w-full"></textarea>
                        <div class="flex justify-end gap-2">
                          <button type="button" @click="replyingTo = null" class="gdfh-btn gdfh-btn-secondary text-[11px] py-1">إلغاء</button>
                          <button type="submit" class="gdfh-btn gdfh-btn-brand text-[11px] py-1">إرسال الرد</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

                {{-- Threaded Replies --}}
                @if ($comment->replies->count() > 0)
                <div class="ms-8 space-y-3 border-r-2 border-[rgb(var(--color-border))] pe-3">
                  @foreach ($comment->replies as $reply)
                  <div class="flex items-start gap-2 text-xs">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-primary))] font-bold text-[11px]">
                      {{ mb_strtoupper(mb_substr($reply->user?->name ?? 'U', 0, 1)) }}
                    </div>

                    <div class="min-w-0 flex-1 space-y-1">
                      <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                          <span class="font-bold text-[rgb(var(--color-text-primary))]">{{ $reply->user?->name }}</span>
                          @if ($reply->user?->username)
                          <span class="text-[10px] font-mono text-[rgb(var(--color-copper))] dir-ltr">{{ '@' . $reply->user->username }}</span>
                          @endif
                        </div>

                        <div class="flex items-center gap-2">
                          <span class="text-[10px] text-[rgb(var(--color-text-secondary))]">{{ $reply->created_at->diffForHumans() }}</span>
                          @can('delete', $reply)
                          <form method="POST" action="{{ route('comments.destroy', $reply) }}" onsubmit="return confirm('هل تريد حذف الرد؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:underline text-[10px]">حذف</button>
                          </form>
                          @endcan
                        </div>
                      </div>

                      <p class="text-[rgb(var(--color-text-primary))] leading-5 whitespace-pre-line text-[11px]">
                        {{ $reply->body ?: $reply->content }}
                      </p>
                    </div>
                  </div>
                  @endforeach
                </div>
                @endif
              </div>
              @empty
              <div class="p-8 text-center space-y-2">
                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))]">
                  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">لا توجد تعليقات أو نقاشات بعد</h3>
                <p class="text-[11px] text-[rgb(var(--color-text-secondary))] max-w-xs mx-auto">اكتب أول تعليق لبدء النقاش والتواصل مع أعضاء المشروع.</p>
              </div>
              @endforelse
            </div>
          </section>

          {{-- Files & Attachments Section --}}
          <section class="gdfh-card overflow-hidden">
            <div class="border-b border-[rgb(var(--color-border))] p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
              <div>
                <h2 class="text-base font-bold text-[rgb(var(--color-text-primary))]">ملفات ومرفقات المشروع (Files)</h2>
                <p class="mt-0.5 text-xs text-[rgb(var(--color-text-secondary))]">المستندات والصور المرفقة مع إمكانية التنزيل الفوري.</p>
              </div>
            </div>

            {{-- File Upload Form --}}
            <div class="p-5 border-b border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface-soft)/0.3)]">
              <form method="POST" action="{{ route('projects.attachments.store', $project) }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                @csrf
                <input type="file" name="file" required class="gdfh-input text-xs flex-1 file:me-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[rgb(var(--color-copper-soft))] file:text-[rgb(var(--color-copper))]" />
                <button type="submit" class="gdfh-btn gdfh-btn-brand text-xs shrink-0">
                  رفع ملف جديد
                </button>
              </form>
            </div>

            {{-- Files List --}}
            <div class="divide-y divide-[rgb(var(--color-border))]">
              @forelse ($project->attachments as $attachment)
              <div class="p-4 flex items-center justify-between gap-3 hover:bg-[rgb(var(--color-surface-soft)/0.5)] transition">
                <div class="flex items-center gap-3 min-w-0">
                  <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))] font-bold text-xs">
                    {{ strtoupper($attachment->extension ?: 'FILE') }}
                  </div>

                  <div class="min-w-0 space-y-0.5">
                    <p class="text-xs font-bold text-[rgb(var(--color-text-primary))] truncate">
                      {{ $attachment->original_name }}
                    </p>
                    <div class="flex items-center gap-3 text-[11px] text-[rgb(var(--color-text-secondary))]">
                      <span>الحجم: <strong>{{ $attachment->formattedSize() }}</strong></span>
                      <span>بواسطة: <strong>{{ $attachment->user?->name ?? 'مستخدم' }}</strong></span>
                      <span>{{ $attachment->created_at->diffForHumans() }}</span>
                    </div>
                  </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                  <a href="{{ route('attachments.download', $attachment) }}" class="gdfh-btn gdfh-btn-secondary text-xs py-1.5 px-3">
                    تنزيل
                  </a>

                  @can('delete', $attachment)
                  <form method="POST" action="{{ route('attachments.destroy', $attachment) }}" onsubmit="return confirm('هل تريد حذف هذا الملف؟')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="gdfh-btn text-xs py-1.5 px-2.5 bg-red-500/10 text-red-500 hover:bg-red-500/20">
                      حذف
                    </button>
                  </form>
                  @endcan
                </div>
              </div>
              @empty
              <div class="p-8 text-center space-y-2">
                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))]">
                  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">لا توجد ملفات مرفقة بعد</h3>
                <p class="text-[11px] text-[rgb(var(--color-text-secondary))] max-w-xs mx-auto">ارفع الملفات والمستندات الهامة الخاصة بالمشروع لتكون متاحة للجميع.</p>
              </div>
              @endforelse
            </div>
          </section>

        </div>

        {{-- Side Column: Quick Actions & Workflow Management --}}
        <aside class="space-y-6">

          {{-- Workflow Actions Card --}}
          @can('update', $project)
          <section class="gdfh-card p-5 space-y-4">
            <h2 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">إدارة حالة المشروع</h2>
            <p class="text-xs text-[rgb(var(--color-text-secondary))]">تغيير مرحلة التنفيذ وفق المحرك البرمجي للمشروع.</p>

            <form method="POST" action="{{ route('projects.change-status', $project) }}" class="space-y-3">
              @csrf
              <select name="status" required class="gdfh-input text-xs">
                <option value="draft" @selected($project->status === 'draft')>مسودة (Draft)</option>
                <option value="open" @selected($project->status === 'open')>مفتوح (Open)</option>
                <option value="in_progress" @selected($project->status === 'in_progress')>قيد التنفيذ (In Progress)</option>
                <option value="review" @selected($project->status === 'review')>قيد المراجعة (Review)</option>
                <option value="completed" @selected($project->status === 'completed')>مكتمل (Completed)</option>
                <option value="cancelled" @selected($project->status === 'cancelled')>ملغي (Cancelled)</option>
              </select>

              <button type="submit" class="w-full gdfh-btn gdfh-btn-brand text-xs py-2 font-bold">
                حفظ الحالة الجديدة
              </button>
            </form>
          </section>
          @endcan

          {{-- Archive & Restore Card --}}
          @can('archive', $project)
          <section class="gdfh-card p-5 space-y-3">
            <h2 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">أرشفة واستعادة المشروع</h2>
            <p class="text-xs text-[rgb(var(--color-text-secondary))]">تحكم في حالة أرشفة المشروع وسجل البيانات.</p>

            @if ($isArchived)
            <form method="POST" action="{{ route('projects.restore', $project) }}">
              @csrf
              <button type="submit" class="w-full gdfh-btn gdfh-btn-secondary text-xs py-2 font-bold">
                استعادة المشروع من الأرشيف
              </button>
            </form>
            @else
            <form method="POST" action="{{ route('projects.archive', $project) }}" onsubmit="return confirm('هل تريد أرشفة هذا المشروع؟')">
              @csrf
              <button type="submit" class="w-full gdfh-btn text-xs py-2 font-bold border border-[rgb(var(--color-border))] text-[rgb(var(--color-text-primary))] hover:bg-[rgb(var(--color-surface-soft))]">
                أرشفة المشروع
              </button>
            </form>
            @endif
          </section>
          @endcan

          {{-- Danger Zone Card --}}
          @can('delete', $project)
          <section class="overflow-hidden rounded-xl border border-[rgb(var(--color-error)/0.35)] bg-[rgb(var(--color-error)/0.06)] p-5">
            <h2 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">منطقة الخطر</h2>
            <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))] leading-5">حذف المشروع إجراء نهائي ولا يمكن التراجع عنه.</p>
            <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا المشروع؟')" class="mt-4">
              @csrf
              @method('DELETE')
              <button type="submit" class="w-full gdfh-btn bg-[rgb(var(--color-error))] text-white hover:brightness-95 py-2 text-xs font-semibold">
                حذف المشروع نهائياً
              </button>
            </form>
          </section>
          @endcan

        </aside>
      </div>

    </div>
  </div>
</x-app-layout>
