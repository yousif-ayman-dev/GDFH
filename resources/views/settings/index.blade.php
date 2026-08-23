<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[rgb(var(--color-text-primary))]">
          إعدادات النظام والتفضيلات (Enterprise Settings)
        </h2>
        <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">
          إدارة الملف الشخصي، صورة الحساب، الأمان، تفضيلات الإشعارات والمظهر.
        </p>
      </div>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-6" x-data="{ activeTab: 'profile' }">
    <div class="mx-auto max-w-7xl space-y-6">

      {{-- Flash Feedback Messages --}}
      @if (session('status') === 'profile-updated')
      <div class="flex items-center justify-between p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-bold">
        <span>تم تحديث الملف الشخصي بنجاح.</span>
      </div>
      @endif

      @if (session('status') === 'avatar-deleted')
      <div class="flex items-center justify-between p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 text-xs font-bold">
        <span>تم حذف صورة الحساب بنجاح.</span>
      </div>
      @endif

      @if (session('status') === 'notifications-updated')
      <div class="flex items-center justify-between p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-bold">
        <span>تم حفظ تفضيلات الإشعارات بنجاح.</span>
      </div>
      @endif

      @if (session('status') === 'password-updated')
      <div class="flex items-center justify-between p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-bold">
        <span>تم تحديث كلمة المرور بنجاح.</span>
      </div>
      @endif

      {{-- Navigation Tabs --}}
      <div class="flex border-b border-[rgb(var(--color-border))] gap-2 overflow-x-auto">
        <button @click="activeTab = 'profile'"
          :class="activeTab === 'profile' ? 'border-[rgb(var(--color-copper))] text-[rgb(var(--color-copper))] font-bold' : 'border-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]'"
          class="px-4 py-3 text-xs border-b-2 transition whitespace-nowrap">
          الملف الشخصي والصورة
        </button>

        <button @click="activeTab = 'security'"
          :class="activeTab === 'security' ? 'border-[rgb(var(--color-copper))] text-[rgb(var(--color-copper))] font-bold' : 'border-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]'"
          class="px-4 py-3 text-xs border-b-2 transition whitespace-nowrap">
          الأمان وكلمة المرور
        </button>

        <button @click="activeTab = 'notifications'"
          :class="activeTab === 'notifications' ? 'border-[rgb(var(--color-copper))] text-[rgb(var(--color-copper))] font-bold' : 'border-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]'"
          class="px-4 py-3 text-xs border-b-2 transition whitespace-nowrap">
          تفضيلات الإشعارات
        </button>

        <button @click="activeTab = 'appearance'"
          :class="activeTab === 'appearance' ? 'border-[rgb(var(--color-copper))] text-[rgb(var(--color-copper))] font-bold' : 'border-transparent text-[rgb(var(--color-text-secondary))] hover:text-[rgb(var(--color-text-primary))]'"
          class="px-4 py-3 text-xs border-b-2 transition whitespace-nowrap">
          مظهر النظام
        </button>
      </div>

      {{-- TAB 1: PROFILE & AVATAR --}}
      <div x-show="activeTab === 'profile'" class="space-y-6">
        <div class="gdfh-card p-6 space-y-6">
          
          <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">صورة الحساب والمعلومات الأساسية</h3>

          {{-- Avatar Section --}}
          <div class="flex items-center gap-6 pb-6 border-b border-[rgb(var(--color-border))]">
            <div class="relative shrink-0">
              @if ($user->avatar_url)
              <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-20 w-20 rounded-full object-cover border-2 border-[rgb(var(--color-copper))] shadow-md">
              @else
              <div class="flex h-20 w-20 items-center justify-center rounded-full bg-[rgb(var(--color-copper-soft))] text-[rgb(var(--color-copper))] font-bold text-2xl border-2 border-[rgb(var(--color-border))]">
                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
              </div>
              @endif
            </div>

            <div class="space-y-2">
              <span class="block text-xs font-bold text-[rgb(var(--color-text-primary))]">الصورة الشخصية</span>
              <p class="text-[11px] text-[rgb(var(--color-text-secondary))]">الصورة المقبولة: JPG, PNG, WEBP (بحد أقصى 2 ميجابايت)</p>

              @if ($user->avatar_url)
              <form method="POST" action="{{ route('profile.avatar.destroy') }}" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-red-500 font-bold hover:underline">حذف الصورة الحالية</button>
              </form>
              @endif
            </div>
          </div>

          {{-- Profile Form --}}
          <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf
            @method('PATCH')

            <div>
              <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">رفع صورة جديدة</label>
              <input type="file" name="avatar" accept="image/jpeg,image/png,image/jpg,image/webp" class="w-full text-xs text-[rgb(var(--color-text-secondary))] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[rgb(var(--color-copper-soft))] file:text-[rgb(var(--color-copper))] hover:file:opacity-80">
              <x-input-error class="mt-1" :messages="$errors->get('avatar')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">الاسم الكامل *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:border-[rgb(var(--color-copper))] focus:outline-none">
                <x-input-error class="mt-1" :messages="$errors->get('name')" />
              </div>

              <div>
                <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">البريد الإلكتروني *</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:border-[rgb(var(--color-copper))] focus:outline-none">
                <x-input-error class="mt-1" :messages="$errors->get('email')" />
              </div>
            </div>

            <div>
              <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">نبذة عنك (Bio)</label>
              <textarea name="bio" rows="3" placeholder="اكتب نبذة مختصرة عن مؤهلاتك أو اهتماماتك..." class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:border-[rgb(var(--color-copper))] focus:outline-none">{{ old('bio', $user->bio) }}</textarea>
              <x-input-error class="mt-1" :messages="$errors->get('bio')" />
            </div>

            <div class="pt-2">
              <button type="submit" class="gdfh-btn gdfh-btn-brand py-2 px-6 font-bold shadow-sm">
                حفظ التغييرات
              </button>
            </div>
          </form>

        </div>
      </div>

      {{-- TAB 2: SECURITY & PASSWORD --}}
      <div x-show="activeTab === 'security'" class="space-y-6">
        <div class="gdfh-card p-6 space-y-6">
          <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">تحديث كلمة المرور</h3>

          <form method="POST" action="{{ route('password.update') }}" class="space-y-4 text-xs max-w-xl">
            @csrf
            @method('PUT')

            <div>
              <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">كلمة المرور الحالية</label>
              <input type="password" name="current_password" required autocomplete="current-password" class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:border-[rgb(var(--color-copper))] focus:outline-none">
              <x-input-error class="mt-1" :messages="$errors->updatePassword->get('current_password')" />
            </div>

            <div>
              <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">كلمة المرور الجديدة</label>
              <input type="password" name="password" required autocomplete="new-password" class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:border-[rgb(var(--color-copper))] focus:outline-none">
              <x-input-error class="mt-1" :messages="$errors->updatePassword->get('password')" />
            </div>

            <div>
              <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">تأكيد كلمة المرور الجديدة</label>
              <input type="password" name="password_confirmation" required autocomplete="new-password" class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:border-[rgb(var(--color-copper))] focus:outline-none">
              <x-input-error class="mt-1" :messages="$errors->updatePassword->get('password_confirmation')" />
            </div>

            <div class="pt-2">
              <button type="submit" class="gdfh-btn gdfh-btn-brand py-2 px-6 font-bold shadow-sm">
                تحديث كلمة المرور
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- TAB 3: NOTIFICATIONS --}}
      <div x-show="activeTab === 'notifications'" class="space-y-6">
        <div class="gdfh-card p-6 space-y-6">
          <div>
            <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">تفضيلات الإشعارات والتنبيهات</h3>
            <p class="text-xs text-[rgb(var(--color-text-secondary))] mt-1">حدد الإشعارات التي ترغب بتلقيها عبر البريد الإلكتروني أو داخل المنصة.</p>
          </div>

          <form method="POST" action="{{ route('settings.notifications.update') }}" class="space-y-4 text-xs">
            @csrf
            @method('PATCH')

            <div class="space-y-3 max-w-xl border-y border-[rgb(var(--color-border))] py-4">
              
              <label class="flex items-center justify-between cursor-pointer">
                <div>
                  <span class="block font-bold text-[rgb(var(--color-text-primary))]">إشعارات البريد الإلكتروني</span>
                  <span class="text-[11px] text-[rgb(var(--color-text-secondary))]">تلقي ملخص المواعيد والتحديثات عبر البريد</span>
                </div>
                <input type="checkbox" name="preferences[email]" value="1" {{ $user->getNotificationPreference('email', true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-[rgb(var(--color-copper))] focus:ring-[rgb(var(--color-copper))]">
              </label>

              <label class="flex items-center justify-between cursor-pointer pt-3 border-t border-[rgb(var(--color-border))]">
                <div>
                  <span class="block font-bold text-[rgb(var(--color-text-primary))]">الإشعارات داخل المنصة</span>
                  <span class="text-[11px] text-[rgb(var(--color-text-secondary))]">إظهار شارات التنبيه في القائمة العلوية</span>
                </div>
                <input type="checkbox" name="preferences[in_app]" value="1" {{ $user->getNotificationPreference('in_app', true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-[rgb(var(--color-copper))] focus:ring-[rgb(var(--color-copper))]">
              </label>

              <label class="flex items-center justify-between cursor-pointer pt-3 border-t border-[rgb(var(--color-border))]">
                <div>
                  <span class="block font-bold text-[rgb(var(--color-text-primary))]">تنبيهات إسناد المهام</span>
                  <span class="text-[11px] text-[rgb(var(--color-text-secondary))]">تنبيه فوري عند إسناد مهمة جديدة لك</span>
                </div>
                <input type="checkbox" name="preferences[task_assigned]" value="1" {{ $user->getNotificationPreference('task_assigned', true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-[rgb(var(--color-copper))] focus:ring-[rgb(var(--color-copper))]">
              </label>

              <label class="flex items-center justify-between cursor-pointer pt-3 border-t border-[rgb(var(--color-border))]">
                <div>
                  <span class="block font-bold text-[rgb(var(--color-text-primary))]">دعوات الفرق والمشاريع</span>
                  <span class="text-[11px] text-[rgb(var(--color-text-secondary))]">تنبيه عند دعوتم للانضمام لفريق عمل</span>
                </div>
                <input type="checkbox" name="preferences[team_invite]" value="1" {{ $user->getNotificationPreference('team_invite', true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-[rgb(var(--color-copper))] focus:ring-[rgb(var(--color-copper))]">
              </label>

            </div>

            <div class="pt-2">
              <button type="submit" class="gdfh-btn gdfh-btn-brand py-2 px-6 font-bold shadow-sm">
                حفظ تفضيلات الإشعارات
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- TAB 4: APPEARANCE / THEME --}}
      <div x-show="activeTab === 'appearance'" class="space-y-6">
        <div class="gdfh-card p-6 space-y-6">
          <div>
            <h3 class="text-sm font-bold text-[rgb(var(--color-text-primary))]">مظهر واجهة المنصة (Theme)</h3>
            <p class="text-xs text-[rgb(var(--color-text-secondary))] mt-1">اختر النمط البصري المفضل لـ Tasker.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-2xl">
            <button type="button" @click="$store.theme.setTheme('light')"
              :class="$store.theme.theme === 'light' ? 'border-[rgb(var(--color-copper))] ring-2 ring-[rgb(var(--color-copper))] bg-slate-50 text-slate-900' : 'border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] text-[rgb(var(--color-text-primary))]'"
              class="p-4 rounded-2xl border text-center space-y-2 transition hover:opacity-90">
              <div class="flex h-10 w-10 mx-auto items-center justify-center rounded-xl bg-amber-500/10 text-amber-500">
                ☀️
              </div>
              <span class="block text-xs font-bold">الوضع الفاتح (Light)</span>
            </button>

            <button type="button" @click="$store.theme.setTheme('dark')"
              :class="$store.theme.theme === 'dark' ? 'border-[rgb(var(--color-copper))] ring-2 ring-[rgb(var(--color-copper))] bg-slate-900 text-white' : 'border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] text-[rgb(var(--color-text-primary))]'"
              class="p-4 rounded-2xl border text-center space-y-2 transition hover:opacity-90">
              <div class="flex h-10 w-10 mx-auto items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400">
                🌙
              </div>
              <span class="block text-xs font-bold">الوضع الداكن (Dark)</span>
            </button>

            <button type="button" @click="$store.theme.setTheme('system')"
              :class="$store.theme.theme === 'system' ? 'border-[rgb(var(--color-copper))] ring-2 ring-[rgb(var(--color-copper))] bg-slate-800 text-slate-200' : 'border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] text-[rgb(var(--color-text-primary))]'"
              class="p-4 rounded-2xl border text-center space-y-2 transition hover:opacity-90">
              <div class="flex h-10 w-10 mx-auto items-center justify-center rounded-xl bg-blue-500/10 text-blue-400">
                💻
              </div>
              <span class="block text-xs font-bold">تلقائي حسب الجهاز (System)</span>
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>
</x-app-layout>
