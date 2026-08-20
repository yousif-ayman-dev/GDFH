<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <a href="{{ route('marketplace.index', ['tab' => 'freelancers']) }}" class="text-xs font-bold text-[rgb(var(--color-copper))] hover:underline flex items-center gap-1">
        ← العودة لدليل المستقلين
      </a>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-6">
    <div class="mx-auto max-w-5xl space-y-6">

      {{-- Freelancer Header Card --}}
      <div class="gdfh-card p-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
          <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-[rgb(var(--color-copper-soft))] text-xl font-bold text-[rgb(var(--color-copper))]">
              {{ mb_substr($user->name, 0, 1) }}
            </div>
            <div>
              <h1 class="text-xl font-bold text-[rgb(var(--color-text-primary))]">{{ $user->name }}</h1>
              <p class="text-xs text-[rgb(var(--color-text-secondary))]">{{ $user->username ? '@'.$user->username : 'مستقل موثق' }}</p>
              <p class="mt-1 text-xs font-bold text-[rgb(var(--color-copper))]">
                {{ $user->freelancerProfile?->title ?? 'مطور ومهندس برمجيات' }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <div class="text-center sm:text-end">
              <span class="text-[10px] text-[rgb(var(--color-text-secondary))]">سعر الساعة</span>
              <div class="text-lg font-bold text-[rgb(var(--color-text-primary))]">${{ number_format($user->freelancerProfile?->hourly_rate ?? 25, 0) }}/h</div>
            </div>

            <form method="POST" action="{{ route('messaging.start', $user) }}">
              @csrf
              <button type="submit" class="gdfh-btn gdfh-btn-brand text-xs py-2.5 px-4 font-bold flex items-center gap-1.5">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.008v.008H8.625V12zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.008v.008h.008V12zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.008v.008h.008V12c0 3.728-4.03 6.75-9 6.75a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-3.728 4.03-6.75 9-6.75s9 3.022 9 6.75z"/></svg>
                <span>تواصل مع المستقل</span>
              </button>
            </form>
          </div>
        </div>

        @if ($user->freelancerProfile?->bio)
        <div class="pt-4 border-t border-[rgb(var(--color-border))]">
          <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))] mb-1">عن المستقل</h3>
          <p class="text-xs text-[rgb(var(--color-text-secondary))] leading-relaxed">
            {{ $user->freelancerProfile->bio }}
          </p>
        </div>
        @endif
      </div>

      {{-- Freelancer Offered Services --}}
      <div class="space-y-4">
        <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">الخدمات المعروضة بواسطة {{ $user->name }}</h3>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          @forelse ($user->services as $service)
          <div class="gdfh-card p-6 space-y-3 flex flex-col justify-between">
            <div class="space-y-2">
              <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-copper))]">
                {{ $service->category }}
              </span>
              <h4 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">
                <a href="{{ route('marketplace.services.show', $service) }}" class="hover:text-[rgb(var(--color-copper))]">
                  {{ $service->title }}
                </a>
              </h4>
            </div>

            <div class="pt-3 border-t border-[rgb(var(--color-border))] flex items-center justify-between text-xs">
              <span class="font-bold text-[rgb(var(--color-copper))]">${{ number_format($service->price, 0) }}</span>
              <a href="{{ route('marketplace.services.show', $service) }}" class="text-[11px] font-bold text-[rgb(var(--color-copper))] hover:underline">
                التفاصيل ←
              </a>
            </div>
          </div>
          @empty
          <div class="p-8 text-center text-xs text-[rgb(var(--color-text-secondary))] col-span-full gdfh-card">
            لم يقم المستقل بإضافة خدمات منفصلة حتى الآن.
          </div>
          @endforelse
        </div>
      </div>

    </div>
  </div>
</x-app-layout>
