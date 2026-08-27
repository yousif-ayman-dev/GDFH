<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-[rgb(var(--color-text-primary))]">معرض أعمالي (Portfolio)</h1>
        <p class="text-xs text-[rgb(var(--color-text-secondary))] mt-1">
          أضف واعرض أفضل مشاريعك وأعمالك السابقة ليطلع عليها العملاء في بروفايلك الخاص.
        </p>
      </div>

      <div>
        <a href="{{ route('marketplace.freelancers.show', Auth::user()) }}" class="gdfh-btn gdfh-btn-secondary text-xs py-2.5 px-4 font-bold flex items-center gap-1.5">
          <span>معاينة بروفايلي للعملاء ←</span>
        </a>
      </div>
    </div>
  </x-slot>

  <div class="space-y-8 py-6">

    @if (session('success'))
    <div class="rounded-xl bg-emerald-500/10 border border-emerald-500/20 p-4 text-xs font-bold text-emerald-600 dark:text-emerald-400">
      {{ session('success') }}
    </div>
    @endif

    {{-- Freelancer Bio, Profile Info & PDF CV Upload Card --}}
    @php
      $profile = Auth::user()->freelancerProfile;
    @endphp
    <div class="gdfh-card p-6 space-y-6">
      <div class="flex items-center justify-between border-b border-[rgb(var(--color-border))] pb-4">
        <div>
          <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))] flex items-center gap-2">
            <svg class="h-4 w-4 text-[rgb(var(--color-copper))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
            <span>النبذة الشخصية والسيرة الذاتية (Bio & PDF CV)</span>
          </h3>
          <p class="text-[11px] text-[rgb(var(--color-text-secondary))] mt-0.5">اكتب عن نفسك وعن خبراتك وارفع ملف الـ CV ليطلع عليها العملاء عند التوظيف.</p>
        </div>

        @if ($profile && $profile->cv_path)
        <a href="{{ asset('storage/' . $profile->cv_path) }}" target="_blank" class="gdfh-btn gdfh-btn-secondary text-xs py-2 px-3 flex items-center gap-1.5 shadow-sm">
          <svg class="h-4 w-4 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
          <span>استعراض الـ CV المرفوع (PDF)</span>
        </a>
        @endif
      </div>

      <form method="POST" action="{{ route('portfolio.profile.update') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div>
            <x-input-label for="title" value="المسمى الوظيفي / الاختصاص" class="text-xs font-bold" />
            <input type="text" name="title" id="title" value="{{ old('title', $profile?->title) }}" placeholder="مثال: مطور فل ستاك / مهندس معماري..." class="mt-1 w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:ring-2 focus:ring-[rgb(var(--color-copper))]">
          </div>

          <div>
            <x-input-label for="hourly_rate" value="سعر الساعة الإرشادي ($)" class="text-xs font-bold" />
            <input type="number" step="0.5" name="hourly_rate" id="hourly_rate" value="{{ old('hourly_rate', $profile?->hourly_rate ?? 25) }}" placeholder="25" class="mt-1 w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:ring-2 focus:ring-[rgb(var(--color-copper))]">
          </div>

          <div>
            <x-input-label for="location" value="البلد / المدينة" class="text-xs font-bold" />
            <input type="text" name="location" id="location" value="{{ old('location', $profile?->location) }}" placeholder="مثال: الرياض، السعودية / عمان، الأردن..." class="mt-1 w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:ring-2 focus:ring-[rgb(var(--color-copper))]">
          </div>
        </div>

        <div>
          <x-input-label for="bio" value="اكتب نبذة عن نفسك وعن خبراتك وسنوات العمل (About Me)" class="text-xs font-bold" />
          <textarea name="bio" id="bio" rows="3" placeholder="تحدث عن أبرز مهاراتك، مشاريعك السابقة، وما الذي يميزك في تقديم الخدمات للعملاء..." class="mt-1 w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:ring-2 focus:ring-[rgb(var(--color-copper))]">{{ old('bio', $profile?->bio ?? Auth::user()->bio) }}</textarea>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <x-input-label for="skills" value="المهارات والتقنيات (مفصولة بفواصل)" class="text-xs font-bold" />
            <input type="text" name="skills" id="skills" value="{{ is_array($profile?->skills) ? implode(', ', $profile->skills) : '' }}" placeholder="Laravel, Vue.js, UI/UX, AutoCAD" class="mt-1 w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:ring-2 focus:ring-[rgb(var(--color-copper))]">
          </div>

          <div>
            <x-input-label for="cv_file" value="رفع ملف السيرة الذاتية (CV بصيغة PDF)" class="text-xs font-bold" />
            <input type="file" name="cv_file" id="cv_file" accept=".pdf,application/pdf" class="mt-1 w-full text-xs text-[rgb(var(--color-text-secondary))] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[rgb(var(--color-copper-soft))] file:text-[rgb(var(--color-copper))] hover:file:bg-amber-500/20">
          </div>
        </div>

        <div class="pt-2">
          <button type="submit" class="gdfh-btn gdfh-btn-brand text-xs py-2.5 px-6 font-bold">
            حفظ النبذة والسيرة الذاتية
          </button>
        </div>
      </form>
    </div>

    {{-- Add New Portfolio Form Card --}}
    <div class="gdfh-card p-6 space-y-6">
      <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))] flex items-center gap-2">
        <svg class="h-4 w-4 text-[rgb(var(--color-copper))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        <span>إضافة عمل جديد لمعرض الأعمال</span>
      </h3>

      <form method="POST" action="{{ route('portfolio.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <x-input-label for="title" value="عنوان العمل / المشروع *" class="text-xs font-bold" />
            <input type="text" name="title" id="title" required placeholder="مثال: تصميم ديكور فيلا مودرن / نظام متجر إلكتروني..." class="mt-1 w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:ring-2 focus:ring-[rgb(var(--color-copper))]">
          </div>

          <div>
            <x-input-label for="category" value="التصنيف / المجال" class="text-xs font-bold" />
            <input type="text" name="category" id="category" placeholder="مثال: تصميم داخلي / تطوير برمجيات / تسويق رقمي..." class="mt-1 w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:ring-2 focus:ring-[rgb(var(--color-copper))]">
          </div>
        </div>

        <div>
          <x-input-label for="description" value="وصف العمل والملاحظات" class="text-xs font-bold" />
          <textarea name="description" id="description" rows="3" placeholder="اكتب تفاصيل المشروع، الأدوار التي قمت بها، النظريات أو الملاحظات التي استخدمتها..." class="mt-1 w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:ring-2 focus:ring-[rgb(var(--color-copper))]"></textarea>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div>
            <x-input-label for="skills" value="المهارات والأدوات المستعملة (مفصولة بفواصل)" class="text-xs font-bold" />
            <input type="text" name="skills" id="skills" placeholder="مثال: PHP, Laravel, 3D Max, AutoCad" class="mt-1 w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:ring-2 focus:ring-[rgb(var(--color-copper))]">
          </div>

          <div>
            <x-input-label for="project_url" value="رابط المعاينة المباشرة (إن وجد)" class="text-xs font-bold" />
            <input type="url" name="project_url" id="project_url" placeholder="https://example.com" class="mt-1 w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:ring-2 focus:ring-[rgb(var(--color-copper))]">
          </div>

          <div>
            <x-input-label for="image" value="صورة غلاف العمل / المخرج" class="text-xs font-bold" />
            <input type="file" name="image" id="image" accept="image/*" class="mt-1 w-full text-xs text-[rgb(var(--color-text-secondary))] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[rgb(var(--color-copper-soft))] file:text-[rgb(var(--color-copper))] hover:file:bg-amber-500/20">
          </div>
        </div>

        <div class="pt-2">
          <button type="submit" class="gdfh-btn gdfh-btn-brand text-xs py-2.5 px-6 font-bold">
            حفظ وإضافة العمل
          </button>
        </div>
      </form>
    </div>

    {{-- Portfolio Items Grid --}}
    <div class="space-y-4">
      <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">الأعمال المضافة حتى الآن ({{ $portfolioItems->total() }})</h3>

      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($portfolioItems as $item)
        <div class="gdfh-card overflow-hidden flex flex-col justify-between shadow-sm border border-[rgb(var(--color-border))]">
          <div>
            @if ($item->image_path)
            <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->title }}" class="h-44 w-full object-cover">
            @else
            <div class="h-44 w-full bg-[rgb(var(--color-copper-soft))] flex items-center justify-center text-[rgb(var(--color-copper))]">
              <svg class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
            </div>
            @endif

            <div class="p-5 space-y-3">
              @if ($item->category)
              <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))]">
                {{ $item->category }}
              </span>
              @endif

              <h4 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">{{ $item->title }}</h4>

              @if ($item->description)
              <p class="text-xs text-[rgb(var(--color-text-secondary))] leading-relaxed line-clamp-3">
                {{ $item->description }}
              </p>
              @endif

              @if (! empty($item->skills))
              <div class="flex flex-wrap gap-1 pt-1">
                @foreach ((array)$item->skills as $skill)
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))] border border-[rgb(var(--color-border))]">
                  {{ $skill }}
                </span>
                @endforeach
              </div>
              @endif
            </div>
          </div>

          <div class="p-4 border-t border-[rgb(var(--color-border))] flex items-center justify-between text-xs bg-[rgb(var(--color-surface-soft)/0.3)]">
            @if ($item->project_url)
            <a href="{{ $item->project_url }}" target="_blank" rel="noopener noreferrer" class="text-[rgb(var(--color-copper))] font-bold hover:underline flex items-center gap-1">
              <span>معاينة الرابط ↗</span>
            </a>
            @else
            <span></span>
            @endif

            <form method="POST" action="{{ route('portfolio.destroy', $item) }}" onsubmit="return confirm('حذف هذا العمل من معرض أعمالك؟')">
              @csrf
              @method('DELETE')
              <button type="submit" class="text-red-400 hover:text-red-600 font-bold text-xs">
                حذف
              </button>
            </form>
          </div>
        </div>
        @empty
        <div class="p-12 text-center text-xs text-[rgb(var(--color-text-secondary))] col-span-full gdfh-card">
          لم تقم بإضافة أعمال إلى معرض أعمالك حتى الآن. استخدم النموذج أعلاه لإضافة أول مشروع.
        </div>
        @endforelse
      </div>

      <div class="pt-4">
        {{ $portfolioItems->links() }}
      </div>
    </div>

  </div>
</x-app-layout>
