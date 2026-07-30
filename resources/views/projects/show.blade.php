<x-app-layout>
  @php
  $statusLabels = [
  'draft' => 'مسودة',
  'open' => 'مفتوح',
  'in_progress' => 'قيد التنفيذ',
  'on_hold' => 'متوقف مؤقتًا',
  'completed' => 'مكتمل',
  'cancelled' => 'ملغي',
  ];

  $visibilityLabels = [
  'private' => 'خاص',
  'marketplace' => 'سوق المشاريع',
  ];

  $budgetTypeLabels = [
  'fixed' => 'ميزانية ثابتة',
  'hourly' => 'بالساعة',
  ];

  $roleLabels = [
  'project_manager' => 'مدير مشروع',
  'team_leader' => 'قائد فريق',
  'member' => 'عضو',
  'viewer' => 'مشاهد',
  ];

  $memberStatusLabels = [
  'pending' => 'بانتظار التفعيل',
  'active' => 'نشط',
  'suspended' => 'موقوف',
  'left' => 'غادر',
  ];

  $memberCount = $project->memberRecords->count();

  $budgetMin = $project->budget_min !== null
  ? number_format((float) $project->budget_min, 2)
  : null;

  $budgetMax = $project->budget_max !== null
  ? number_format((float) $project->budget_max, 2)
  : null;
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
          تفاصيل المشروع
        </h2>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('projects.index') }}" class="gdfh-btn gdfh-btn-secondary">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6" />
          </svg>

          <span class="hidden sm:inline">المشاريع</span>
        </a>

        <a href="{{ route('projects.edit', $project) }}" class="gdfh-btn gdfh-btn-brand">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M13.5 6.5l4 4M4 20l4.5-1 10-10a2.8 2.8 0 00-4-4l-10 10L4 20z" />
          </svg>

          تعديل المشروع
        </a>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
    <div class="mx-auto max-w-7xl">

      {{-- Success --}}
      @if (session('success'))
      <div class="mb-6 flex items-start gap-3 rounded-xl border p-4" style="
                        border-color: rgb(var(--color-success) / 0.30);
                        background-color: rgb(var(--color-success) / 0.08);
                    ">
        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" style="
                            background-color: rgb(var(--color-success) / 0.12);
                            color: rgb(var(--color-success));
                        ">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5l4 4L19 7" />
          </svg>
        </div>

        <div>
          <p class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
            تمت العملية بنجاح
          </p>

          <p class="mt-1 text-sm" style="color: rgb(var(--color-success));">
            {{ session('success') }}
          </p>
        </div>
      </div>
      @endif

      {{-- Errors --}}
      @if ($errors->any())
      <div class="mb-6 flex items-start gap-3 rounded-xl border p-4" style="
                        border-color: rgb(var(--color-error) / 0.30);
                        background-color: rgb(var(--color-error) / 0.08);
                    ">
        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" style="
                            background-color: rgb(var(--color-error) / 0.12);
                            color: rgb(var(--color-error));
                        ">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 9v4m0 4h.01M10.3 3.8L2.6 17.1A2 2 0 004.3 20h15.4a2 2 0 001.7-2.9L13.7 3.8a2 2 0 00-3.4 0z" />
          </svg>
        </div>

        <div>
          <p class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
            يوجد بعض الحقول التي تحتاج إلى مراجعة
          </p>

          <ul class="mt-2 space-y-1 text-sm" style="color: rgb(var(--color-error));">
            @foreach ($errors->all() as $error)
            <li>• {{ $error }}</li>
            @endforeach
          </ul>
        </div>
      </div>
      @endif

      {{-- Project Hero --}}
      <section class="gdfh-card relative overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-1" style="
                        background: linear-gradient(
                            90deg,
                            rgb(var(--color-copper)),
                            rgb(var(--color-mineral))
                        );
                    "></div>

        <div class="p-5 sm:p-7 lg:p-8">
          <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center gap-2">
                <span class="gdfh-badge" style="
                                        background-color: rgb(var(--color-copper-soft));
                                        color: rgb(var(--color-copper));
                                    ">
                  {{ $statusLabels[$project->status] ?? $project->status }}
                </span>

                <span class="gdfh-badge" style="
                                        background-color: rgb(var(--color-mineral-soft));
                                        color: rgb(var(--color-mineral));
                                    ">
                  @if ($project->visibility === 'private')
                  <svg class="me-1 h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.8">
                    <rect x="5" y="10" width="14" height="10" rx="2" />
                    <path d="M8 10V7a4 4 0 018 0v3" />
                  </svg>
                  @else
                  <svg class="me-1 h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.8">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M3 12h18M12 3a15 15 0 010 18M12 3a15 15 0 000 18" />
                  </svg>
                  @endif

                  {{ $visibilityLabels[$project->visibility] ?? $project->visibility }}
                </span>

                @if ($project->category)
                <span class="gdfh-badge bg-[rgb(var(--color-surface-soft))]
                                               text-[rgb(var(--color-text-secondary))]">
                  {{ $project->category }}
                </span>
                @endif
              </div>

              <h1 class="mt-5 break-words text-2xl font-bold tracking-tight
                                       text-[rgb(var(--color-text-primary))]
                                       sm:text-3xl lg:text-4xl">
                {{ $project->title }}
              </h1>

              <p class="mt-4 max-w-3xl whitespace-pre-line text-sm leading-8
                                       text-[rgb(var(--color-text-secondary))] sm:text-base">
                {{ $project->description }}</p>
            </div>

            {{-- Owner --}}
            <div class="w-full shrink-0 rounded-xl border p-4 lg:w-72" style="
                                border-color: rgb(var(--color-border));
                                background-color: rgb(var(--color-surface-soft));
                            ">
              <p class="text-xs font-semibold text-[rgb(var(--color-text-secondary))]">
                مالك المشروع
              </p>

              <div class="mt-3 flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full
                                           bg-[rgb(var(--color-copper-soft))]
                                           text-sm font-bold text-[rgb(var(--color-copper))]">
                  {{ mb_strtoupper(mb_substr($project->owner->name, 0, 1)) }}
                </div>

                <div class="min-w-0">
                  <p class="truncate text-sm font-bold text-[rgb(var(--color-text-primary))]">
                    {{ $project->owner->name }}
                  </p>

                  <p dir="ltr" class="mt-0.5 truncate text-left text-xs text-[rgb(var(--color-text-secondary))]">
                    {{ $project->owner->email }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          {{-- Quick Facts --}}
          <div class="mt-7 grid grid-cols-2 gap-px overflow-hidden rounded-xl border
                               bg-[rgb(var(--color-border))]
                               md:grid-cols-4" style="border-color: rgb(var(--color-border));">
            <div class="bg-[rgb(var(--color-surface))] p-4">
              <div class="flex items-center gap-2 text-[rgb(var(--color-text-secondary))]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <rect x="3" y="5" width="18" height="16" rx="2" />
                  <path d="M8 3v4M16 3v4M3 10h18" />
                </svg>

                <span class="text-xs font-medium">تاريخ البداية</span>
              </div>

              <p class="mt-2 text-sm font-bold text-[rgb(var(--color-text-primary))]">
                {{ $project->start_date
                                    ? $project->start_date->format('Y/m/d')
                                    : 'غير محدد' }}
              </p>
            </div>

            <div class="bg-[rgb(var(--color-surface))] p-4">
              <div class="flex items-center gap-2 text-[rgb(var(--color-text-secondary))]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <circle cx="12" cy="12" r="9" />
                  <path d="M12 7v5l3 2" />
                </svg>

                <span class="text-xs font-medium">الموعد النهائي</span>
              </div>

              <p class="mt-2 text-sm font-bold text-[rgb(var(--color-text-primary))]">
                {{ $project->deadline
                                    ? $project->deadline->format('Y/m/d')
                                    : 'غير محدد' }}
              </p>
            </div>

            <div class="bg-[rgb(var(--color-surface))] p-4">
              <div class="flex items-center gap-2 text-[rgb(var(--color-text-secondary))]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2" />
                  <circle cx="9" cy="7" r="4" />
                  <path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" />
                </svg>

                <span class="text-xs font-medium">الأعضاء</span>
              </div>

              <p class="mt-2 text-sm font-bold text-[rgb(var(--color-text-primary))]">
                {{ $memberCount }}
              </p>
            </div>

            <div class="bg-[rgb(var(--color-surface))] p-4">
              <div class="flex items-center gap-2 text-[rgb(var(--color-text-secondary))]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <rect x="3" y="6" width="18" height="13" rx="2" />
                  <path d="M16 12h5M6 6V4h11v2" />
                </svg>

                <span class="text-xs font-medium">نوع الميزانية</span>
              </div>

              <p class="mt-2 text-sm font-bold text-[rgb(var(--color-text-primary))]">
                {{ $project->budget_type
                                    ? ($budgetTypeLabels[$project->budget_type] ?? $project->budget_type)
                                    : 'غير محدد' }}
              </p>
            </div>
          </div>
        </div>
      </section>

      <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- Main Column --}}
        <div class="space-y-6 xl:col-span-2">

          {{-- Project Details --}}
          <section class="gdfh-card overflow-hidden">
            <div class="flex items-center justify-between gap-4 border-b px-5 py-4 sm:px-6"
              style="border-color: rgb(var(--color-border));">
              <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg
                                           bg-[rgb(var(--color-copper-soft))]
                                           text-[rgb(var(--color-copper))]">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M4 19.5V5a2 2 0 012-2h8l6 6v10.5a1.5 1.5 0 01-1.5 1.5h-13A1.5 1.5 0 014 19.5z" />
                    <path d="M14 3v6h6" />
                  </svg>
                </div>

                <div>
                  <h2 class="text-base font-bold text-[rgb(var(--color-text-primary))]">
                    تفاصيل المشروع
                  </h2>

                  <p class="mt-0.5 text-xs text-[rgb(var(--color-text-secondary))]">
                    معلومات المشروع المالية والزمنية.
                  </p>
                </div>
              </div>

              <a href="{{ route('projects.edit', $project) }}" class="text-xs font-semibold text-[rgb(var(--color-copper))]
                                       transition hover:opacity-75">
                تعديل
              </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2">
              <div class="border-b p-5 sm:border-e" style="border-color: rgb(var(--color-border));">
                <p class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">
                  التصنيف
                </p>

                <p class="mt-2 text-sm font-bold text-[rgb(var(--color-text-primary))]">
                  {{ $project->category ?: 'غير محدد' }}
                </p>
              </div>

              <div class="border-b p-5" style="border-color: rgb(var(--color-border));">
                <p class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">
                  نوع الميزانية
                </p>

                <p class="mt-2 text-sm font-bold text-[rgb(var(--color-text-primary))]">
                  {{ $project->budget_type
                                        ? ($budgetTypeLabels[$project->budget_type] ?? $project->budget_type)
                                        : 'غير محدد' }}
                </p>
              </div>

              <div class="border-b p-5 sm:border-e" style="border-color: rgb(var(--color-border));">
                <p class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">
                  الحد الأدنى للميزانية
                </p>

                <p dir="ltr" class="mt-2 text-right text-sm font-bold text-[rgb(var(--color-text-primary))]">
                  @if ($budgetMin !== null)
                  {{ $budgetMin }} {{ strtoupper($project->currency) }}
                  @else
                  غير محدد
                  @endif
                </p>
              </div>

              <div class="border-b p-5" style="border-color: rgb(var(--color-border));">
                <p class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">
                  الحد الأعلى للميزانية
                </p>

                <p dir="ltr" class="mt-2 text-right text-sm font-bold text-[rgb(var(--color-text-primary))]">
                  @if ($budgetMax !== null)
                  {{ $budgetMax }} {{ strtoupper($project->currency) }}
                  @else
                  غير محدد
                  @endif
                </p>
              </div>

              <div class="p-5 sm:border-e" style="border-color: rgb(var(--color-border));">
                <p class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">
                  تاريخ البداية
                </p>

                <p class="mt-2 text-sm font-bold text-[rgb(var(--color-text-primary))]">
                  {{ $project->start_date
                                        ? $project->start_date->format('Y/m/d')
                                        : 'غير محدد' }}
                </p>
              </div>

              <div class="p-5">
                <p class="text-xs font-medium text-[rgb(var(--color-text-secondary))]">
                  الموعد النهائي
                </p>

                <p class="mt-2 text-sm font-bold text-[rgb(var(--color-text-primary))]">
                  {{ $project->deadline
                                        ? $project->deadline->format('Y/m/d')
                                        : 'غير محدد' }}
                </p>
              </div>
            </div>
          </section>

          {{-- Members --}}
          <section class="gdfh-card overflow-hidden">
            <div class="flex flex-col gap-4 border-b px-5 py-5
                                   sm:flex-row sm:items-center sm:justify-between sm:px-6"
              style="border-color: rgb(var(--color-border));">
              <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg
                                           bg-[rgb(var(--color-mineral-soft))]
                                           text-[rgb(var(--color-mineral))]">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" />
                  </svg>
                </div>

                <div>
                  <h2 class="text-base font-bold text-[rgb(var(--color-text-primary))]">
                    أعضاء المشروع
                  </h2>

                  <p class="mt-0.5 text-xs text-[rgb(var(--color-text-secondary))]">
                    أضف أعضاء المشروع وحدد صلاحياتهم وحالتهم.
                  </p>
                </div>
              </div>

              <span class="gdfh-badge w-fit bg-[rgb(var(--color-surface-soft))]
                                       text-[rgb(var(--color-text-secondary))]">
                {{ $memberCount }}
                {{ $memberCount === 1 ? 'عضو' : 'أعضاء' }}
              </span>
            </div>

            {{-- Add Member --}}
            <div class="border-b p-5 sm:p-6" style="
                                border-color: rgb(var(--color-border));
                                background-color: rgb(var(--color-surface-soft) / 0.45);
                            ">
              <div>
                <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
                  إضافة عضو جديد
                </h3>

                <p class="mt-1 text-xs leading-6 text-[rgb(var(--color-text-secondary))]">
                  أدخل رقم المستخدم وحدد دوره داخل المشروع.
                </p>
              </div>

              <form method="POST" action="{{ route('projects.members.store', $project) }}"
                class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-[1fr_1fr_auto]">
                @csrf

                <div>
                  <label for="user_id" class="mb-2 block text-xs font-semibold text-[rgb(var(--color-text-primary))]">
                    رقم المستخدم
                  </label>

                  <input id="user_id" name="user_id" type="number" min="1" value="{{ old('user_id') }}" required
                    placeholder="مثال: 15" dir="ltr" class="gdfh-input text-left">

                  @error('user_id')
                  <p class="mt-2 text-xs font-medium" style="color: rgb(var(--color-error));">
                    {{ $message }}
                  </p>
                  @enderror
                </div>

                <div>
                  <label for="role" class="mb-2 block text-xs font-semibold text-[rgb(var(--color-text-primary))]">
                    الدور
                  </label>

                  <select id="role" name="role" required class="gdfh-input">
                    <option value="project_manager" @selected(old('role')==='project_manager' )>
                      مدير مشروع
                    </option>

                    <option value="team_leader" @selected(old('role')==='team_leader' )>
                      قائد فريق
                    </option>

                    <option value="member" @selected(old('role', 'member' )==='member' )>
                      عضو
                    </option>

                    <option value="viewer" @selected(old('role')==='viewer' )>
                      مشاهد
                    </option>
                  </select>

                  @error('role')
                  <p class="mt-2 text-xs font-medium" style="color: rgb(var(--color-error));">
                    {{ $message }}
                  </p>
                  @enderror
                </div>

                <div class="flex items-end">
                  <button type="submit" class="gdfh-btn gdfh-btn-brand w-full lg:w-auto">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                      <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                    </svg>

                    إضافة عضو
                  </button>
                </div>
              </form>
            </div>

            {{-- Members List --}}
            <div class="p-5 sm:p-6">
              @forelse ($project->memberRecords as $memberRecord)
              <article class="mb-4 rounded-xl border p-4 last:mb-0 sm:p-5"
                style="border-color: rgb(var(--color-border));">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                  <div class="flex min-w-0 items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full
                                                       bg-[rgb(var(--color-mineral-soft))]
                                                       text-sm font-bold text-[rgb(var(--color-mineral))]">
                      {{ mb_strtoupper(mb_substr($memberRecord->user->name, 0, 1)) }}
                    </div>

                    <div class="min-w-0">
                      <div class="flex flex-wrap items-center gap-2">
                        <p class="truncate text-sm font-bold text-[rgb(var(--color-text-primary))]">
                          {{ $memberRecord->user->name }}
                        </p>

                        <span class="gdfh-badge bg-[rgb(var(--color-surface-soft))]
                                                               text-[rgb(var(--color-text-secondary))]">
                          {{ $memberStatusLabels[$memberRecord->status] ?? $memberRecord->status }}
                        </span>
                      </div>

                      <p dir="ltr" class="mt-1 truncate text-left text-xs text-[rgb(var(--color-text-secondary))]">
                        {{ $memberRecord->user->email }}
                      </p>

                      <p class="mt-1 text-[11px] text-[rgb(var(--color-text-secondary))]">
                        رقم المستخدم: {{ $memberRecord->user_id }}
                      </p>
                    </div>
                  </div>

                  <form method="POST" action="{{ route('projects.members.update', [$project, $memberRecord]) }}"
                    class="grid w-full grid-cols-1 gap-3 sm:grid-cols-3 xl:w-auto">
                    @csrf
                    @method('PATCH')

                    <select name="role" aria-label="دور العضو" class="gdfh-input min-w-[150px]">
                      <option value="project_manager" @selected($memberRecord->role === 'project_manager')
                        >
                        مدير مشروع
                      </option>

                      <option value="team_leader" @selected($memberRecord->role === 'team_leader')
                        >
                        قائد فريق
                      </option>

                      <option value="member" @selected($memberRecord->role === 'member')
                        >
                        عضو
                      </option>

                      <option value="viewer" @selected($memberRecord->role === 'viewer')
                        >
                        مشاهد
                      </option>
                    </select>

                    <select name="status" aria-label="حالة العضو" class="gdfh-input min-w-[150px]">
                      <option value="pending" @selected($memberRecord->status === 'pending')
                        >
                        بانتظار التفعيل
                      </option>

                      <option value="active" @selected($memberRecord->status === 'active')
                        >
                        نشط
                      </option>

                      <option value="suspended" @selected($memberRecord->status === 'suspended')
                        >
                        موقوف
                      </option>

                      <option value="left" @selected($memberRecord->status === 'left')
                        >
                        غادر
                      </option>
                    </select>

                    <button type="submit" class="gdfh-btn gdfh-btn-secondary">
                      حفظ التغييرات
                    </button>
                  </form>
                </div>

                <div class="mt-4 flex flex-col gap-3 border-t pt-4
                                               sm:flex-row sm:items-center sm:justify-between"
                  style="border-color: rgb(var(--color-border));">
                  <p class="text-xs text-[rgb(var(--color-text-secondary))]">
                    تاريخ الانضمام:
                    <span class="font-semibold text-[rgb(var(--color-text-primary))]">
                      {{ $memberRecord->joined_at
                                                    ? $memberRecord->joined_at->format('Y/m/d')
                                                    : 'غير محدد' }}
                    </span>
                  </p>

                  <form method="POST" action="{{ route('projects.members.destroy', [$project, $memberRecord]) }}"
                    onsubmit="return confirm('هل أنت متأكد من إزالة هذا العضو من المشروع؟');">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                      class="inline-flex items-center gap-1.5 text-xs font-semibold transition hover:opacity-75"
                      style="color: rgb(var(--color-error));">
                      <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" />
                        <path d="M10 11v5M14 11v5" />
                      </svg>

                      إزالة العضو
                    </button>
                  </form>
                </div>
              </article>
              @empty
              <div class="rounded-xl border border-dashed p-8 text-center"
                style="border-color: rgb(var(--color-border));">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl
                                               bg-[rgb(var(--color-surface-soft))]
                                               text-[rgb(var(--color-text-secondary))]">
                  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M19 8v6M16 11h6" />
                  </svg>
                </div>

                <h3 class="mt-4 text-sm font-bold text-[rgb(var(--color-text-primary))]">
                  لا يوجد أعضاء بعد
                </h3>

                <p class="mt-1 text-xs leading-6 text-[rgb(var(--color-text-secondary))]">
                  استخدم النموذج أعلاه لإضافة أول عضو إلى المشروع.
                </p>
              </div>
              @endforelse
            </div>
          </section>
        </div>

        {{-- Side Column --}}
        <aside class="space-y-6">

          {{-- Project Summary --}}
          <section class="gdfh-card overflow-hidden">
            <div class="border-b px-5 py-4" style="border-color: rgb(var(--color-border));">
              <h2 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
                ملخص المشروع
              </h2>
            </div>

            <div class="divide-y divide-[rgb(var(--color-border))]">
              <div class="flex items-center justify-between gap-4 px-5 py-4">
                <span class="text-xs text-[rgb(var(--color-text-secondary))]">
                  الحالة
                </span>

                <span class="text-xs font-bold text-[rgb(var(--color-text-primary))]">
                  {{ $statusLabels[$project->status] ?? $project->status }}
                </span>
              </div>

              <div class="flex items-center justify-between gap-4 px-5 py-4">
                <span class="text-xs text-[rgb(var(--color-text-secondary))]">
                  الظهور
                </span>

                <span class="text-xs font-bold text-[rgb(var(--color-text-primary))]">
                  {{ $visibilityLabels[$project->visibility] ?? $project->visibility }}
                </span>
              </div>

              <div class="flex items-center justify-between gap-4 px-5 py-4">
                <span class="text-xs text-[rgb(var(--color-text-secondary))]">
                  العملة
                </span>

                <span dir="ltr" class="text-xs font-bold text-[rgb(var(--color-text-primary))]">
                  {{ strtoupper($project->currency) }}
                </span>
              </div>

              <div class="flex items-center justify-between gap-4 px-5 py-4">
                <span class="text-xs text-[rgb(var(--color-text-secondary))]">
                  الأعضاء
                </span>

                <span class="text-xs font-bold text-[rgb(var(--color-text-primary))]">
                  {{ $memberCount }}
                </span>
              </div>

              <div class="flex items-center justify-between gap-4 px-5 py-4">
                <span class="text-xs text-[rgb(var(--color-text-secondary))]">
                  تاريخ الإنشاء
                </span>

                <span class="text-xs font-bold text-[rgb(var(--color-text-primary))]">
                  {{ $project->created_at->format('Y/m/d') }}
                </span>
              </div>
            </div>
          </section>

          {{-- Quick Actions --}}
          <section class="gdfh-card p-5">
            <h2 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
              إجراءات سريعة
            </h2>

            <p class="mt-1 text-xs leading-6 text-[rgb(var(--color-text-secondary))]">
              انتقل إلى أقسام إدارة المشروع.
            </p>

            <div class="mt-4 space-y-2">
              <a href="{{ route('projects.tasks.index', $project) }}" class="flex items-center justify-between gap-3 rounded-lg border p-3
                                       text-sm font-semibold text-[rgb(var(--color-text-primary))]
                                       transition hover:bg-[rgb(var(--color-surface-soft))]"
                style="border-color: rgb(var(--color-border));">
                <span class="flex items-center gap-2">
                  <svg class="h-4 w-4 text-[rgb(var(--color-copper))]" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="4" width="18" height="16" rx="2" />
                    <path d="M8 9h8M8 13h5" />
                  </svg>

                  إدارة المهام
                </span>

                <svg class="h-4 w-4 text-[rgb(var(--color-text-secondary))]" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="1.8">
                  <path d="M15 18l-6-6 6-6" />
                </svg>
              </a>

              <a href="{{ route('projects.reviews.index', $project) }}" class="flex items-center justify-between gap-3 rounded-lg border p-3
                                       text-sm font-semibold text-[rgb(var(--color-text-primary))]
                                       transition hover:bg-[rgb(var(--color-surface-soft))]"
                style="border-color: rgb(var(--color-border));">
                <span class="flex items-center gap-2">
                  <svg class="h-4 w-4 text-[rgb(var(--color-mineral))]" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.8">
                    <path d="M12 3l2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-2.9-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3z" />
                  </svg>

                  التقييمات
                </span>

                <svg class="h-4 w-4 text-[rgb(var(--color-text-secondary))]" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="1.8">
                  <path d="M15 18l-6-6 6-6" />
                </svg>
              </a>

              <a href="{{ route('projects.edit', $project) }}" class="flex items-center justify-between gap-3 rounded-lg border p-3
                                       text-sm font-semibold text-[rgb(var(--color-text-primary))]
                                       transition hover:bg-[rgb(var(--color-surface-soft))]"
                style="border-color: rgb(var(--color-border));">
                <span class="flex items-center gap-2">
                  <svg class="h-4 w-4 text-[rgb(var(--color-text-secondary))]" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.8">
                    <path d="M13.5 6.5l4 4M4 20l4.5-1 10-10a2.8 2.8 0 00-4-4l-10 10L4 20z" />
                  </svg>

                  تعديل المشروع
                </span>

                <svg class="h-4 w-4 text-[rgb(var(--color-text-secondary))]" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="1.8">
                  <path d="M15 18l-6-6 6-6" />
                </svg>
              </a>
            </div>
          </section>

          {{-- Danger Zone --}}
          <section class="overflow-hidden rounded-xl border" style="border-color: rgb(var(--color-error) / 0.35);">
            <div class="border-b px-5 py-4" style="
                                border-color: rgb(var(--color-error) / 0.25);
                                background-color: rgb(var(--color-error) / 0.06);
                            ">
              <div class="flex items-center gap-2">
                <svg class="h-4 w-4" style="color: rgb(var(--color-error));" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="1.8">
                  <path
                    d="M12 9v4m0 4h.01M10.3 3.8L2.6 17.1A2 2 0 004.3 20h15.4a2 2 0 001.7-2.9L13.7 3.8a2 2 0 00-3.4 0z" />
                </svg>

                <h2 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
                  منطقة الخطر
                </h2>
              </div>
            </div>

            <div class="bg-[rgb(var(--color-surface))] p-5">
              <p class="text-xs leading-6 text-[rgb(var(--color-text-secondary))]">
                حذف المشروع إجراء نهائي ولا يمكن التراجع عنه.
                سيتم حذف البيانات المرتبطة بالمشروع وفق قواعد النظام.
              </p>

              <form method="POST" action="{{ route('projects.destroy', $project) }}" class="mt-4"
                onsubmit="return confirm('هل أنت متأكد من حذف هذا المشروع؟ لا يمكن التراجع عن هذه العملية.');">
                @csrf
                @method('DELETE')

                <button type="submit" class="inline-flex min-h-[42px] w-full items-center justify-center gap-2
                                           rounded-lg border px-4 py-2.5 text-sm font-semibold transition
                                           hover:opacity-80" style="
                                        border-color: rgb(var(--color-error) / 0.40);
                                        background-color: rgb(var(--color-error) / 0.08);
                                        color: rgb(var(--color-error));
                                    ">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" />
                    <path d="M10 11v5M14 11v5" />
                  </svg>

                  حذف المشروع
                </button>
              </form>
            </div>
          </section>
        </aside>
      </div>
    </div>
  </div>
</x-app-layout>
