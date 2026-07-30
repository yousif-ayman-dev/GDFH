<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between gap-4">
      <div>
        <p class="text-xs font-semibold text-[rgb(var(--color-copper))]">
          إدارة المشاريع
        </p>

        <h2 class="mt-1 text-xl font-bold text-[rgb(var(--color-text-primary))]">
          تعديل المشروع
        </h2>
      </div>

      <a href="{{ route('projects.show', $project) }}"
        class="hidden sm:inline-flex items-center gap-2 text-sm font-medium text-[rgb(var(--color-text-secondary))] transition hover:text-[rgb(var(--color-text-primary))]">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6" />
        </svg>

        العودة إلى المشروع
      </a>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
    <div class="mx-auto max-w-5xl">

      {{-- Intro --}}
      <section class="mb-8">
        <div class="flex items-start gap-4">
          <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl
                      bg-[rgb(var(--color-copper-soft))]
                      text-[rgb(var(--color-copper))]">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9" />
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M16.5 3.5a2.1 2.1 0 013 3L8 18l-4 1 1-4L16.5 3.5z" />
            </svg>
          </div>

          <div>
            <p class="text-xs font-semibold text-[rgb(var(--color-copper))]">
              إعدادات المشروع
            </p>

            <h1 class="mt-1 text-2xl font-bold tracking-tight text-[rgb(var(--color-text-primary))] sm:text-3xl">
              تعديل {{ $project->title }}
            </h1>

            <p class="mt-2 max-w-2xl text-sm leading-7 text-[rgb(var(--color-text-secondary))]">
              حدّث المعلومات الأساسية للمشروع وخصائص الظهور والميزانية والجدول الزمني.
              لن يتم حفظ أي تغييرات حتى تضغط على زر حفظ التغييرات.
            </p>
          </div>
        </div>
      </section>

      {{-- Global validation errors --}}
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

      <form method="POST" action="{{ route('projects.update', $project) }}">
        @csrf
        @method('PUT')

        <div class="space-y-6">

          {{-- Basic information --}}
          <section class="gdfh-card overflow-hidden">
            <div class="flex items-center gap-3 border-b px-5 py-4 sm:px-6"
              style="border-color: rgb(var(--color-border));">

              <div class="flex h-9 w-9 items-center justify-center rounded-lg
                          bg-[rgb(var(--color-copper-soft))]
                          text-[rgb(var(--color-copper))]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 19.5V5a2 2 0 012-2h8l6 6v10.5a1.5 1.5 0 01-1.5 1.5h-13A1.5 1.5 0 014 19.5z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 3v6h6" />
                </svg>
              </div>

              <div>
                <h2 class="text-base font-bold text-[rgb(var(--color-text-primary))]">
                  معلومات المشروع
                </h2>

                <p class="mt-0.5 text-xs text-[rgb(var(--color-text-secondary))]">
                  عدّل المعلومات الأساسية التي تعرّف مشروعك.
                </p>
              </div>
            </div>

            <div class="space-y-6 p-5 sm:p-6">

              {{-- Title --}}
              <div>
                <label for="title" class="mb-2 block text-sm font-semibold text-[rgb(var(--color-text-primary))]">
                  اسم المشروع
                  <span style="color: rgb(var(--color-error));">*</span>
                </label>

                <input id="title" name="title" type="text" value="{{ old('title', $project->title) }}" required
                  autofocus autocomplete="off" placeholder="مثال: تطوير منصة إدارة المشاريع" class="gdfh-input">

                @error('title')
                <p class="mt-2 text-xs font-medium" style="color: rgb(var(--color-error));">
                  {{ $message }}
                </p>
                @enderror
              </div>

              {{-- Description --}}
              <div>
                <div class="mb-2 flex items-center justify-between gap-3">
                  <label for="description" class="block text-sm font-semibold text-[rgb(var(--color-text-primary))]">
                    وصف المشروع
                    <span style="color: rgb(var(--color-error));">*</span>
                  </label>

                  <span class="hidden text-xs text-[rgb(var(--color-text-secondary))] sm:inline">
                    اكتب وصفًا واضحًا ومختصرًا
                  </span>
                </div>

                <textarea id="description" name="description" rows="6" required
                  placeholder="اشرح فكرة المشروع، أهدافه، والنتيجة التي تريد الوصول إليها..."
                  class="gdfh-input min-h-[150px] resize-y">{{ old('description', $project->description) }}</textarea>

                @error('description')
                <p class="mt-2 text-xs font-medium" style="color: rgb(var(--color-error));">
                  {{ $message }}
                </p>
                @enderror
              </div>

              {{-- Category --}}
              <div>
                <label for="category" class="mb-2 block text-sm font-semibold text-[rgb(var(--color-text-primary))]">
                  التصنيف
                </label>

                <input id="category" name="category" type="text" value="{{ old('category', $project->category) }}"
                  autocomplete="off" placeholder="مثال: Web Development" class="gdfh-input">

                <p class="mt-2 text-xs text-[rgb(var(--color-text-secondary))]">
                  يساعد التصنيف في تنظيم المشروع والعثور عليه بسهولة.
                </p>

                @error('category')
                <p class="mt-2 text-xs font-medium" style="color: rgb(var(--color-error));">
                  {{ $message }}
                </p>
                @enderror
              </div>
            </div>
          </section>

          {{-- Visibility --}}
          <section class="gdfh-card overflow-hidden">
            <div class="flex items-center gap-3 border-b px-5 py-4 sm:px-6"
              style="border-color: rgb(var(--color-border));">

              <div class="flex h-9 w-9 items-center justify-center rounded-lg
                          bg-[rgb(var(--color-copper-soft))]
                          text-[rgb(var(--color-copper))]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z" />
                  <circle cx="12" cy="12" r="2.5" />
                </svg>
              </div>

              <div>
                <h2 class="text-base font-bold text-[rgb(var(--color-text-primary))]">
                  ظهور المشروع
                </h2>

                <p class="mt-0.5 text-xs text-[rgb(var(--color-text-secondary))]">
                  حدّد من يمكنه الوصول إلى المشروع.
                </p>
              </div>
            </div>

            <div class="p-5 sm:p-6">
              <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                {{-- Private --}}
                <label class="group relative cursor-pointer">
                  <input type="radio" name="visibility" value="private" class="peer sr-only" @checked(old('visibility',
                    $project->visibility) === 'private')
                  >

                  <div class="h-full rounded-xl border p-4 transition
                              peer-checked:border-[rgb(var(--color-copper))]
                              peer-checked:bg-[rgb(var(--color-copper-soft))]
                              hover:border-[rgb(var(--color-copper))]" style="border-color: rgb(var(--color-border));">

                    <div class="flex items-start gap-3">
                      <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg
                                  bg-[rgb(var(--color-surface-soft))]
                                  text-[rgb(var(--color-text-secondary))]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                          <rect x="5" y="10" width="14" height="10" rx="2" />
                          <path stroke-linecap="round" d="M8 10V7a4 4 0 018 0v3" />
                        </svg>
                      </div>

                      <div>
                        <p class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
                          خاص
                        </p>

                        <p class="mt-1 text-xs leading-6 text-[rgb(var(--color-text-secondary))]">
                          المشروع متاح لك وللأعضاء والفرق المرتبطة به فقط.
                        </p>
                      </div>
                    </div>
                  </div>
                </label>

                {{-- Marketplace --}}
                <label class="group relative cursor-pointer">
                  <input type="radio" name="visibility" value="marketplace" class="peer sr-only"
                    @checked(old('visibility', $project->visibility) === 'marketplace')
                  >

                  <div class="h-full rounded-xl border p-4 transition
                              peer-checked:border-[rgb(var(--color-copper))]
                              peer-checked:bg-[rgb(var(--color-copper-soft))]
                              hover:border-[rgb(var(--color-copper))]" style="border-color: rgb(var(--color-border));">

                    <div class="flex items-start gap-3">
                      <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg
                                  bg-[rgb(var(--color-surface-soft))]
                                  text-[rgb(var(--color-text-secondary))]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                          <circle cx="12" cy="12" r="9" />
                          <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 12h18M12 3a15 15 0 010 18M12 3a15 15 0 000 18" />
                        </svg>
                      </div>

                      <div>
                        <p class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
                          سوق المشاريع
                        </p>

                        <p class="mt-1 text-xs leading-6 text-[rgb(var(--color-text-secondary))]">
                          المشروع قابل للظهور في سوق المشاريع وفق آلية المنصة.
                        </p>
                      </div>
                    </div>
                  </div>
                </label>
              </div>

              @error('visibility')
              <p class="mt-3 text-xs font-medium" style="color: rgb(var(--color-error));">
                {{ $message }}
              </p>
              @enderror
            </div>
          </section>

          {{-- Budget --}}
          <section class="gdfh-card overflow-hidden">
            <div class="flex items-center gap-3 border-b px-5 py-4 sm:px-6"
              style="border-color: rgb(var(--color-border));">

              <div class="flex h-9 w-9 items-center justify-center rounded-lg
                          bg-[rgb(var(--color-copper-soft))]
                          text-[rgb(var(--color-copper))]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <rect x="3" y="6" width="18" height="13" rx="2" />
                  <path stroke-linecap="round" d="M16 12h5" />
                  <circle cx="16" cy="12" r=".5" fill="currentColor" />
                  <path stroke-linecap="round" d="M6 6V4h11v2" />
                </svg>
              </div>

              <div>
                <h2 class="text-base font-bold text-[rgb(var(--color-text-primary))]">
                  الميزانية
                </h2>

                <p class="mt-0.5 text-xs text-[rgb(var(--color-text-secondary))]">
                  حدّث نوع الميزانية والقيم المالية الخاصة بالمشروع.
                </p>
              </div>
            </div>

            <div class="space-y-6 p-5 sm:p-6">

              <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <div>
                  <label for="budget_type"
                    class="mb-2 block text-sm font-semibold text-[rgb(var(--color-text-primary))]">
                    نوع الميزانية
                  </label>

                  <select id="budget_type" name="budget_type" class="gdfh-input">
                    <option value="" @selected(old('budget_type', $project->budget_type) === null || old('budget_type',
                      $project->budget_type) === '')
                      >
                      بدون ميزانية محددة
                    </option>

                    <option value="fixed" @selected(old('budget_type', $project->budget_type) === 'fixed')
                      >
                      ميزانية ثابتة
                    </option>

                    <option value="hourly" @selected(old('budget_type', $project->budget_type) === 'hourly')
                      >
                      بالساعة
                    </option>
                  </select>

                  @error('budget_type')
                  <p class="mt-2 text-xs font-medium" style="color: rgb(var(--color-error));">
                    {{ $message }}
                  </p>
                  @enderror
                </div>

                <div>
                  <label for="currency" class="mb-2 block text-sm font-semibold text-[rgb(var(--color-text-primary))]">
                    العملة
                  </label>

                  <input id="currency" name="currency" type="text" maxlength="3"
                    value="{{ old('currency', $project->currency) }}" required autocomplete="off" dir="ltr"
                    placeholder="USD" class="gdfh-input uppercase">

                  <p class="mt-2 text-xs text-[rgb(var(--color-text-secondary))]">
                    استخدم رمز العملة المكوّن من 3 أحرف مثل USD.
                  </p>

                  @error('currency')
                  <p class="mt-2 text-xs font-medium" style="color: rgb(var(--color-error));">
                    {{ $message }}
                  </p>
                  @enderror
                </div>
              </div>

              <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <div>
                  <label for="budget_min"
                    class="mb-2 block text-sm font-semibold text-[rgb(var(--color-text-primary))]">
                    الحد الأدنى للميزانية
                  </label>

                  <input id="budget_min" name="budget_min" type="number" min="0" step="0.01"
                    value="{{ old('budget_min', $project->budget_min) }}" placeholder="0.00" dir="ltr"
                    class="gdfh-input text-left">

                  @error('budget_min')
                  <p class="mt-2 text-xs font-medium" style="color: rgb(var(--color-error));">
                    {{ $message }}
                  </p>
                  @enderror
                </div>

                <div>
                  <label for="budget_max"
                    class="mb-2 block text-sm font-semibold text-[rgb(var(--color-text-primary))]">
                    الحد الأعلى للميزانية
                  </label>

                  <input id="budget_max" name="budget_max" type="number" min="0" step="0.01"
                    value="{{ old('budget_max', $project->budget_max) }}" placeholder="0.00" dir="ltr"
                    class="gdfh-input text-left">

                  @error('budget_max')
                  <p class="mt-2 text-xs font-medium" style="color: rgb(var(--color-error));">
                    {{ $message }}
                  </p>
                  @enderror
                </div>
              </div>
            </div>
          </section>

          {{-- Timeline --}}
          <section class="gdfh-card overflow-hidden">
            <div class="flex items-center gap-3 border-b px-5 py-4 sm:px-6"
              style="border-color: rgb(var(--color-border));">

              <div class="flex h-9 w-9 items-center justify-center rounded-lg
                          bg-[rgb(var(--color-copper-soft))]
                          text-[rgb(var(--color-copper))]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <rect x="3" y="5" width="18" height="16" rx="2" />
                  <path stroke-linecap="round" d="M8 3v4M16 3v4M3 10h18" />
                </svg>
              </div>

              <div>
                <h2 class="text-base font-bold text-[rgb(var(--color-text-primary))]">
                  الجدول الزمني
                </h2>

                <p class="mt-0.5 text-xs text-[rgb(var(--color-text-secondary))]">
                  عدّل تاريخ البداية والموعد النهائي للمشروع.
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-6 p-5 sm:p-6 md:grid-cols-2">

              <div>
                <label for="start_date" class="mb-2 block text-sm font-semibold text-[rgb(var(--color-text-primary))]">
                  تاريخ البداية
                </label>

                <input id="start_date" name="start_date" type="date"
                  value="{{ old('start_date', $project->start_date ? $project->start_date->format('Y-m-d') : '') }}"
                  class="gdfh-input">

                @error('start_date')
                <p class="mt-2 text-xs font-medium" style="color: rgb(var(--color-error));">
                  {{ $message }}
                </p>
                @enderror
              </div>

              <div>
                <label for="deadline" class="mb-2 block text-sm font-semibold text-[rgb(var(--color-text-primary))]">
                  الموعد النهائي
                </label>

                <input id="deadline" name="deadline" type="date"
                  value="{{ old('deadline', $project->deadline ? $project->deadline->format('Y-m-d') : '') }}"
                  class="gdfh-input">

                @error('deadline')
                <p class="mt-2 text-xs font-medium" style="color: rgb(var(--color-error));">
                  {{ $message }}
                </p>
                @enderror
              </div>
            </div>
          </section>

          {{-- Submit --}}
          <section class="gdfh-card p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

              <div>
                <p class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
                  حفظ تعديلات المشروع
                </p>

                <p class="mt-1 text-xs leading-6 text-[rgb(var(--color-text-secondary))]">
                  راجع البيانات قبل الحفظ. ستظهر التغييرات مباشرة في تفاصيل المشروع بعد نجاح العملية.
                </p>
              </div>

              <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">

                <a href="{{ route('projects.show', $project) }}" class="gdfh-btn gdfh-btn-secondary">
                  إلغاء
                </a>

                <button type="submit" class="gdfh-btn gdfh-btn-brand">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12l4 4L19 6" />
                  </svg>

                  حفظ التغييرات
                </button>
              </div>
            </div>
          </section>

        </div>
      </form>
    </div>
  </div>
</x-app-layout>
