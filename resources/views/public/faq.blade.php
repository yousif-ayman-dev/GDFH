<x-guest-layout>
  <x-slot name="title">الأسئلة الشائعة (FAQ) | منصة Tasker Enterprise</x-slot>
  <x-slot name="metaDescription">إجابات عن أكثر الأسئلة شيوعاً حول منصة Tasker: كيفية إنشاء المشاريع، التوظيف المستقل، الأمان، وإدارة الفرق.</x-slot>

  <div class="space-y-6 text-center">
    <div class="space-y-2">
      <span class="gdfh-badge gdfh-badge-copper text-xs px-3 py-1 font-bold">الأسئلة الشائعة — FAQ</span>
      <h1 class="text-2xl font-black tracking-tight text-[rgb(var(--color-text-primary))] sm:text-3xl">
        إجابات عن تساؤلاتك حول Tasker
      </h1>
      <p class="text-xs leading-relaxed text-[rgb(var(--color-text-secondary))]">
        كل ما تحتاج معرفته حول طريقة العمل، إنشاء الحساب، والبدء في المنصة.
      </p>
    </div>

    <div class="space-y-3 text-start text-xs border-t border-b border-[rgb(var(--color-border))] py-6">
      <div class="p-3 rounded-xl bg-[rgb(var(--color-surface-soft))]">
        <h3 class="font-bold text-[rgb(var(--color-text-primary))]">كيف يمكنني التسجيل كعميل أو مستقل؟</h3>
        <p class="mt-1 text-[rgb(var(--color-text-secondary))]">عند إنشاء حسابك الجديد، ستتمكن في الخطوة الأولى من اختيار نوع الحساب واستكمال ملفك الشخصي.</p>
      </div>

      <div class="p-3 rounded-xl bg-[rgb(var(--color-surface-soft))]">
        <h3 class="font-bold text-[rgb(var(--color-text-primary))]">هل تتوفر أدوات الذكاء الاصطناعي مجاناً؟</h3>
        <p class="mt-1 text-[rgb(var(--color-text-secondary))]">نعم، يتيح مساعد Tasker Copilot إمكانية استشارة الذكاء الاصطناعي وتحليل بيئة العمل مجاناً.</p>
      </div>

      <div class="p-3 rounded-xl bg-[rgb(var(--color-surface-soft))]">
        <h3 class="font-bold text-[rgb(var(--color-text-primary))]">كيف تضمن المنصة سرية وأمان البيانات؟</h3>
        <p class="mt-1 text-[rgb(var(--color-text-secondary))]">تطبق المنصة أعلى معايير التشفير (Bcrypt, HTTPS, CSRF Protection) وحساسية البيانات لمنع أي تسريب.</p>
      </div>
    </div>

    <div class="flex items-center justify-center gap-3 pt-2">
      <a href="{{ route('login') }}" class="gdfh-btn gdfh-btn-brand text-xs font-bold px-6 py-2.5 shadow-md">
        تسجيل الدخول إلى حسابك
      </a>
      <a href="{{ route('public.about') }}" class="gdfh-btn gdfh-btn-secondary text-xs font-bold px-4 py-2.5">
        عن المنصة ←
      </a>
    </div>
  </div>
</x-guest-layout>
