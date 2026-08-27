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
