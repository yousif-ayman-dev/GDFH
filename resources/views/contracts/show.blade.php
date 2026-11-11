<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <a href="{{ route('contracts.index') }}" class="text-xs font-bold text-[rgb(var(--color-copper))] hover:underline flex items-center gap-1">
        ← العودة لقائمة العقود
      </a>

      <div class="flex items-center gap-2">
        <span class="gdfh-badge text-xs font-bold {{ $contract->isActive() ? 'bg-emerald-500/10 text-emerald-500' : 'bg-blue-500/10 text-blue-500' }}">
          حالة العقد: {{ $contract->isActive() ? 'نشط وقيد التنفيذ' : 'مكتمل ومسلم' }}
        </span>

        @if ($contract->isPaid())
        <span class="gdfh-badge bg-emerald-500/10 text-emerald-500 text-xs font-bold flex items-center gap-1">
          <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
          <span>سداد مؤكد</span>
        </span>
        @elseif ($contract->payment_status === 'pending')
        <span class="gdfh-badge bg-amber-500/10 text-amber-500 text-xs font-bold">
          الدفع قيد الجلسة
        </span>
        @else
        <span class="gdfh-badge bg-red-500/10 text-red-500 text-xs font-bold">
          غير مدفوع
        </span>
        @endif
      </div>
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

      @if ($errors->has('payment'))
      <div class="flex items-start gap-3 rounded-xl border p-4 border-red-500/30 bg-red-500/10 text-red-700 dark:text-red-400 text-xs font-semibold">
        {{ $errors->first('payment') }}
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

        {{-- Payment Status & Action Section --}}
        <div class="pt-6 border-t border-[rgb(var(--color-border))] space-y-4">
          <h3 class="text-xs font-bold text-[rgb(var(--color-text-primary))] uppercase tracking-wider">حالة الدفع والتسوية المالية</h3>

          @php
            $rate = (float) config('monetization.commission_rate', 0.10);
            $commissionAmount = round((float) $contract->amount * $rate, 2);
            $freelancerNet = round((float) $contract->amount - $commissionAmount, 2);
          @endphp

          @if (auth()->id() === $contract->client_id)
            {{-- Client View --}}
            <div class="p-4 rounded-xl bg-[rgb(var(--color-surface-soft))] border border-[rgb(var(--color-border))] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div>
                <div class="text-xs font-bold text-[rgb(var(--color-text-primary))]">
                  إجمالي المبلغ المستحق للدفع: <span class="text-emerald-600 dark:text-emerald-400 font-extrabold">${{ number_format($contract->amount, 2) }}</span>
                </div>
                <div class="text-[10px] text-[rgb(var(--color-text-secondary))] mt-0.5">
                  @if ($contract->isPaid())
                  تم سداد وتأكيد دفع هذا العقد الإلكتروني بنجاح.
                  @else
                  سداد العقد يضمن توثيق العمل وحفظ حقوق المستقل والعميل عبر المنصة.
                  @endif
                </div>
              </div>

              @if (!$contract->isPaid())
              <form method="POST" action="{{ route('payments.initiate', $contract) }}">
                @csrf
                <button type="submit" class="gdfh-btn gdfh-btn-brand text-xs py-2.5 px-6 font-bold flex items-center gap-1.5">
                  <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10h18M7 15h.01M11 15h2M3 6h18a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z"/></svg>
                  <span>سداد العقد الآن ←</span>
                </button>
              </form>
              @else
              <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                <span>تم السداد بالكامل</span>
              </span>
              @endif
            </div>
          @elseif (auth()->id() === $contract->freelancer_id)
            {{-- Freelancer View --}}
            <div class="p-4 rounded-xl bg-[rgb(var(--color-surface-soft))] border border-[rgb(var(--color-border))] space-y-3">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[rgb(var(--color-border)/0.5)] pb-3">
                <div class="text-xs font-bold text-[rgb(var(--color-text-primary))]">
                  المبلغ المخصص للمستقل لهذا العقد: 
                  <span class="text-emerald-600 dark:text-emerald-400 font-extrabold text-sm">${{ number_format($freelancerNet, 2) }}</span>
                </div>

                @if ($contract->isPaid())
                <span class="gdfh-badge bg-emerald-500/10 text-emerald-500 text-xs font-bold">
                  تم تأكيد سداد هذا العقد
                </span>
                @else
                <span class="gdfh-badge bg-amber-500/10 text-amber-500 text-xs font-bold">
                  بانتظار سداد العميل
                </span>
                @endif
              </div>

              <div class="grid grid-cols-2 gap-4 text-[10px] text-[rgb(var(--color-text-secondary))]">
                <div>قيمة العقد الكلية (Gross): <span class="font-bold text-[rgb(var(--color-text-primary))]">${{ number_format($contract->amount, 2) }}</span></div>
                <div>عمولة المنصة التشغيلية ({{ $rate * 100 }}%): <span class="font-bold text-[rgb(var(--color-text-primary))]">${{ number_format($commissionAmount, 2) }}</span></div>
              </div>
            </div>
          @endif
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
          <form method="POST" action="{{ route('contracts.complete', $contract) }}" onsubmit="return confirm('تأكيد إتمام وتسليم العقد والمشروع؟')">
            @csrf
            <button type="submit" class="gdfh-btn gdfh-btn-brand text-xs py-2.5 px-6 font-bold flex items-center gap-1.5">
              <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
              <span>تأكيد تسليم وإتمام العقد</span>
            </button>
          </form>
        </div>
        @endif

      </div>

    </div>
  </div>
</x-app-layout>
