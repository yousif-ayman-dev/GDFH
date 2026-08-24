<x-guest-layout>
    <div class="mb-6 text-center space-y-2">
        <h2 class="text-xl font-bold tracking-tight text-[rgb(var(--color-text-primary))]">إنشاء حساب جديد في Tasker</h2>
        <p class="text-xs text-[rgb(var(--color-text-secondary))]">انضم الآن لإدارة الأعمال، المشاريع، والتوظيف المستقل بسهولة</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-xs font-bold text-[rgb(var(--color-text-primary))] mb-1.5">الاسم الكامل *</label>
            <input id="name" class="gdfh-input text-xs" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="محمد أحمد" />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-bold text-[rgb(var(--color-text-primary))] mb-1.5">البريد الإلكتروني *</label>
            <input id="email" class="gdfh-input text-xs" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="user@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-bold text-[rgb(var(--color-text-primary))] mb-1.5">كلمة المرور *</label>
            <input id="password" class="gdfh-input text-xs" type="password" name="password" required autocomplete="new-password" placeholder="8 أحرف أو أكثر" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-xs font-bold text-[rgb(var(--color-text-primary))] mb-1.5">تأكيد كلمة المرور *</label>
            <input id="password_confirmation" class="gdfh-input text-xs" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="أعد إدخال كلمة المرور" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="pt-2">
            <button type="submit" class="gdfh-btn gdfh-btn-brand w-full font-bold py-3 text-sm shadow-md" id="register-submit-btn">
                إنشاء الحساب والمتابعة ✨
            </button>
        </div>
    </form>

    <div class="mt-6 pt-6 border-t border-[rgb(var(--color-border))] text-center">
        <p class="text-xs text-[rgb(var(--color-text-secondary))] mb-2">لديك حساب مسجل بالفعل؟</p>
        <a href="{{ route('login') }}" class="text-xs font-bold text-[#2B58A8] hover:underline" id="login-redirect-link">
            تسجيل الدخول إلى حسابك الحالي
        </a>
    </div>
</x-guest-layout>
