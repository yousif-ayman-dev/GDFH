<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs font-semibold text-[rgb(var(--color-copper))]">بوابة سداد العقود والخدمات</p>
        <h2 class="mt-1 text-xl font-bold text-[rgb(var(--color-text-primary))]">إتمام عملية السداد الإلكتروني</h2>
      </div>
      <a href="{{ route('contracts.show', $payment->contract) }}" class="gdfh-btn gdfh-btn-secondary text-xs px-3 py-1.5">
        ← العودة للعقد
      </a>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-6">
    <div class="mx-auto max-w-2xl space-y-6">

      {{-- Flash Messages --}}
      @if (session('error'))
      <div class="flex items-center gap-3 p-4 rounded-xl border border-red-500/20 bg-red-500/10 text-red-700 dark:text-red-400 text-xs font-semibold">
        {{ session('error') }}
      </div>
      @endif

      {{-- Payment Summary Card --}}
      <div class="gdfh-card p-6 sm:p-8 space-y-6">
        <div class="border-b border-[rgb(var(--color-border))] pb-6 space-y-1">
          <span class="text-xs font-bold text-[rgb(var(--color-copper))]">تفاصيل الفاتورة والعقد</span>
          <h3 class="text-lg font-bold text-[rgb(var(--color-text-primary))]">{{ $payment->contract->title }}</h3>
          <p class="text-xs text-[rgb(var(--color-text-secondary))]">المستقل المستفيد: <span class="font-bold text-[rgb(var(--color-text-primary))]">{{ $payment->contract->freelancer?->name }}</span></p>
        </div>

        {{-- Breakdown Table --}}
        <div class="space-y-3 text-xs">
          <div class="flex justify-between py-2 border-b border-[rgb(var(--color-border)/0.5)] text-[rgb(var(--color-text-secondary))]">
            <span>قيمة العقد المتفق عليها (Gross Amount)</span>
            <span class="font-bold text-[rgb(var(--color-text-primary))]">${{ number_format($payment->amount, 2) }} {{ $payment->currency }}</span>
          </div>

          <div class="flex justify-between py-2 border-b border-[rgb(var(--color-border)/0.5)] text-[rgb(var(--color-text-secondary))]">
            <span>رسوم وعمولة المنصة ({{ $rate * 100 }}%)</span>
            <span class="font-semibold text-emerald-600 dark:text-emerald-400">${{ number_format($commissionEstimate, 2) }} {{ $payment->currency }}</span>
          </div>

          <div class="flex justify-between py-2 border-b border-[rgb(var(--color-border)/0.5)] text-[rgb(var(--color-text-secondary))]">
            <span>صافي المستقل المتوقع عند الاعتماد</span>
            <span class="font-semibold text-[rgb(var(--color-copper))]">${{ number_format($freelancerEstimate, 2) }} {{ $payment->currency }}</span>
          </div>

          <div class="flex justify-between py-3 pt-4 font-bold text-sm text-[rgb(var(--color-text-primary))] bg-[rgb(var(--color-surface-soft))] px-4 rounded-xl">
            <span>الإجمالي المستحق للدفع الآن</span>
            <span class="text-lg font-extrabold text-[rgb(var(--color-copper))]">${{ number_format($payment->amount, 2) }} {{ $payment->currency }}</span>
          </div>
        </div>

        {{-- Sandbox Provider Selection / Notice --}}
        <div class="p-4 rounded-xl bg-blue-500/10 border border-blue-500/20 text-xs text-blue-700 dark:text-blue-300 space-y-2">
          <div class="flex items-center gap-2 font-bold">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            <span>بيئة اختبار المدفوعات التفاعلية (Payment Sandbox Simulator)</span>
          </div>
          <p class="leading-relaxed">تتم عملية السداد هذه عبر بوابات تجريبية آمنة (Non-Custodial Sandbox Provider). لا توجد مبالغ حقيقية مخصومة من أي بطاقة مصرفية.</p>
        </div>

        {{-- Payment Options Form --}}
        <form method="POST" action="{{ route('payments.callback', $payment) }}" class="space-y-4 pt-2">
          @csrf

          <div class="space-y-2">
            <label class="block text-xs font-bold text-[rgb(var(--color-text-primary))]">اختر نتيجة محاكاة عملية الدفع للاختبار:</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <label class="flex items-center gap-3 p-3 rounded-xl border border-[rgb(var(--color-border))] cursor-pointer hover:border-emerald-500 transition">
                <input type="radio" name="simulated_status" value="paid" checked class="text-emerald-500 focus:ring-emerald-500">
                <div>
                  <div class="font-bold text-xs text-emerald-600 dark:text-emerald-400">سداد ناجح (Paid)</div>
                  <div class="text-[10px] text-[rgb(var(--color-text-secondary))]">محاكاة عملية دفع ناجحة وتفعيل العقد</div>
                </div>
              </label>

              <label class="flex items-center gap-3 p-3 rounded-xl border border-[rgb(var(--color-border))] cursor-pointer hover:border-red-500 transition">
                <input type="radio" name="simulated_status" value="failed" class="text-red-500 focus:ring-red-500">
                <div>
                  <div class="font-bold text-xs text-red-600 dark:text-red-400">فشل العملية (Failed)</div>
                  <div class="text-[10px] text-[rgb(var(--color-text-secondary))]">محاكاة رفض البطاقة أو إلغاء العميل</div>
                </div>
              </label>
            </div>
          </div>

          <div class="pt-4 flex items-center justify-end gap-3">
            <a href="{{ route('contracts.show', $payment->contract) }}" class="gdfh-btn gdfh-btn-secondary text-xs px-4">إلغاء</a>
            <button type="submit" class="gdfh-btn gdfh-btn-brand text-xs py-3 px-8 font-bold">
              تأكيد الدفع الإلكتروني الآن ←
            </button>
          </div>
        </form>

      </div>

    </div>
  </div>
</x-app-layout>
