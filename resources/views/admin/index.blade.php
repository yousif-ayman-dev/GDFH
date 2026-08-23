<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <p class="text-xs font-semibold text-[rgb(var(--color-copper))]">لوحة الإدارة العليا</p>
        <h2 class="mt-1 text-xl font-bold text-[rgb(var(--color-text-primary))]">نظرة عامة على النظام</h2>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('admin.users') }}" class="gdfh-btn gdfh-btn-secondary text-xs px-3 py-1.5">إدارة المستخدمين</a>
        <a href="{{ route('admin.projects') }}" class="gdfh-btn gdfh-btn-secondary text-xs px-3 py-1.5">إدارة المشاريع</a>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-8">
    <div class="mx-auto max-w-7xl space-y-8">

      {{-- Flash Messages --}}
      @if (session('success'))
      <div class="flex items-center gap-3 p-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 text-xs font-semibold">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
      </div>
      @endif
      @if (session('error'))
      <div class="flex items-center gap-3 p-4 rounded-xl border border-red-500/20 bg-red-500/10 text-red-700 dark:text-red-400 text-xs font-semibold">
        {{ session('error') }}
      </div>
      @endif

      {{-- Admin Badge --}}
      <div class="flex items-center gap-3 p-4 rounded-2xl border border-[rgb(var(--color-copper)/0.30)] bg-[rgb(var(--color-copper-soft))]">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[rgb(var(--color-copper))] text-white">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <div>
          <div class="text-sm font-bold text-[rgb(var(--color-text-primary))]">لوحة تحكم النظام — System Administrator</div>
          <div class="text-xs text-[rgb(var(--color-text-secondary))] mt-0.5">أنت مسجل دخول كمدير النظام الشامل. تملك صلاحية الاطلاع على جميع البيانات وإدارة الحسابات.</div>
        </div>
      </div>

      {{-- System Stats Grid --}}
      <section>
        <h3 class="mb-4 text-sm font-bold text-[rgb(var(--color-text-primary))]">إحصائيات النظام الشاملة</h3>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">

          <div class="gdfh-card p-5 flex flex-col gap-2">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-500/10 text-blue-500">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div class="text-2xl font-black text-[rgb(var(--color-text-primary))]">{{ $stats['total_users'] }}</div>
            <div class="text-xs text-[rgb(var(--color-text-secondary))]">إجمالي المستخدمين</div>
          </div>

          <div class="gdfh-card p-5 flex flex-col gap-2">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))]">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div class="text-2xl font-black text-[rgb(var(--color-text-primary))]">{{ $stats['total_clients'] }}</div>
            <div class="text-xs text-[rgb(var(--color-text-secondary))]">العملاء</div>
          </div>

          <div class="gdfh-card p-5 flex flex-col gap-2">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
            </div>
            <div class="text-2xl font-black text-[rgb(var(--color-text-primary))]">{{ $stats['total_freelancers'] }}</div>
            <div class="text-xs text-[rgb(var(--color-text-secondary))]">المستقلون</div>
          </div>

          <div class="gdfh-card p-5 flex flex-col gap-2">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-500/10 text-violet-500">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            </div>
            <div class="text-2xl font-black text-[rgb(var(--color-text-primary))]">{{ $stats['total_projects'] }}</div>
            <div class="text-xs text-[rgb(var(--color-text-secondary))]">إجمالي المشاريع</div>
          </div>

          <div class="gdfh-card p-5 flex flex-col gap-2">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-500/10 text-sky-500">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="text-2xl font-black text-[rgb(var(--color-text-primary))]">{{ $stats['total_tasks'] }}</div>
            <div class="text-xs text-[rgb(var(--color-text-secondary))]">إجمالي المهام</div>
          </div>

          <div class="gdfh-card p-5 flex flex-col gap-2">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div class="text-2xl font-black text-[rgb(var(--color-text-primary))]">{{ $stats['total_contracts'] }}</div>
            <div class="text-xs text-[rgb(var(--color-text-secondary))]">إجمالي العقود</div>
          </div>

          <div class="gdfh-card p-5 flex flex-col gap-2">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))]">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div class="text-2xl font-black text-[rgb(var(--color-text-primary))]">{{ $stats['total_admins'] }}</div>
            <div class="text-xs text-[rgb(var(--color-text-secondary))]">مديرو النظام</div>
          </div>

        </div>
      </section>

      {{-- Quick Links --}}
      <section class="grid gap-4 sm:grid-cols-2">
        <a href="{{ route('admin.users') }}" id="admin-users-link"
          class="gdfh-card p-5 flex items-center gap-4 hover:border-[rgb(var(--color-copper)/0.4)] transition group">
          <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-500/10 text-blue-500 group-hover:scale-110 transition-transform">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
          </div>
          <div>
            <div class="font-bold text-[rgb(var(--color-text-primary))]">إدارة حسابات المستخدمين</div>
            <div class="text-xs text-[rgb(var(--color-text-secondary))] mt-0.5">استعراض جميع الحسابات، تعديل الأدوار، ومنح/سحب الصلاحيات أو حظر الحسابات.</div>
          </div>
        </a>

        <a href="{{ route('admin.projects') }}" id="admin-projects-link"
          class="gdfh-card p-5 flex items-center gap-4 hover:border-[rgb(var(--color-copper)/0.4)] transition group">
          <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-500/10 text-violet-500 group-hover:scale-110 transition-transform">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
          </div>
          <div>
            <div class="font-bold text-[rgb(var(--color-text-primary))]">إدارة مشاريع النظام</div>
            <div class="text-xs text-[rgb(var(--color-text-secondary))] mt-0.5">استعراض جميع المشاريع في المنصة بحسب الحالة والمالك والتاريخ.</div>
          </div>
        </a>
      </section>

      {{-- Recent Users --}}
      <section class="gdfh-card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-[rgb(var(--color-border))]">
          <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">آخر المستخدمين المسجلين</h3>
          <a href="{{ route('admin.users') }}" class="text-xs font-semibold text-[rgb(var(--color-copper))] hover:underline">عرض الكل</a>
        </div>
        <div class="divide-y divide-[rgb(var(--color-border)/0.5)]">
          @forelse ($recentUsers as $u)
          <div class="flex items-center gap-3 px-5 py-3">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-500/10 text-blue-500 text-xs font-bold">
              {{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
              <div class="text-xs font-semibold text-[rgb(var(--color-text-primary))] truncate">{{ $u->name }}</div>
              <div class="text-[10px] text-[rgb(var(--color-text-secondary))] truncate">{{ $u->email }}</div>
            </div>
            <div class="flex items-center gap-2">
              @if ($u->is_admin)
              <span class="gdfh-badge gdfh-badge-copper text-[10px]">مدير</span>
              @endif
              <span class="text-[10px] text-[rgb(var(--color-text-secondary))]">{{ $u->created_at->diffForHumans() }}</span>
            </div>
          </div>
          @empty
          <div class="px-5 py-8 text-center text-xs text-[rgb(var(--color-text-secondary))]">لا يوجد مستخدمون بعد.</div>
          @endforelse
        </div>
      </section>

    </div>
  </div>
</x-app-layout>
