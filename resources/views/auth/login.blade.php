<x-guest-layout>
    <div class="mb-6 text-center space-y-2">
        <h2 class="text-xl font-bold tracking-tight text-[rgb(var(--color-text-primary))]">تسجيل الدخول إلى حسابك</h2>
        <p class="text-xs text-[rgb(var(--color-text-secondary))]">أدخل بريدك الإلكتروني وكلمة المرور للوصول إلى منصة Tasker</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-bold text-[rgb(var(--color-text-primary))] mb-1.5">البريد الإلكتروني *</label>
            <input id="email" class="gdfh-input text-xs" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="example@tasker.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-bold text-[rgb(var(--color-text-primary))]">كلمة المرور *</label>
                @if (Route::has('password.request'))
                    <a class="text-xs font-semibold text-[#2B58A8] hover:underline" href="{{ route('password.request') }}">
                        نسيت كلمة المرور؟
                    </a>
                @endif
            </div>
            <input id="password" class="gdfh-input text-xs" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-[rgb(var(--color-border))] text-[#2B58A8] focus:ring-[#2B58A8]" name="remember">
                <span class="text-xs text-[rgb(var(--color-text-secondary))]">تذكر تسجيل دخولي</span>
            </label>
        </div>

        <div class="pt-2 space-y-2">
            <button type="submit" class="gdfh-btn gdfh-btn-brand w-full font-bold py-3 text-sm shadow-md" id="login-submit-btn">
                تسجيل الدخول
            </button>

            <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center gap-2.5 rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] hover:bg-[rgb(var(--color-surface-soft))] py-2.5 px-4 text-xs font-bold text-[rgb(var(--color-text-primary))] shadow-sm transition">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg>
                <span>تسجيل الدخول باستخدام Google</span>
            </a>
        </div>
    </form>

    {{-- Register Redirect Box --}}
    @if (Route::has('register'))
    <div class="mt-6 pt-6 border-t border-[rgb(var(--color-border))] text-center">
        <p class="text-xs text-[rgb(var(--color-text-secondary))] mb-3">ليس لديك حساب على المنصة بعد؟</p>
        <a href="{{ route('register') }}" class="gdfh-btn gdfh-btn-secondary w-full text-xs font-bold py-2.5" id="register-redirect-btn">
            إنشاء حساب جديد الآن ✨
        </a>
    </div>
    @endif
</x-guest-layout>
