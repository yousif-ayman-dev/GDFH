<x-app-layout>
  @php
    $roleLabels = [
        'owner' => 'مالك',
        'admin' => 'مدير',
        'member' => 'عضو',
        'viewer' => 'مشاهد',
    ];

    $statusLabels = [
        'pending' => 'قيد الانتظار',
        'accepted' => 'مقبولة',
        'rejected' => 'مرفوضة',
        'cancelled' => 'ملغاة',
        'expired' => 'منتهية',
    ];
  @endphp

  <x-slot name="header">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold text-[rgb(var(--color-text-primary))]">
          مركز الدعوات
        </h2>
        <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">
          إدارة دعوات الانضمام إلى فرق العمل والمشاريع.
        </p>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
    <div class="mx-auto max-w-5xl space-y-6">

      @if (session('success'))
      <div class="flex items-start gap-3 rounded-xl border border-[rgb(var(--color-success)/0.25)] bg-[rgb(var(--color-success)/0.08)] px-4 py-3" role="alert">
        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[rgb(var(--color-success)/0.12)] text-[rgb(var(--color-success))]">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </div>

        <div class="min-w-0">
          <p class="text-sm font-semibold text-[rgb(var(--color-text-primary))]">
            تمت العملية بنجاح
          </p>

          <p class="mt-0.5 text-xs leading-6 text-[rgb(var(--color-text-secondary))]">
            {{ session('success') }}
          </p>
        </div>
      </div>
      @endif

      @if ($errors->any())
      <div class="flex items-start gap-3 rounded-xl border border-[rgb(var(--color-error)/0.30)] bg-[rgb(var(--color-error)/0.08)] p-4">
        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[rgb(var(--color-error)/0.12)] text-[rgb(var(--color-error))]">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 9v4m0 4h.01M10.3 3.8L2.6 17.1A2 2 0 004.3 20h15.4a2 2 0 001.7-2.9L13.7 3.8a2 2 0 00-3.4 0z" />
          </svg>
        </div>

        <div>
          <p class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
            حدث خطأ أثناء معالجة الدعوة
          </p>

          <ul class="mt-2 space-y-1 text-sm text-[rgb(var(--color-error))]">
            @foreach ($errors->all() as $error)
            <li>• {{ $error }}</li>
            @endforeach
          </ul>
        </div>
      </div>
      @endif

      <section class="gdfh-card overflow-hidden">
        <div class="border-b border-[rgb(var(--color-border))] p-5 sm:p-6">
          <h3 class="text-base font-bold text-[rgb(var(--color-text-primary))]">
            دعوات الفرق المتلقاة
          </h3>
          <p class="mt-0.5 text-xs text-[rgb(var(--color-text-secondary))]">
            قائمة بجميع الدعوات الموجهة إليك للانضمام إلى فرق العمل.
          </p>
        </div>

        <div class="divide-y divide-[rgb(var(--color-border))]">
          @forelse ($invitations as $invitation)
          <div class="p-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between transition hover:bg-[rgb(var(--color-surface-soft)/0.4)]">
            <div class="min-w-0 flex-1 space-y-2">
              <div class="flex flex-wrap items-center gap-2">
                <span class="text-base font-bold text-[rgb(var(--color-text-primary))]">
                  {{ $invitation->team?->name ?? 'فريق غير معروف' }}
                </span>

                <span class="gdfh-badge bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))] text-xs font-semibold">
                  {{ $roleLabels[$invitation->role] ?? $invitation->role }}
                </span>

                @if ($invitation->isPending())
                  <span class="gdfh-badge bg-amber-500/10 text-amber-600 text-xs">
                    قيد الانتظار
                  </span>
                @elseif ($invitation->status === 'accepted')
                  <span class="gdfh-badge bg-[rgb(var(--color-success)/0.12)] text-[rgb(var(--color-success))] text-xs">
                    مقبولة
                  </span>
                @elseif ($invitation->status === 'rejected')
                  <span class="gdfh-badge bg-[rgb(var(--color-error)/0.12)] text-[rgb(var(--color-error))] text-xs">
                    مرفوضة
                  </span>
                @elseif ($invitation->isExpired())
                  <span class="gdfh-badge bg-gray-500/10 text-gray-500 text-xs">
                    منتهية
                  </span>
                @else
                  <span class="gdfh-badge bg-gray-500/10 text-gray-500 text-xs">
                    {{ $statusLabels[$invitation->status] ?? $invitation->status }}
                  </span>
                @endif
              </div>

              @if ($invitation->message)
              <p class="text-xs text-[rgb(var(--color-text-secondary))] bg-[rgb(var(--color-surface-soft))] p-2.5 rounded-lg border border-[rgb(var(--color-border))]">
                "{{ $invitation->message }}"
              </p>
              @endif

              <div class="flex flex-wrap items-center gap-4 text-xs text-[rgb(var(--color-text-secondary))]">
                <span>الداعي: <strong>{{ $invitation->inviter?->name ?? 'المالك' }}</strong> (@ {{ $invitation->inviter?->username ?? 'user' }})</span>
                <span>تاريخ الدعوة: <strong>{{ $invitation->created_at->format('Y/m/d') }}</strong></span>
                <span>تنتهي في: <strong>{{ $invitation->expires_at->format('Y/m/d') }}</strong></span>
              </div>
            </div>

            <div class="flex items-center gap-2 self-end sm:self-center">
              @if ($invitation->isPending())
                <form method="POST" action="{{ route('invitations.accept', $invitation) }}">
                  @csrf
                  <button type="submit" class="gdfh-btn gdfh-btn-brand py-1.5 px-4 text-xs font-bold">
                    قبول الدعوة
                  </button>
                </form>

                <form method="POST" action="{{ route('invitations.reject', $invitation) }}">
                  @csrf
                  <button type="submit" class="gdfh-btn gdfh-btn-secondary py-1.5 px-4 text-xs font-semibold text-[rgb(var(--color-error))] hover:bg-[rgb(var(--color-error)/0.1)]">
                    رفض
                  </button>
                </form>
              @else
                <span class="text-xs text-[rgb(var(--color-text-secondary))] font-medium italic">
                  تمت المعالجة
                </span>
              @endif
            </div>
          </div>
          @empty
          <div class="p-12 text-center text-xs text-[rgb(var(--color-text-secondary))]">
            <svg class="mx-auto h-8 w-8 opacity-40 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
            </svg>
            لا توجد لديك أي دعوات حالياً.
          </div>
          @endforelse
        </div>
      </section>

    </div>
  </div>
</x-app-layout>
