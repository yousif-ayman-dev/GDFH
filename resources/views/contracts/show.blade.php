<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <a href="{{ route('contracts.index') }}" class="text-xs font-bold text-[rgb(var(--color-copper))] hover:underline flex items-center gap-1">
        ← العودة لقائمة العقود
      </a>

      <span class="gdfh-badge text-xs font-bold {{ $contract->isActive() ? 'bg-emerald-500/10 text-emerald-500' : 'bg-blue-500/10 text-blue-500' }}">
        حالة العقد: {{ $contract->isActive() ? 'نشط وقيد التنفيذ' : 'مكتمل ومسلم' }}
      </span>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-6">
    <div class="mx-auto max-w-4xl space-y-6">

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

      {{-- Contract Details Card --}}
      <div class="gdfh-card p-8 space-y-6">
        <div class="border-b border-[rgb(var(--color-border))] pb-6 space-y-2">
          <span class="text-xs font-bold text-[rgb(var(--color-copper))]">وثيقة عقد رسمي</span>
          <h1 class="text-xl font-bold text-[rgb(var(--color-text-primary))]">{{ $contract->title }}</h1>
          <p class="text-xs text-[rgb(var(--color-text-secondary))]">المشروع المرتبط: <a href="{{ route('projects.show', $contract->project) }}" class="text-[rgb(var(--color-copper))] font-bold hover:underline">{{ $contract->project?->title }}</a></p>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3 text-xs">
          <div class="space-y-1">
            <span class="text-[rgb(var(--color-text-secondary))]">العميل (صاحب العمل)</span>
            <div class="font-bold text-[rgb(var(--color-text-primary))] text-sm">{{ $contract->client?->name }}</div>
          </div>

          <div class="space-y-1">
            <span class="text-[rgb(var(--color-text-secondary))]">المستقل (المنفذ)</span>
            <div class="font-bold text-[rgb(var(--color-text-primary))] text-sm">{{ $contract->freelancer?->name }}</div>
          </div>

          <div class="space-y-1">
            <span class="text-[rgb(var(--color-text-secondary))]">قيمة العقد المتفق عليها</span>
            <div class="font-extrabold text-[rgb(var(--color-copper))] text-base">${{ number_format($contract->amount, 2) }}</div>
          </div>
        </div>

        @if ($contract->proposal)
        <div class="pt-6 border-t border-[rgb(var(--color-border))] space-y-3">
          <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))] uppercase tracking-wider">نص العرض المقدم والمعتمد</h3>
          <div class="p-4 rounded-xl bg-[rgb(var(--color-surface-soft))] text-xs text-[rgb(var(--color-text-primary))] leading-relaxed whitespace-pre-line border border-[rgb(var(--color-border))]">
            {{ $contract->proposal->cover_letter }}
          </div>
        </div>
        @endif

        {{-- Complete Contract Button --}}
        @if ($contract->isActive())
        <div class="pt-6 border-t border-[rgb(var(--color-border))] flex items-center justify-end">
          <form method="POST" action="{{ route('contracts.complete', $contract) }}" onsubmit="return confirm('تأكيد إتمام وتأليم العقد والمشروع؟')">
            @csrf
            <button type="submit" class="gdfh-btn gdfh-btn-brand text-xs py-2.5 px-6 font-bold">
              ✓ تأكيد تسليم وإتمام العقد
            </button>
          </form>
        </div>
        @endif

      </div>

    </div>
  </div>
</x-app-layout>
