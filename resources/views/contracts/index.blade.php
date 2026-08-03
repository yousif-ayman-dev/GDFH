<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[rgb(var(--color-text-primary))]">
          العقود والاتفاقيات (Contracts & Agreements)
        </h2>
        <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">
          متابعة العقود النشطة والمكتملة بين العملاء والمستقلين.
        </p>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-6">
    <div class="mx-auto max-w-7xl space-y-6">

      {{-- Success Message --}}
      @if (session('success'))
      <div class="flex items-start gap-3 rounded-xl border p-4" style="border-color: rgb(var(--color-success) / 0.30); background-color: rgb(var(--color-success) / 0.08);">
        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" style="background-color: rgb(var(--color-success) / 0.12); color: rgb(var(--color-success));">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5l4 4L19 7" /></svg>
        </div>
        <div>
          <p class="text-sm font-bold text-[rgb(var(--color-text-primary))]">تمت العملية بنجاح</p>
          <p class="mt-1 text-sm" style="color: rgb(var(--color-success));">{{ session('success') }}</p>
        </div>
      </div>
      @endif

      {{-- Contracts List Card --}}
      <div class="gdfh-card overflow-hidden space-y-0">
        <div class="border-b border-[rgb(var(--color-border))] p-5">
          <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">جميع العقود المسجلة</h3>
        </div>

        <div class="divide-y divide-[rgb(var(--color-border))]">
          @forelse ($contracts as $contract)
          <div class="p-6 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 hover:bg-[rgb(var(--color-surface-soft)/0.3)] transition">
            <div class="space-y-1.5 min-w-0">
              <div class="flex items-center gap-2">
                <span class="gdfh-badge text-xs font-bold {{ $contract->isActive() ? 'bg-emerald-500/10 text-emerald-500' : 'bg-blue-500/10 text-blue-500' }}">
                  {{ $contract->isActive() ? 'نشط (Active)' : 'مكتمل (Completed)' }}
                </span>
                <span class="text-xs text-[rgb(var(--color-text-secondary))]">تاريخ البدء: {{ $contract->start_date->format('Y-m-d') }}</span>
              </div>

              <h4 class="text-sm font-bold text-[rgb(var(--color-text-primary))] truncate">
                <a href="{{ route('contracts.show', $contract) }}" class="hover:text-[rgb(var(--color-copper))]">
                  {{ $contract->title }}
                </a>
              </h4>

              <div class="flex items-center gap-4 text-xs text-[rgb(var(--color-text-secondary))]">
                <span>العميل: <strong>{{ $contract->client?->name }}</strong></span>
                <span>المستقل: <strong>{{ $contract->freelancer?->name }}</strong></span>
              </div>
            </div>

            <div class="flex items-center justify-between md:justify-end gap-4 shrink-0">
              <div class="text-end">
                <span class="text-[10px] text-[rgb(var(--color-text-secondary))]">قيمة العقد</span>
                <div class="text-base font-extrabold text-[rgb(var(--color-copper))]">${{ number_format($contract->amount, 2) }}</div>
              </div>

              <a href="{{ route('contracts.show', $contract) }}" class="gdfh-btn gdfh-btn-brand text-xs py-2 px-4">
                عرض التفاصيل
              </a>
            </div>
          </div>
          @empty
          <div class="p-12 text-center text-xs text-[rgb(var(--color-text-secondary))]">
            لا توجد عقود مسجلة حتى الآن.
          </div>
          @endforelse
        </div>
      </div>

    </div>
  </div>
</x-app-layout>
