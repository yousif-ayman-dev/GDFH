<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <a href="{{ route('marketplace.index', ['tab' => 'freelancers']) }}" class="text-xs font-bold text-[rgb(var(--color-copper))] hover:underline flex items-center gap-1">
        ← العودة لدليل المستقلين
      </a>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-6">
    <div class="mx-auto max-w-5xl space-y-8">

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
                {{ $user->freelancerProfile?->title ?? 'مستقل وخبير منفذ' }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <div class="text-center sm:text-end">
              <span class="text-[10px] text-[rgb(var(--color-text-secondary))]">سعر الساعة</span>
              <div class="text-lg font-bold text-[rgb(var(--color-text-primary))]">${{ number_format($user->freelancerProfile?->hourly_rate ?? 25, 0) }}/h</div>
            </div>

            @if (Auth::check() && (int) Auth::id() === (int) $user->id)
            <div class="flex items-center gap-2">
              <a href="{{ route('portfolio.index') }}" class="gdfh-btn gdfh-btn-brand text-xs py-2.5 px-4 font-bold">
                <span>+ إدارة معرض أعمالي</span>
              </a>
              <a href="{{ route('marketplace.freelancers.profile.edit') }}" class="gdfh-btn gdfh-btn-secondary text-xs py-2.5 px-4 font-bold">
                <span>تعديل بروفايلي</span>
              </a>
            </div>
            @else
            <form method="POST" action="{{ route('messaging.start', $user) }}">
              @csrf
              <button type="submit" class="gdfh-btn gdfh-btn-brand text-xs py-2.5 px-4 font-bold flex items-center gap-1.5 shadow-md">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.008v.008H8.625V12zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.008v.008h.008V12zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.008v.008h.008V12c0 3.728-4.03 6.75-9 6.75a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-3.728 4.03-6.75 9-6.75s9 3.022 9 6.75z"/></svg>
                <span>تواصل مباشر مع المستقل</span>
              </button>
            </form>
            @endif
          </div>
        </div>

        @if ($user->freelancerProfile?->bio || $user->bio)
        <div class="pt-4 border-t border-[rgb(var(--color-border))] space-y-2">
          <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">نبذة عن المستقل وخبراته</h3>
          <p class="text-xs text-[rgb(var(--color-text-secondary))] leading-relaxed">
            {{ $user->freelancerProfile?->bio ?? $user->bio }}
          </p>
        </div>
        @endif

        @if ($user->freelancerProfile?->skills)
        <div class="pt-3 border-t border-[rgb(var(--color-border))] space-y-2">
          <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))]">المهارات والتخصصات</h3>
          <div class="flex flex-wrap gap-1.5">
            @php
              $skills = is_array($user->freelancerProfile->skills) 
                ? $user->freelancerProfile->skills 
                : array_filter(array_map('trim', explode(',', (string)$user->freelancerProfile->skills)));
            @endphp
            @foreach ($skills as $sk)
            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))] border border-[rgb(var(--color-copper-soft))]">
              {{ $sk }}
            </span>
            @endforeach
          </div>
        </div>
        @endif
      </div>

      {{-- Portfolio Showcase Section (معرض الأعمال السابق) --}}
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-base font-black text-[rgb(var(--color-text-primary))] flex items-center gap-2">
              <svg class="h-5 w-5 text-[rgb(var(--color-copper))]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
              <span>معرض الأعمال والمشاريع السابقة (Portfolio)</span>
            </h3>
            <p class="text-xs text-[rgb(var(--color-text-secondary))] mt-0.5">
              استعرض المخرجات والتصاميم البرمجية والهندسية التي نفذها المستقل سابقاً.
            </p>
          </div>

          @if (Auth::check() && (int) Auth::id() === (int) $user->id)
          <a href="{{ route('portfolio.index') }}" class="text-xs font-bold text-[rgb(var(--color-copper))] hover:underline">
            + إضافة عمل جديد
          </a>
          @endif
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          @forelse ($user->portfolioItems as $item)
          <div class="gdfh-card overflow-hidden flex flex-col justify-between shadow-sm border border-[rgb(var(--color-border))] hover:shadow-md transition">
            <div>
              @if ($item->image_path)
              <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->title }}" class="h-48 w-full object-cover">
              @else
              <div class="h-48 w-full bg-[rgb(var(--color-copper-soft))] flex items-center justify-center text-[rgb(var(--color-copper))]">
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

            @if ($item->project_url)
            <div class="p-4 border-t border-[rgb(var(--color-border))] text-xs bg-[rgb(var(--color-surface-soft)/0.3)]">
              <a href="{{ $item->project_url }}" target="_blank" rel="noopener noreferrer" class="text-[rgb(var(--color-copper))] font-bold hover:underline flex items-center gap-1">
                <span>معاينة المشروع المباشر ↗</span>
              </a>
            </div>
            @endif
          </div>
          @empty
          <div class="p-10 text-center text-xs text-[rgb(var(--color-text-secondary))] col-span-full gdfh-card">
            لم يقم المستقل بإضافة نماذج أعمال إلى البورتفوليو حتى الآن.
          </div>
          @endforelse
        </div>
      </div>

      {{-- Freelancer Offered Services --}}
      <div class="space-y-4 pt-4">
        <h3 class="text-base font-black text-[rgb(var(--color-text-primary))]">الخدمات المصغرة المعروضة بواسطة {{ $user->name }}</h3>

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
                طلب الخدمة والتفاصيل ←
              </a>
            </div>
          </div>
          @empty
          <div class="p-8 text-center text-xs text-[rgb(var(--color-text-secondary))] col-span-full gdfh-card">
            لم يقم المستقل بإضافة خدمات مصغرة معروضة حتى الآن.
          </div>
          @endforelse
        </div>
      </div>

    </div>
  </div>
</x-app-layout>
