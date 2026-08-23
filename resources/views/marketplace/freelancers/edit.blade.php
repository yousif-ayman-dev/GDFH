<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[rgb(var(--color-text-primary))]">
          إعدادات بروفايل المستقل (Edit Freelancer Profile)
        </h2>
        <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">
          تحديث معلوماتك المهنية وسعر الساعة والتوفر في دليل المستقلين.
        </p>
      </div>

      <a href="{{ route('marketplace.freelancers.show', Auth::user()) }}" class="gdfh-btn gdfh-btn-secondary text-xs py-1.5 px-3">
        ← معاينة بروفايلي
      </a>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-6">
    <div class="mx-auto max-w-3xl space-y-6">

      <div class="gdfh-card p-6 sm:p-8 space-y-6">
        <form method="POST" action="{{ route('marketplace.freelancers.profile.update') }}" class="space-y-5 text-xs">
          @csrf
          @method('PUT')

          <div>
            <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">المسمى الوظيفي واللقب المهني *</label>
            <input type="text" name="title" value="{{ old('title', $user->freelancerProfile?->title ?? 'مطور ومهندس برمجيات') }}" required placeholder="مثال: مهندس برمجيات full-stack متقدم..." class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:border-[rgb(var(--color-copper))] focus:outline-none">
            <x-input-error class="mt-1" :messages="$errors->get('title')" />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">سعر الساعة ($/hr) *</label>
              <input type="number" step="0.50" min="0" name="hourly_rate" value="{{ old('hourly_rate', $user->freelancerProfile?->hourly_rate ?? 30.00) }}" required class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:border-[rgb(var(--color-copper))] focus:outline-none">
              <x-input-error class="mt-1" :messages="$errors->get('hourly_rate')" />
            </div>

            <div>
              <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">حالة التوفر *</label>
              <select name="availability" required class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:border-[rgb(var(--color-copper))] focus:outline-none">
                <option value="available" {{ old('availability', $user->freelancerProfile?->availability) === 'available' ? 'selected' : '' }}>متاح للعمل (Available)</option>
                <option value="busy" {{ old('availability', $user->freelancerProfile?->availability) === 'busy' ? 'selected' : '' }}>مشغول حالياً (Busy)</option>
                <option value="offline" {{ old('availability', $user->freelancerProfile?->availability) === 'offline' ? 'selected' : '' }}>غير متاح (Offline)</option>
              </select>
              <x-input-error class="mt-1" :messages="$errors->get('availability')" />
            </div>

            <div>
              <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">البلد / المدينة</label>
              <input type="text" name="location" value="{{ old('location', $user->freelancerProfile?->location) }}" placeholder="مثال: الرياض، المملكة العربية السعودية" class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:border-[rgb(var(--color-copper))] focus:outline-none">
              <x-input-error class="mt-1" :messages="$errors->get('location')" />
            </div>
          </div>

          <div>
            <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">نبذة عن خبراتك ومهاراتك</label>
            <textarea name="bio" rows="5" placeholder="اكتب وصفاً مهنياً مفصلاً عن مشاريعك السابقة وخبراتك البرمجية..." class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:border-[rgb(var(--color-copper))] focus:outline-none">{{ old('bio', $user->freelancerProfile?->bio ?? $user->bio) }}</textarea>
            <x-input-error class="mt-1" :messages="$errors->get('bio')" />
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-[rgb(var(--color-border))]">
            <a href="{{ route('marketplace.freelancers.show', Auth::user()) }}" class="gdfh-btn gdfh-btn-secondary py-2 px-5 font-bold">إلغاء</a>
            <button type="submit" class="gdfh-btn gdfh-btn-brand py-2 px-6 font-bold shadow-sm">
              حفظ بيانات البروفايل
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</x-app-layout>
