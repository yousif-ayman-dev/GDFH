<x-guest-layout>
    <div class="mb-6 text-center space-y-2">
        <h2 class="text-xl font-bold tracking-tight text-[rgb(var(--color-text-primary))]">تأكيد البريد الإلكتروني ✉️</h2>
        <p class="text-xs text-[rgb(var(--color-text-secondary))] leading-relaxed">
            شكراً لتسجيلك في منصة Tasker! تم إرسال رابط التأكيد إلى بريدك الإلكتروني. يرجى الضغط على الرابط في الرسالة لتفعيل حسابك والوصول إلى المنصة.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-bold text-center">
            تم إرسال رابط تأكيد جديد إلى بريدك الإلكتروني بنجاح! 🚀
        </div>
    @endif

    <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
        <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
            @csrf
            <button type="submit" class="gdfh-btn gdfh-btn-brand w-full text-xs font-bold py-2.5 px-4 shadow-sm">
                إعادة إرسال رابط التأكيد
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
            @csrf
            <button type="submit" class="gdfh-btn gdfh-btn-secondary w-full text-xs font-bold py-2.5 px-4">
                تسجيل الخروج
            </button>
        </form>
    </div>
</x-guest-layout>
