<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between gap-4">
      <div>
        <p class="text-xs font-semibold text-[rgb(var(--color-copper))]">
          إدارة الفرق
        </p>

        <h2 class="mt-1 text-xl font-bold text-[rgb(var(--color-text-primary))]">
          إنشاء فريق جديد
        </h2>
      </div>

      <a href="{{ route('teams.index') }}"
        class="hidden sm:inline-flex items-center gap-2 text-sm font-medium text-[rgb(var(--color-text-secondary))] transition hover:text-[rgb(var(--color-text-primary))]">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6" />
        </svg>

        العودة للفرق
      </a>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
    <div class="mx-auto max-w-5xl">
      <section class="mb-8">
        <div class="flex items-start gap-4">
          <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))]">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
            </svg>
          </div>

          <div>
            <p class="text-xs font-semibold text-[rgb(var(--color-copper))]">
              فريق جديد
            </p>

            <h1 class="mt-1 text-2xl font-bold tracking-tight text-[rgb(var(--color-text-primary))] sm:text-3xl">
              ابدأ فريقك من هنا
            </h1>

            <p class="mt-2 max-w-2xl text-sm leading-7 text-[rgb(var(--color-text-secondary))]">
              املأ المعلومات الأساسية للفريق وحدد نوعه وظهوره، ثم احفظه ليصبح متاحًا داخل مساحة العمل.
            </p>
          </div>
        </div>
      </section>

      @if ($errors->any())
      <div class="mb-6 flex items-start gap-3 rounded-xl border border-[rgb(var(--color-error)/0.30)] bg-[rgb(var(--color-error)/0.08)] p-4">
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

      <form method="POST" action="{{ route('teams.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="space-y-6">
          <section class="gdfh-card overflow-hidden">
            <div class="flex items-center gap-3 border-b border-[rgb(var(--color-border))] px-5 py-4 sm:px-6">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 19.5V5a2 2 0 012-2h8l6 6v10.5a1.5 1.5 0 01-1.5 1.5h-13A1.5 1.5 0 014 19.5z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 3v6h6" />
                </svg>
              </div>

              <div>
                <h2 class="text-base font-bold text-[rgb(var(--color-text-primary))]">
                  معلومات الفريق
                </h2>

                <p class="mt-0.5 text-xs text-[rgb(var(--color-text-secondary))]">
                  المعلومات الأساسية التي تعرّف الفريق وتحدد دوره داخل العمل.
                </p>
              </div>
            </div>

            <div class="space-y-6 p-5 sm:p-6">
              <div>
                <label for="name" class="mb-2 block text-sm font-semibold text-[rgb(var(--color-text-primary))]">
                  اسم الفريق
                  <span class="text-[rgb(var(--color-error))]">*</span>
                </label>

                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                  autocomplete="off" placeholder="مثال: فريق التطوير" class="gdfh-input">

                @error('name')
                <p class="mt-2 text-xs font-medium text-[rgb(var(--color-error))]">
                  {{ $message }}
                </p>
                @enderror
              </div>

              <div>
                <div class="mb-2 flex items-center justify-between gap-3">
                  <label for="description" class="block text-sm font-semibold text-[rgb(var(--color-text-primary))]">
                    وصف الفريق
                  </label>

                  <span class="hidden text-xs text-[rgb(var(--color-text-secondary))] sm:inline">
                    أضف وصفًا واضحًا ومختصرًا
                  </span>
                </div>

                <textarea id="description" name="description" rows="6" placeholder="اشرح دور الفريق، مهامه، أو طريقة عمله..."
                  class="gdfh-input min-h-[150px] resize-y">{{ old('description') }}</textarea>

                @error('description')
                <p class="mt-2 text-xs font-medium text-[rgb(var(--color-error))]">
                  {{ $message }}
                </p>
                @enderror
              </div>

              <div class="rounded-2xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface-soft))] p-4">
                <label for="logo" class="mb-2 block text-sm font-semibold text-[rgb(var(--color-text-primary))]">
                  شعار الفريق
                </label>

                <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif"
                  class="block w-full text-sm text-[rgb(var(--color-text-secondary))] file:mr-4 file:rounded-lg file:border-0 file:bg-[rgb(var(--color-copper-soft))] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[rgb(var(--color-copper))] hover:file:bg-[rgb(var(--color-copper-soft))]">

                <p class="mt-2 text-xs leading-6 text-[rgb(var(--color-text-secondary))]">
                  اختياري. يُقبل JPG، PNG، WebP أو GIF حتى 2 ميجابايت.
                </p>

                @error('logo')
                <p class="mt-2 text-xs font-medium text-[rgb(var(--color-error))]">
                  {{ $message }}
                </p>
                @enderror
              </div>
            </div>
          </section>

          <section class="gdfh-card overflow-hidden">
            <div class="flex items-center gap-3 border-b border-[rgb(var(--color-border))] px-5 py-4 sm:px-6">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 7h16M7 12h10M10 17h4" />
                </svg>
              </div>

              <div>
                <h2 class="text-base font-bold text-[rgb(var(--color-text-primary))]">
                  إعدادات الفريق
                </h2>

                <p class="mt-0.5 text-xs text-[rgb(var(--color-text-secondary))]">
                  حدّد نوع الفريق ودرجة ظهوره داخل النظام.
                </p>
              </div>
            </div>

            <div class="grid gap-6 p-5 sm:grid-cols-2 sm:p-6">
              <div>
                <label for="type" class="mb-2 block text-sm font-semibold text-[rgb(var(--color-text-primary))]">
                  نوع الفريق
                </label>

                <select id="type" name="type" class="gdfh-input">
                  <option value="permanent" {{ old('type', 'permanent') === 'permanent' ? 'selected' : '' }}>دائم</option>
                  <option value="project_based" {{ old('type') === 'project_based' ? 'selected' : '' }}>قائم على مشروع</option>
                </select>

                @error('type')
                <p class="mt-2 text-xs font-medium text-[rgb(var(--color-error))]">
                  {{ $message }}
                </p>
                @enderror
              </div>

              <div>
                <label for="visibility" class="mb-2 block text-sm font-semibold text-[rgb(var(--color-text-primary))]">
                  الظهور
                </label>

                <select id="visibility" name="visibility" class="gdfh-input">
                  <option value="private" {{ old('visibility', 'private') === 'private' ? 'selected' : '' }}>خاص</option>
                  <option value="public" {{ old('visibility') === 'public' ? 'selected' : '' }}>عام</option>
                </select>

                @error('visibility')
                <p class="mt-2 text-xs font-medium text-[rgb(var(--color-error))]">
                  {{ $message }}
                </p>
                @enderror
              </div>
            </div>
          </section>

          <div class="flex flex-col-reverse gap-3 border-t border-[rgb(var(--color-border))] pt-6 sm:flex-row sm:items-center sm:justify-end">
            <a href="{{ route('teams.index') }}"
              class="inline-flex items-center justify-center gap-2 rounded-lg border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] px-4 py-2.5 text-sm font-semibold text-[rgb(var(--color-text-primary))] transition hover:bg-[rgb(var(--color-surface-soft))]">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6" />
              </svg>

              <span>إلغاء</span>
            </a>

            <button type="submit" class="gdfh-btn gdfh-btn-brand">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
              </svg>

              <span>إنشاء الفريق</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</x-app-layout>
