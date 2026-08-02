<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl font-bold text-[rgb(var(--color-text-primary))]">
          مركز الإشعارات (Notification Center)
        </h2>
        <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">
          استعرض وتابع جميع الإشعارات والتحذيرات والتحديثات الواردة.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <form method="POST" action="{{ route('notifications.read-all') }}">
          @csrf
          <button type="submit" class="gdfh-btn gdfh-btn-secondary text-xs">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            تحديد الكل كمقروء
          </button>
        </form>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
    <div class="mx-auto max-w-4xl space-y-6">

      {{-- Flash Messages --}}
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

      {{-- Notifications List Card --}}
      <section class="gdfh-card overflow-hidden">
        <div class="border-b border-[rgb(var(--color-border))] p-5 flex items-center justify-between">
          <h2 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">جميع الإشعارات</h2>
          <span class="gdfh-badge text-xs font-bold" style="background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));">
            غير مقروء: {{ Auth::user()->unreadNotificationsCount() }}
          </span>
        </div>

        <div class="divide-y divide-[rgb(var(--color-border))]">
          @forelse ($notifications as $notification)
          <div class="p-4 sm:p-5 flex items-start justify-between gap-4 transition {{ $notification->isUnread() ? 'bg-[rgb(var(--color-copper-soft)/0.2)] font-medium' : 'opacity-90' }}">
            <div class="flex items-start gap-3 min-w-0">
              {{-- Notification Icon --}}
              <div class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl font-bold text-xs" style="{{ $notification->isUnread() ? 'background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));' : 'background-color: rgb(var(--color-surface-soft)); color: rgb(var(--color-text-secondary));' }}">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
              </div>

              <div class="min-w-0 space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                  <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))] truncate">
                    {{ $notification->title }}
                  </h3>

                  @if ($notification->priority === 'urgent' || $notification->priority === 'high')
                  <span class="gdfh-badge text-[10px] bg-red-500/10 text-red-500 font-bold">عاجل</span>
                  @endif

                  @if ($notification->isUnread())
                  <span class="h-2 w-2 rounded-full bg-[rgb(var(--color-copper))]"></span>
                  @endif
                </div>

                <p class="text-xs text-[rgb(var(--color-text-secondary))] leading-5">
                  {{ $notification->description }}
                </p>

                <div class="flex items-center gap-3 text-[11px] text-[rgb(var(--color-text-secondary))] pt-1">
                  @if ($notification->sender)
                  <span>المُرسل: <strong>{{ $notification->sender->name }}</strong></span>
                  @endif
                  <span>{{ $notification->created_at->diffForHumans() }}</span>
                </div>
              </div>
            </div>

            {{-- Action Controls --}}
            <div class="flex items-center gap-2 shrink-0">
              @if ($notification->action_url)
              <form method="POST" action="{{ route('notifications.read', $notification) }}">
                @csrf
                <button type="submit" class="gdfh-btn gdfh-btn-brand text-xs py-1 px-3">
                  عرض
                </button>
              </form>
              @elseif ($notification->isUnread())
              <form method="POST" action="{{ route('notifications.read', $notification) }}">
                @csrf
                <button type="submit" class="gdfh-btn gdfh-btn-secondary text-xs py-1 px-3">
                  تحديد كمقروء
                </button>
              </form>
              @endif

              <form method="POST" action="{{ route('notifications.destroy', $notification) }}" onsubmit="return confirm('هل تريد حذف هذا الإشعار؟')">
                @csrf
                @method('DELETE')
                <button type="submit" class="gdfh-btn text-xs py-1 px-2.5 bg-red-500/10 text-red-500 hover:bg-red-500/20">
                  حذف
                </button>
              </form>
            </div>
          </div>
          @empty
          <div class="p-12 text-center space-y-3">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-[rgb(var(--color-surface-soft))] text-[rgb(var(--color-text-secondary))]">
              <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </div>
            <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">لا توجد إشعارات حالياً</h3>
            <p class="text-xs text-[rgb(var(--color-text-secondary))] max-w-sm mx-auto">ستظهر هنا كافة الإشعارات والتحذيرات الواردة حول مشاريعك ومهامك وفريقك.</p>
          </div>
          @endforelse
        </div>

        @if ($notifications->hasPages())
        <div class="border-t border-[rgb(var(--color-border))] p-4">
          {{ $notifications->links() }}
        </div>
        @endif
      </section>

    </div>
  </div>
</x-app-layout>
