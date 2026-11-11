<x-guest-layout>
  <x-slot name="title">سياسة المدفوعات والعمولات | منصة Tasker Enterprise</x-slot>
  <x-slot name="metaDescription">السياسة المالية المعتمدة لاحتساب العمولات، معالجة الدفع، وسداد مستحقات المستقلين على منصة Tasker.</x-slot>
  <x-slot name="maxWidth">max-w-4xl</x-slot>

  <div class="space-y-8 text-sm text-[rgb(var(--color-text-primary))] leading-relaxed" dir="rtl">
    <!-- Header -->
    <div class="border-b border-[rgb(var(--color-border))] pb-6 space-y-2">
      <div class="flex items-center gap-2">
        <span class="gdfh-badge gdfh-badge-copper text-xs px-3 py-1 font-bold">الوثائق والسياسات الرسمية</span>
        <span class="text-xs text-[rgb(var(--color-text-secondary))]">الشفافية المالية</span>
      </div>
      <h1 class="text-2xl sm:text-3xl font-black text-[rgb(var(--color-text-primary))]">
        سياسة المدفوعات والعمولات والفوترة
      </h1>
      <p class="text-xs text-[rgb(var(--color-text-secondary))]">
        تاريخ آخر تحديث: 2026-11-11 | توضح هذه السياسة آلية سداد المشاريع والخدمات، واحتساب عمولة المنصة التشغيلية.
      </p>
    </div>

    <!-- Quick Navigation Pills -->
    <div class="flex flex-wrap gap-2 text-xs">
      <a href="{{ route('legal.terms') }}" class="px-3 py-1.5 rounded-lg font-medium bg-[rgb(var(--color-surface-soft))] hover:bg-[rgb(var(--color-border))] text-[rgb(var(--color-text-secondary))] transition">شروط الاستخدام</a>
      <a href="{{ route('legal.privacy') }}" class="px-3 py-1.5 rounded-lg font-medium bg-[rgb(var(--color-surface-soft))] hover:bg-[rgb(var(--color-border))] text-[rgb(var(--color-text-secondary))] transition">سياسة الخصوصية</a>
      <a href="{{ route('legal.payment-policy') }}" class="px-3 py-1.5 rounded-lg font-bold bg-[rgb(var(--color-copper))] text-white">سياسة المدفوعات والعمولات</a>
      <a href="{{ route('legal.refund-policy') }}" class="px-3 py-1.5 rounded-lg font-medium bg-[rgb(var(--color-surface-soft))] hover:bg-[rgb(var(--color-border))] text-[rgb(var(--color-text-secondary))] transition">سياسة الاسترداد والنزاعات</a>
      <a href="{{ route('legal.marketplace-rules') }}" class="px-3 py-1.5 rounded-lg font-medium bg-[rgb(var(--color-surface-soft))] hover:bg-[rgb(var(--color-border))] text-[rgb(var(--color-text-secondary))] transition">قواعد سوق العمل</a>
    </div>

    <!-- Policy Content Sections -->
    <div class="space-y-6">
      <section class="p-5 rounded-2xl bg-[rgb(var(--color-surface-soft))] border border-[rgb(var(--color-border))] space-y-3">
        <h2 class="font-bold text-base text-[rgb(var(--color-text-primary))] flex items-center gap-2">
          <span class="w-6 h-6 rounded-full bg-[rgb(var(--color-copper))]/10 text-[rgb(var(--color-copper))] text-xs flex items-center justify-center font-black">1</span>
          آلية سداد العقود والخدمات
        </h2>
        <p class="text-xs text-[rgb(var(--color-text-secondary))] leading-normal">
          عند موافقة العميل على عرض المستقل، يُطلب سداد قيمة العقد عبر إحدى بوابات الدفع الإلكترونية المعتمدة في النظام. يتم توثيق العملية وتأكيد حالة العقد كـ "مسدد ومُعتمد" لتبدأ مرحلة التنفيذ الفعلي بكل أمان وموثوقية للطرفين.
        </p>
      </section>

      <section class="p-5 rounded-2xl bg-[rgb(var(--color-surface-soft))] border border-[rgb(var(--color-border))] space-y-3">
        <h2 class="font-bold text-base text-[rgb(var(--color-text-primary))] flex items-center gap-2">
          <span class="w-6 h-6 rounded-full bg-[rgb(var(--color-copper))]/10 text-[rgb(var(--color-copper))] text-xs flex items-center justify-center font-black">2</span>
          عمولة المنصة التشغيلية (Platform Commission)
        </h2>
        <p class="text-xs text-[rgb(var(--color-text-secondary))] leading-normal">
          تقتطع المنصة عمولة تشغيلية قدرها **10%** (ما لم تنص الاتفاقيات المؤسسية الخاصة على خلاف ذلك) من إجمالي قيمة العقد. تُخصص هذه النسبة لتغطية تكاليف التشغيل السحابي، بوابات الدفع الإلكتروني، التطوير التقني المستمر، وخدمات الدعم والتحكيم الفني.
        </p>
      </section>

      <section class="p-5 rounded-2xl bg-[rgb(var(--color-surface-soft))] border border-[rgb(var(--color-border))] space-y-3">
        <h2 class="font-bold text-base text-[rgb(var(--color-text-primary))] flex items-center gap-2">
          <span class="w-6 h-6 rounded-full bg-[rgb(var(--color-copper))]/10 text-[rgb(var(--color-copper))] text-xs flex items-center justify-center font-black">3</span>
          صرف مستحقات المستقلين
        </h2>
        <p class="text-xs text-[rgb(var(--color-text-secondary))] leading-normal">
          يتم احتساب صافي المستقل (قيمة العقد مخصوماً منها نسبة العمولة) وتحويله إلى رصيد حسابه فور اعتماد العميل لتسليم المشروع النهائي. يُتاح سحب الأرباح عبر القنوات البنكية أو المحافظ الإلكترونية المعتمدة بعد انقضاء فترة الأمان القياسية.
        </p>
      </section>
    </div>

    <!-- Footer Links -->
    <div class="pt-6 border-t border-[rgb(var(--color-border))] flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
      <a href="/" class="text-[rgb(var(--color-copper))] font-bold hover:underline flex items-center gap-1">
        <span>← العودة إلى الصفحة الرئيسية</span>
      </a>
      <div class="flex items-center gap-4 text-[rgb(var(--color-text-secondary))]">
        <a href="{{ route('legal.terms') }}" class="hover:text-[rgb(var(--color-copper))]">شروط الاستخدام</a>
        <span>•</span>
        <a href="{{ route('legal.privacy') }}" class="hover:text-[rgb(var(--color-copper))]">سياسة الخصوصية</a>
        <span>•</span>
        <a href="{{ route('legal.refund-policy') }}" class="hover:text-[rgb(var(--color-copper))]">الاسترداد</a>
      </div>
    </div>
  </div>
</x-guest-layout>
