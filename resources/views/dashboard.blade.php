<x-app-layout>
  @php
  $hour = now()->hour;

  $greeting = match (true) {
  $hour < 12=> 'صباح الخير',
    $hour < 18=> 'مساء الخير',
      default => 'مساء الخير',
      };

      $projectStatusLabels = [
      'draft' => 'مسودة',
      'open' => 'مفتوح',
      'in_progress' => 'قيد التنفيذ',
      'on_hold' => 'متوقف مؤقتًا',
      'completed' => 'مكتمل',
      'cancelled' => 'ملغي',
      ];

      $taskPriorityLabels = [
      'low' => 'منخفضة',
      'medium' => 'متوسطة',
      'high' => 'عالية',
      'urgent' => 'عاجلة',
      ];
      @endphp

      <div class="gdfh-dashboard">
        {{-- Hero --}}
        <section class="gdfh-dashboard-hero">
          <div>
            <div class="gdfh-eyebrow">مساحة العمل</div>

            <h1 class="gdfh-dashboard-title">
              {{ $greeting }}، {{ auth()->user()->name }}
            </h1>

            <p class="gdfh-dashboard-subtitle">
              إليك نظرة سريعة على مشاريعك ومهامك وما يحتاج إلى انتباهك اليوم.
            </p>
          </div>

          <a href="{{ route('projects.create') }}" class="gdfh-primary-action">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="M12 5v14M5 12h14" stroke-linecap="round" />
            </svg>

            <span>مشروع جديد</span>
          </a>
        </section>

        {{-- Statistics --}}
        <section class="gdfh-stat-grid" aria-label="ملخص مساحة العمل">
          <article class="gdfh-stat-card">
            <div class="gdfh-stat-top">
              <div class="gdfh-stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                  <path d="M4 7.5h6l2 2H20v9.5H4z" stroke-linejoin="round" />
                  <path d="M4 7.5V5h6l2 2h8v2.5" stroke-linejoin="round" />
                </svg>
              </div>

              <span class="gdfh-stat-label">المشاريع النشطة</span>
            </div>

            <div class="gdfh-stat-value">
              {{ number_format($stats['active_projects']) }}
            </div>

            <p class="gdfh-stat-caption">
              المشاريع المفتوحة وقيد التنفيذ
            </p>
          </article>

          <article class="gdfh-stat-card">
            <div class="gdfh-stat-top">
              <div class="gdfh-stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                  <path d="M8 6h11M8 12h11M8 18h11" stroke-linecap="round" />
                  <path d="m3.5 6 1 1 2-2M3.5 12l1 1 2-2M3.5 18l1 1 2-2" stroke-linecap="round"
                    stroke-linejoin="round" />
                </svg>
              </div>

              <span class="gdfh-stat-label">المهام المفتوحة</span>
            </div>

            <div class="gdfh-stat-value">
              {{ number_format($stats['open_tasks']) }}
            </div>

            <p class="gdfh-stat-caption">
              المهام المسندة إليك حاليًا
            </p>
          </article>

          <article class="gdfh-stat-card">
            <div class="gdfh-stat-top">
              <div class="gdfh-stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                  <circle cx="9" cy="8" r="3" />
                  <circle cx="17" cy="9" r="2" />
                  <path d="M3.5 19c.5-3.5 2.4-5.5 5.5-5.5s5 2 5.5 5.5M14 14.5c2.8-.4 5 .9 6 3.5"
                    stroke-linecap="round" />
                </svg>
              </div>

              <span class="gdfh-stat-label">الفرق</span>
            </div>

            <div class="gdfh-stat-value">
              {{ number_format($stats['teams']) }}
            </div>

            <p class="gdfh-stat-caption">
              الفرق التي تديرها أو تعمل معها
            </p>
          </article>

          <article class="gdfh-stat-card gdfh-stat-card--attention">
            <div class="gdfh-stat-top">
              <div class="gdfh-stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                  <path d="M12 8v5" stroke-linecap="round" />
                  <path d="M12 16.5v.1" stroke-linecap="round" />
                  <path d="M10.2 4.6 3.4 17a2 2 0 0 0 1.8 3h13.6a2 2 0 0 0 1.8-3L13.8 4.6a2 2 0 0 0-3.6 0Z"
                    stroke-linejoin="round" />
                </svg>
              </div>

              <span class="gdfh-stat-label">تحتاج انتباهك</span>
            </div>

            <div class="gdfh-stat-value">
              {{ number_format($stats['overdue_tasks']) }}
            </div>

            <p class="gdfh-stat-caption">
              مهام تجاوزت موعد التسليم
            </p>
          </article>
        </section>

        {{-- Primary workspace --}}
        <section class="gdfh-dashboard-grid">
          {{-- Active projects --}}
          <article class="gdfh-panel gdfh-panel--projects">
            <header class="gdfh-panel-header">
              <div>
                <span class="gdfh-panel-kicker">المشاريع</span>
                <h2>المشاريع النشطة</h2>
              </div>

              <a href="{{ route('projects.index') }}" class="gdfh-text-link">
                <span>عرض الكل</span>

                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M15 6 9 12l6 6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </a>
            </header>

            @if ($activeProjects->isNotEmpty())
            <div class="gdfh-project-list">
              @foreach ($activeProjects as $project)
              <a href="{{ route('projects.show', $project) }}" class="gdfh-project-item">
                <div class="gdfh-project-heading">
                  <div class="gdfh-project-identity">
                    <span class="gdfh-project-dot"></span>

                    <div>
                      <h3>{{ $project->title }}</h3>

                      <span>
                        {{ $projectStatusLabels[$project->status] ?? $project->status }}
                      </span>
                    </div>
                  </div>

                  <strong>{{ $project->progress_percentage }}%</strong>
                </div>

                <div class="gdfh-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100"
                  aria-valuenow="{{ $project->progress_percentage }}">
                  <span style="width: {{ $project->progress_percentage }}%"></span>
                </div>

                <div class="gdfh-project-meta">
                  <span>
                    {{ number_format($project->completed_tasks_count) }}
                    من
                    {{ number_format($project->tasks_count) }}
                    مهمة مكتملة
                  </span>

                  <span class="gdfh-meta-separator"></span>

                  <span>
                    {{ number_format($project->active_members_count) }}
                    أعضاء
                  </span>

                  @if ($project->deadline)
                  <span class="gdfh-meta-separator"></span>

                  <span>
                    التسليم
                    {{ $project->deadline->translatedFormat('j M') }}
                  </span>
                  @endif
                </div>
              </a>
              @endforeach
            </div>
            @else
            <div class="gdfh-empty-state">
              <div class="gdfh-empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                  <path d="M4 7.5h6l2 2H20v9.5H4z" stroke-linejoin="round" />
                </svg>
              </div>

              <h3>ابدأ أول مشروع لك</h3>

              <p>
                لا توجد مشاريع نشطة حاليًا. أنشئ مشروعًا وابدأ بتنظيم العمل من مكان واحد.
              </p>

              <a href="{{ route('projects.create') }}" class="gdfh-secondary-action">
                إنشاء مشروع
              </a>
            </div>
            @endif
          </article>

          {{-- Tasks --}}
          <article class="gdfh-panel">
            <header class="gdfh-panel-header">
              <div>
                <span class="gdfh-panel-kicker">الأولوية الآن</span>
                <h2>مهامك القادمة</h2>
              </div>

              <div class="gdfh-live-indicator">
                <span></span>
                مباشر
              </div>
            </header>

            @if ($upcomingTasks->isNotEmpty())
            <div class="gdfh-task-list">
              @foreach ($upcomingTasks as $task)
              @php
              $isOverdue = $task->due_at
              && $task->due_at->isPast()
              && ! in_array($task->status, ['completed', 'cancelled'], true);
              @endphp

              <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}" class="gdfh-task-item">
                <div class="gdfh-task-check"></div>

                <div class="gdfh-task-content">
                  <div class="gdfh-task-title-row">
                    <h3>{{ $task->title }}</h3>

                    <span class="gdfh-priority gdfh-priority--{{ $task->priority }}">
                      {{ $taskPriorityLabels[$task->priority] ?? $task->priority }}
                    </span>
                  </div>

                  <div class="gdfh-task-meta">
                    <span>{{ $task->project->title }}</span>

                    @if ($task->due_at)
                    <span class="gdfh-meta-separator"></span>

                    <span @class(['gdfh-overdue'=> $isOverdue])>
                      @if ($isOverdue)
                      متأخرة
                      @else
                      {{ $task->due_at->translatedFormat('j M، H:i') }}
                      @endif
                    </span>
                    @endif
                  </div>
                </div>
              </a>
              @endforeach
            </div>
            @else
            <div class="gdfh-empty-state gdfh-empty-state--compact">
              <div class="gdfh-empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                  <path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </div>

              <h3>كل شيء تحت السيطرة</h3>

              <p>لا توجد مهام مفتوحة مسندة إليك حاليًا.</p>
            </div>
            @endif
          </article>
        </section>

        {{-- Secondary workspace --}}
        <section class="gdfh-dashboard-grid gdfh-dashboard-grid--secondary">
          {{-- Deadlines --}}
          <article class="gdfh-panel">
            <header class="gdfh-panel-header">
              <div>
                <span class="gdfh-panel-kicker">الجدول</span>
                <h2>المواعيد القادمة</h2>
              </div>
            </header>

            @if ($projectDeadlines->isNotEmpty())
            <div class="gdfh-deadline-list">
              @foreach ($projectDeadlines as $project)
              <a href="{{ route('projects.show', $project) }}" class="gdfh-deadline-item">
                <div class="gdfh-date-block">
                  <strong>{{ $project->deadline->format('d') }}</strong>
                  <span>{{ $project->deadline->translatedFormat('M') }}</span>
                </div>

                <div class="gdfh-deadline-content">
                  <h3>{{ $project->title }}</h3>

                  <span>
                    @if ($project->deadline->isToday())
                    اليوم
                    @elseif ($project->deadline->isTomorrow())
                    غدًا
                    @else
                    متبقي {{ (int) today()->diffInDays($project->deadline) }} أيام
                    @endif
                  </span>
                </div>

                <svg class="gdfh-row-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M15 6 9 12l6 6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </a>
              @endforeach
            </div>
            @else
            <div class="gdfh-empty-state gdfh-empty-state--compact">
              <div class="gdfh-empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                  <rect x="4" y="5.5" width="16" height="14" rx="2" />
                  <path d="M8 3.5v4M16 3.5v4M4 10h16" stroke-linecap="round" />
                </svg>
              </div>

              <h3>لا مواعيد قريبة</h3>
              <p>لا توجد مشاريع نشطة لها موعد تسليم قادم.</p>
            </div>
            @endif
          </article>

          {{-- Teams --}}
          <article class="gdfh-panel">
            <header class="gdfh-panel-header">
              <div>
                <span class="gdfh-panel-kicker">التعاون</span>
                <h2>فرقك</h2>
              </div>

              <a href="{{ route('teams.index') }}" class="gdfh-text-link">
                <span>عرض الكل</span>

                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M15 6 9 12l6 6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </a>
            </header>

            @if ($teams->isNotEmpty())
            <div class="gdfh-team-list">
              @foreach ($teams as $team)
              <a href="{{ route('teams.show', $team) }}" class="gdfh-team-item">
                <div class="gdfh-team-avatar">
                  {{ mb_substr($team->name, 0, 1) }}
                </div>

                <div class="gdfh-team-content">
                  <h3>{{ $team->name }}</h3>

                  <span>
                    {{ number_format($team->active_members_count) }}
                    أعضاء نشطين
                  </span>
                </div>

                <svg class="gdfh-row-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M15 6 9 12l6 6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </a>
              @endforeach
            </div>
            @else
            <div class="gdfh-empty-state gdfh-empty-state--compact">
              <div class="gdfh-empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                  <circle cx="9" cy="8" r="3" />
                  <circle cx="17" cy="9" r="2" />
                  <path d="M3.5 19c.5-3.5 2.4-5.5 5.5-5.5s5 2 5.5 5.5M14 14.5c2.8-.4 5 .9 6 3.5"
                    stroke-linecap="round" />
                </svg>
              </div>

              <h3>ابنِ فريقك</h3>
              <p>أنشئ فريقًا أو انضم إلى فريق لبدء العمل المشترك.</p>

              <a href="{{ route('teams.create') }}" class="gdfh-secondary-action">
                إنشاء فريق
              </a>
            </div>
            @endif
          </article>
        </section>
      </div>
</x-app-layout>
