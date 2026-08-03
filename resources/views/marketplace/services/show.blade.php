<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <a href="{{ route('marketplace.index') }}" class="text-xs font-bold text-[rgb(var(--color-copper))] hover:underline flex items-center gap-1">
        ← العودة لدليل السوق
      </a>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-6">
    <div class="mx-auto max-w-5xl space-y-6">

      <div class="gdfh-card p-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
        
        {{-- Main Service Info --}}
        <div class="lg:col-span-2 space-y-6">
          <div class="space-y-2">
            <span class="text-xs font-bold px-2.5 py-1 rounded bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-copper))]">
              {{ $service->category }}
            </span>

            <h1 class="text-xl font-bold text-[rgb(var(--color-text-primary))] leading-relaxed">
              {{ $service->title }}
            </h1>
          </div>

          <div class="pt-4 border-t border-[rgb(var(--color-border))] space-y-3">
            <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))] uppercase tracking-wider">وصف الخدمة</h3>
            <p class="text-xs text-[rgb(var(--color-text-secondary))] leading-relaxed whitespace-pre-line">
              {{ $service->description }}
            </p>
          </div>
        </div>

        {{-- Side Checkout Card --}}
        <div class="gdfh-card p-6 border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface-soft)/0.3)] space-y-6 h-fit">
          <div class="space-y-1">
            <span class="text-xs text-[rgb(var(--color-text-secondary))]">سعر الخدمة</span>
            <div class="text-3xl font-extrabold text-[rgb(var(--color-copper))]">${{ number_format($service->price, 2) }}</div>
          </div>

          <div class="space-y-2 text-xs text-[rgb(var(--color-text-primary))]">
            <div class="flex items-center justify-between">
              <span class="text-[rgb(var(--color-text-secondary))]">مدة التسليم المتوقعة:</span>
              <span class="font-bold">{{ $service->delivery_days }} أيام</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-[rgb(var(--color-text-secondary))]">تقييم الخدمة:</span>
              <span class="font-bold text-amber-500">★ {{ number_format($service->rating, 1) }}</span>
            </div>
          </div>

          <div class="pt-4 border-t border-[rgb(var(--color-border))] space-y-3">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[rgb(var(--color-copper-soft))] text-xs font-bold text-[rgb(var(--color-copper))]">
                {{ mb_substr($service->user?->name ?? 'م', 0, 1) }}
              </div>
              <div>
                <h4 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">{{ $service->user?->name }}</h4>
                <p class="text-[10px] text-[rgb(var(--color-text-secondary))]">مستقل معتمد</p>
              </div>
            </div>

            <button type="button" onclick="alert('تم إرسال طلب الشراء إلى المستقل بنجاح!')" class="w-full gdfh-btn gdfh-btn-brand text-xs py-3 font-bold">
              طلب هذه الخدمة الآن
            </button>
          </div>
        </div>

      </div>

    </div>
  </div>
</x-app-layout>
