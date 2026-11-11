<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <p class="text-xs font-semibold text-[rgb(var(--color-copper))]">لوحة الإدارة العليا</p>
        <h2 class="mt-1 text-xl font-bold text-[rgb(var(--color-text-primary))]">تدقيق وسجل المدفوعات والعمولات</h2>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('admin.index') }}" class="gdfh-btn gdfh-btn-secondary text-xs px-3 py-1.5">← لوحة المشرف</a>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-6">
    <div class="mx-auto max-w-7xl space-y-6">

      {{-- Financial Summary KPIs --}}
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="gdfh-card p-5 space-y-2">
          <span class="text-xs text-[rgb(var(--color-text-secondary))] font-medium">إجمالي المدفوعات المؤكدة (Gross Volume)</span>
          <div class="text-2xl font-extrabold text-[rgb(var(--color-text-primary))]">${{ number_format($totalGrossPaid, 2) }}</div>
        </div>

        <div class="gdfh-card p-5 space-y-2 border-r-4 border-r-emerald-500">
          <span class="text-xs text-[rgb(var(--color-text-secondary))] font-medium">صافي إيرادات وعمولات المنصة المحسوبة</span>
          <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">${{ number_format($totalCommissionEarned, 2) }}</div>
        </div>

        <div class="gdfh-card p-5 space-y-2">
          <span class="text-xs text-[rgb(var(--color-text-secondary))] font-medium">عدد المعاملات المسجلة</span>
          <div class="text-2xl font-extrabold text-[rgb(var(--color-copper))]">{{ $payments->total() }}</div>
        </div>
      </div>

      {{-- Filter Bar --}}
      <form method="GET" action="{{ route('admin.payments') }}" class="gdfh-card p-4 flex flex-wrap items-center gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
          placeholder="ابحث بالعميل أو عنوان العقد..."
          class="gdfh-input flex-1 min-w-48">

        <select name="status" class="gdfh-input w-44">
          <option value="">جميع الحالات</option>
          <option value="paid" @selected(request('status') === 'paid')>مكتمـل (Paid)</option>
          <option value="pending" @selected(request('status') === 'pending')>معلّق (Pending)</option>
          <option value="failed" @selected(request('status') === 'failed')>فاشل (Failed)</option>
          <option value="refunded" @selected(request('status') === 'refunded')>مسترد (Refunded)</option>
        </select>

        <button type="submit" class="gdfh-btn gdfh-btn-brand text-xs px-4">تصفية</button>
        @if (request()->hasAny(['search', 'status']))
        <a href="{{ route('admin.payments') }}" class="gdfh-btn gdfh-btn-secondary text-xs px-3">مسح</a>
        @endif
      </form>

      {{-- Payments Table --}}
      <div class="gdfh-card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-[rgb(var(--color-border))]">
          <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
            سجل جميع عمليات السداد
            <span class="ms-2 text-xs font-normal text-[rgb(var(--color-text-secondary))]">({{ $payments->total() }} معاملة)</span>
          </h3>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-xs">
            <thead>
              <tr class="border-b border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface-soft))]">
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">#</th>
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">العقد والمرجع</th>
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">العميل</th>
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">الإجمالي (Gross)</th>
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">عمولة المنصة</th>
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">صافي المستقل</th>
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">الحالة</th>
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">التاريخ</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[rgb(var(--color-border)/0.5)]">
              @forelse ($payments as $p)
              <tr class="hover:bg-[rgb(var(--color-surface-soft))] transition">
                <td class="px-4 py-3 text-[rgb(var(--color-text-secondary))]">{{ $p->id }}</td>
                <td class="px-4 py-3">
                  <div class="font-semibold text-[rgb(var(--color-text-primary))]">{{ $p->contract?->title }}</div>
                  <div class="text-[10px] text-[rgb(var(--color-text-secondary))] font-mono">{{ $p->provider_payment_id ?? 'N/A' }}</div>
                </td>
                <td class="px-4 py-3 text-[rgb(var(--color-text-primary))] font-medium">{{ $p->client?->name }}</td>
                <td class="px-4 py-3 font-bold text-[rgb(var(--color-text-primary))]">${{ number_format($p->amount, 2) }}</td>
                <td class="px-4 py-3 font-semibold text-emerald-600 dark:text-emerald-400">
                  ${{ number_format($p->commission?->commission_amount ?? 0, 2) }}
                  <span class="text-[10px] text-[rgb(var(--color-text-secondary))]">({{ ($p->commission?->rate ?? 0.10) * 100 }}%)</span>
                </td>
                <td class="px-4 py-3 font-semibold text-[rgb(var(--color-copper))]">
                  ${{ number_format($p->commission?->freelancer_amount ?? 0, 2) }}
                </td>
                <td class="px-4 py-3">
                  @if ($p->isPaid())
                  <span class="gdfh-badge bg-emerald-500/10 text-emerald-500 text-[10px]">مكتمل</span>
                  @elseif ($p->isRefunded())
                  <span class="gdfh-badge bg-amber-500/10 text-amber-500 text-[10px]">مسترد</span>
                  @elseif ($p->isFailed())
                  <span class="gdfh-badge bg-red-500/10 text-red-500 text-[10px]">فاشل</span>
                  @else
                  <span class="gdfh-badge bg-blue-500/10 text-blue-500 text-[10px]">معلق</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-[rgb(var(--color-text-secondary))] text-[10px]">{{ $p->created_at->format('Y-m-d H:i') }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="8" class="px-4 py-10 text-center text-[rgb(var(--color-text-secondary))]">لا توجد عمليات دفع مسجلة حالياً.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if ($payments->hasPages())
        <div class="px-5 py-4 border-t border-[rgb(var(--color-border))]">
          {{ $payments->links() }}
        </div>
        @endif
      </div>

    </div>
  </div>
</x-app-layout>
