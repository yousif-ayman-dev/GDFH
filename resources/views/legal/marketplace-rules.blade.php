<x-guest-layout>
  <x-slot name="title">قواعد وإرشادات سوق العمل | منصة Tasker Enterprise</x-slot>
  <x-slot name="metaDescription">القواعد السلوكية والإرشادات الاحترافية للتعامل وإبرام العقود وتقديم العروض على منصة Tasker.</x-slot>
  <x-slot name="maxWidth">max-w-4xl</x-slot>

  <div class="space-y-8 text-sm text-[rgb(var(--color-text-primary))] leading-relaxed" dir="rtl">
    <!-- Header -->
    <div class="border-b border-[rgb(var(--color-border))] pb-6 space-y-2">
      <div class="flex items-center gap-2">
        <span class="gdfh-badge gdfh-badge-copper text-xs px-3 py-1 font-bold">الوثائق والسياسات الرسمية</span>
        <span class="text-xs text-[rgb(var(--color-text-secondary))]">ميثاق العمل الاحترافي</span>
      </div>
      <h1 class="text-2xl sm:text-3xl font-black text-[rgb(var(--color-text-primary))]">
        قواعد وإرشادات سوق العمل والخدمات المصغرة
      </h1>
      <p class="text-xs text-[rgb(var(--color-text-secondary))]">
        تاريخ آخر تحديث: 2026-11-11 | المعايير السلوكية والمهنية لضمان تجربة آمنة وناجحة لجميع المستقلين والشركات.
      </p>
    </div>

    <!-- Quick Navigation Pills -->
    <div class="flex flex-wrap gap-2 text-xs">
      <a href="{{ route('legal.terms') }}" class="px-3 py-1.5 rounded-lg font-medium bg-[rgb(var(--color-surface-soft))] hover:bg-[rgb(var(--color-border))] text-[rgb(var(--color-text-secondary))] transition">شروط الاستخدام</a>
      <a href="{{ route('legal.privacy') }}" class="px-3 py-1.5 rounded-lg font-medium bg-[rgb(var(--color-surface-soft))] hover:bg-[rgb(var(--color-border))] text-[rgb(var(--color-text-secondary))] transition">سياسة الخصوصية</a>
      <a href="{{ route('legal.payment-policy') }}" class="px-3 py-1.5 rounded-lg font-medium bg-[rgb(var(--color-surface-soft))] hover:bg-[rgb(var(--color-border))] text-[rgb(var(--color-text-secondary))] transition">سياسة المدفوعات والعمولات</a>
      <a href="{{ route('legal.refund-policy') }}" class="px-3 py-1.5 rounded-lg font-medium bg-[rgb(var(--color-surface-soft))] hover:bg-[rgb(var(--color-border))] text-[rgb(var(--color-text-secondary))] transition">سياسة الاسترداد والنزاعات</a>
      <a href="{{ route('legal.marketplace-rules') }}" class="px-3 py-1.5 rounded-lg font-bold bg-[rgb(var(--color-copper))] text-white">قواعد سوق العمل</a>
    </div>

    <!-- Policy Content Sections -->
    <div class="space-y-6">
      <section class="p-5 rounded-2xl bg-[rgb(var(--color-surface-soft))] border border-[rgb(var(--color-border))] space-y-3">
        <h2 class="font-bold text-base text-[rgb(var(--color-text-primary))] flex items-center gap-2">
          <span class="w-6 h-6 rounded-full bg-[rgb(var(--color-copper))]/10 text-[rgb(var(--color-copper))] text-xs flex items-center justify-center font-black">1</span>
          المصداقية في تقديم العروض والمشاريع
        </h2>
        <ul class="text-xs text-[rgb(var(--color-text-secondary))] space-y-2 list-disc list-inside">
          <li>يجب أن تتضمن تفاصيل المشروع وصفاً دقيقاً للمتطلبات والمخرجات المتوقعة بدون غموض.</li>
          <li>يجب أن يعكس عرض المستقل كفاءته الحقيقية، والجدول الزمني الفعلي للتنفيذ والميزانية الصادقة.</li>
          <li>يُحظر وضع عروض أسعار وهمية بقصد تصدر القوائم ثم محاولة مضاعفة السعر لاحقاً.</li>
        </ul>
      </section>

      <section class="p-5 rounded-2xl bg-[rgb(var(--color-surface-soft))] border border-[rgb(var(--color-border))] space-y-3">
        <h2 class="font-bold text-base text-[rgb(var(--color-text-primary))] flex items-center gap-2">
          <span class="w-6 h-6 rounded-full bg-[rgb(var(--color-copper))]/10 text-[rgb(var(--color-copper))] text-xs flex items-center justify-center font-black">2</span>
          حظر التعاملات خارج المنصة
        </h2>
        <p class="text-xs text-[rgb(var(--color-text-secondary))] leading-normal">
          نظام الرسائل الداخلي وعقود المنصة صُممت لحمايتك. يُعد طلب وسائل اتصال خارجية (مثل أرقام الهواتف أو تيليجرام أو البريد المباشر) أو محاولة الدفع خارج النظام مخالفة جسيمة تؤدي إلى إيقاف العقد وتجميد الحسابات فوراً.
        </p>
      </section>

      <section class="p-5 rounded-2xl bg-[rgb(var(--color-surface-soft))] border border-[rgb(var(--color-border))] space-y-3">
        <h2 class="font-bold text-base text-[rgb(var(--color-text-primary))] flex items-center gap-2">
          <span class="w-6 h-6 rounded-full bg-[rgb(var(--color-copper))]/10 text-[rgb(var(--color-copper))] text-xs flex items-center justify-center font-black">3</span>
          التقييمات والمراجعات المهنية
        </h2>
        <p class="text-xs text-[rgb(var(--color-text-secondary))] leading-normal">
          نظام التقييم والتعليقات يبني سمعة المستقل ومصداقية العميل. يجب أن تعبر التقييمات عن التجربة الحقيقية بموضوعية، ويُحظر تماماً ابتزاز الطرف الآخر بالتقييم السلبي لفرض شروط خارج بنود العقد.
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
        <a href="{{ route('legal.payment-policy') }}" class="hover:text-[rgb(var(--color-copper))]">المدفوعات</a>
      </div>
    </div>
  </div>
</x-guest-layout>
