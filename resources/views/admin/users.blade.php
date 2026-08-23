<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <p class="text-xs font-semibold text-[rgb(var(--color-copper))]">لوحة الإدارة العليا</p>
        <h2 class="mt-1 text-xl font-bold text-[rgb(var(--color-text-primary))]">إدارة حسابات المستخدمين</h2>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('admin.index') }}" class="gdfh-btn gdfh-btn-secondary text-xs px-3 py-1.5">← لوحة المشرف</a>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-6">
    <div class="mx-auto max-w-7xl space-y-6">

      {{-- Flash Messages --}}
      @if (session('success'))
      <div class="flex items-center gap-3 p-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 text-xs font-semibold" id="admin-success-msg">
        {{ session('success') }}
      </div>
      @endif
      @if (session('error'))
      <div class="flex items-center gap-3 p-4 rounded-xl border border-red-500/20 bg-red-500/10 text-red-700 dark:text-red-400 text-xs font-semibold">
        {{ session('error') }}
      </div>
      @endif

      {{-- Search/Filter Bar --}}
      <form method="GET" action="{{ route('admin.users') }}" class="gdfh-card p-4 flex flex-wrap items-center gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
          placeholder="ابحث بالاسم أو البريد الإلكتروني أو اسم المستخدم..."
          class="gdfh-input flex-1 min-w-48" id="admin-user-search">

        <select name="type" class="gdfh-input w-44">
          <option value="">جميع الأنواع</option>
          <option value="client" @selected(request('type') === 'client')>عميل</option>
          <option value="freelancer" @selected(request('type') === 'freelancer')>مستقل</option>
          <option value="banned" @selected(request('type') === 'banned')>محظور</option>
        </select>

        <button type="submit" class="gdfh-btn gdfh-btn-brand text-xs px-4">بحث</button>
        @if (request()->hasAny(['search', 'type']))
        <a href="{{ route('admin.users') }}" class="gdfh-btn gdfh-btn-secondary text-xs px-3">مسح</a>
        @endif
      </form>

      {{-- Users Table --}}
      <div class="gdfh-card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-[rgb(var(--color-border))]">
          <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">
            جميع الحسابات
            <span class="ms-2 text-xs font-normal text-[rgb(var(--color-text-secondary))]">({{ $users->total() }} حساب)</span>
          </h3>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-xs" id="admin-users-table">
            <thead>
              <tr class="border-b border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface-soft))]">
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">#</th>
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">الاسم والبريد</th>
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">اسم المستخدم</th>
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">النوع</th>
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">الصلاحية</th>
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">تاريخ التسجيل</th>
                <th class="px-4 py-3 text-start font-semibold text-[rgb(var(--color-text-secondary))]">الإجراءات</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[rgb(var(--color-border)/0.5)]">
              @forelse ($users as $u)
              <tr class="hover:bg-[rgb(var(--color-surface-soft))] transition">
                <td class="px-4 py-3 text-[rgb(var(--color-text-secondary))]">{{ $u->id }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-500/10 text-blue-500 font-bold text-[10px]">
                      {{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}
                    </div>
                    <div>
                      <div class="font-semibold text-[rgb(var(--color-text-primary))]">{{ $u->name }}</div>
                      <div class="text-[10px] text-[rgb(var(--color-text-secondary))]">{{ $u->email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 text-[rgb(var(--color-text-secondary))]">{{ $u->username ?? '—' }}</td>
                <td class="px-4 py-3">
                  @if ($u->is_banned)
                  <span class="gdfh-badge text-[10px] bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">محظور</span>
                  @elseif ($u->account_type === 'freelancer')
                  <span class="gdfh-badge gdfh-badge-mineral text-[10px]">مستقل</span>
                  @else
                  <span class="gdfh-badge gdfh-badge-copper text-[10px]">عميل</span>
                  @endif
                </td>
                <td class="px-4 py-3">
                  @if ($u->is_admin)
                  <span class="gdfh-badge gdfh-badge-copper text-[10px]">مدير النظام</span>
                  @else
                  <span class="text-[rgb(var(--color-text-secondary))]">—</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-[rgb(var(--color-text-secondary))]">{{ $u->created_at->format('Y-m-d') }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    @if ($u->id !== auth()->id())
                    {{-- Toggle Admin --}}
                    <form method="POST" action="{{ route('admin.users.toggle-admin', $u) }}">
                      @csrf
                      <button type="submit"
                        class="text-[10px] font-semibold px-2 py-1 rounded-lg border transition
                          {{ $u->is_admin ? 'border-amber-400/40 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20' : 'border-[rgb(var(--color-border))] text-[rgb(var(--color-text-secondary))] hover:border-[rgb(var(--color-copper)/0.4)] hover:text-[rgb(var(--color-copper))]' }}"
                        title="{{ $u->is_admin ? 'سحب صلاحية المدير' : 'منح صلاحية المدير' }}">
                        {{ $u->is_admin ? 'سحب المدير' : 'منح مدير' }}
                      </button>
                    </form>

                    {{-- Toggle Ban --}}
                    @if (!$u->is_admin)
                    <form method="POST" action="{{ route('admin.users.toggle-ban', $u) }}">
                      @csrf
                      <button type="submit"
                        class="text-[10px] font-semibold px-2 py-1 rounded-lg border transition
                          {{ $u->is_banned ? 'border-emerald-400/40 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20' : 'border-red-400/40 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20' }}"
                        title="{{ $u->is_banned ? 'رفع الحظر' : 'حظر الحساب' }}">
                        {{ $u->is_banned ? 'رفع الحظر' : 'حظر' }}
                      </button>
                    </form>
                    @endif
                    @else
                    <span class="text-[10px] text-[rgb(var(--color-text-secondary))]">أنت</span>
                    @endif
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="px-4 py-10 text-center text-[rgb(var(--color-text-secondary))]">لا توجد نتائج مطابقة للبحث.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if ($users->hasPages())
        <div class="px-5 py-4 border-t border-[rgb(var(--color-border))]">
          {{ $users->links() }}
        </div>
        @endif
      </div>

    </div>
  </div>
</x-app-layout>
