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
        <div x-data="{ showPassword: false }">
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-bold text-[rgb(var(--color-text-primary))]">كلمة المرور *</label>
                @if (Route::has('password.request'))
                    <a class="text-xs font-semibold text-[#2B58A8] hover:underline" href="{{ route('password.request') }}">
                        نسيت كلمة المرور؟
                    </a>
                @endif
            </div>
            <div class="relative">
                <input id="password" :type="showPassword ? 'text' : 'password'" class="gdfh-input text-xs pl-10" name="password" required autocomplete="current-password" placeholder="••••••••" />
                <button type="button" @click="showPassword = !showPassword" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 focus:outline-none transition p-1">
                    <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a8.959 8.959 0 013.682-.787c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m-1.74-2.222a3 3 0 00-4.24-4.24M3 3l18 18"/></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-[rgb(var(--color-border))] text-[#2B58A8] focus:ring-[#2B58A8]" name="remember">
                <span class="text-xs text-[rgb(var(--color-text-secondary))]">تذكر تسجيل دخولي</span>
            </label>
        </div>

        <div class="pt-2">
            <button type="submit" class="gdfh-btn gdfh-btn-brand w-full font-bold py-3 text-sm shadow-md" id="login-submit-btn">
                تسجيل الدخول
            </button>
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
