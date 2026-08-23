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
              <span class="font-bold text-amber-500 flex items-center gap-1">
                <svg class="h-3.5 w-3.5 text-amber-500 fill-amber-500 inline" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> {{ number_format($service->rating, 1) }}
              </span>
            </div>
          </div>

          <div class="pt-4 border-t border-[rgb(var(--color-border))] space-y-3">
            <div class="flex items-center gap-3">
              @if ($service->user?->avatar_url)
              <img src="{{ $service->user->avatar_url }}" alt="{{ $service->user->name }}" class="h-10 w-10 rounded-full object-cover border border-slate-600">
              @else
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[rgb(var(--color-copper-soft))] text-xs font-bold text-[rgb(var(--color-copper))]">
                {{ mb_substr($service->user?->name ?? 'م', 0, 1) }}
              </div>
              @endif
              <div>
                <h4 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">{{ $service->user?->name }}</h4>
                <p class="text-[10px] text-[rgb(var(--color-text-secondary))]">مستقل معتمد</p>
              </div>
            </div>

            @if ($errors->has('order'))
            <div class="p-2.5 rounded-lg bg-red-500/10 border border-red-500/20 text-red-500 text-[11px] font-bold">
              {{ $errors->first('order') }}
            </div>
            @endif

            @if (Auth::check() && (int) Auth::id() === (int) $service->user_id)
            <a href="{{ route('marketplace.services.edit', $service) }}" class="w-full gdfh-btn gdfh-btn-secondary text-xs py-3 font-bold text-center block">
              تعديل تفاصيل الخدمة
            </a>
            @else
            <form method="POST" action="{{ route('marketplace.services.order', $service) }}">
              @csrf
              <button type="submit" class="w-full gdfh-btn gdfh-btn-brand text-xs py-3 font-bold">
                طلب هذه الخدمة الآن
              </button>
            </form>
            @endif
          </div>
        </div>

      </div>

    </div>
  </div>
</x-app-layout>
