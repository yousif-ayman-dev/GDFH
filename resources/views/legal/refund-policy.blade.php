<x-guest-layout>
  <x-slot name="title">سياسة الاسترداد وإلغاء العقود | منصة Tasker Enterprise</x-slot>
  <x-slot name="metaDescription">الشروط والضوابط المنظمة لطلبات استرداد الأموال وإلغاء العقود في منصة Tasker Enterprise.</x-slot>
  <x-slot name="maxWidth">max-w-4xl</x-slot>

  <div class="space-y-8 text-sm text-[rgb(var(--color-text-primary))] leading-relaxed" dir="rtl">
    <!-- Header -->
    <div class="border-b border-[rgb(var(--color-border))] pb-6 space-y-2">
      <div class="flex items-center gap-2">
        <span class="gdfh-badge gdfh-badge-copper text-xs px-3 py-1 font-bold">الوثائق والسياسات الرسمية</span>
        <span class="text-xs text-[rgb(var(--color-text-secondary))]">حماية الحقوق والمستحقات</span>
      </div>
      <h1 class="text-2xl sm:text-3xl font-black text-[rgb(var(--color-text-primary))]">
        سياسة الاسترداد وإلغاء العقود والنزاعات
      </h1>
      <p class="text-xs text-[rgb(var(--color-text-secondary))]">
        تاريخ آخر تحديث: 2026-11-11 | تضمن هذه السياسة حماية أموال العملاء وحقوق المستقلين في حال تعذر استكمال الأعمال.
      </p>
    </div>

    <!-- Quick Navigation Pills -->
    <div class="flex flex-wrap gap-2 text-xs">
      <a href="{{ route('legal.terms') }}" class="px-3 py-1.5 rounded-lg font-medium bg-[rgb(var(--color-surface-soft))] hover:bg-[rgb(var(--color-border))] text-[rgb(var(--color-text-secondary))] transition">شروط الاستخدام</a>
      <a href="{{ route('legal.privacy') }}" class="px-3 py-1.5 rounded-lg font-medium bg-[rgb(var(--color-surface-soft))] hover:bg-[rgb(var(--color-border))] text-[rgb(var(--color-text-secondary))] transition">سياسة الخصوصية</a>
      <a href="{{ route('legal.payment-policy') }}" class="px-3 py-1.5 rounded-lg font-medium bg-[rgb(var(--color-surface-soft))] hover:bg-[rgb(var(--color-border))] text-[rgb(var(--color-text-secondary))] transition">سياسة المدفوعات والعمولات</a>
      <a href="{{ route('legal.refund-policy') }}" class="px-3 py-1.5 rounded-lg font-bold bg-[rgb(var(--color-copper))] text-white">سياسة الاسترداد والنزاعات</a>
      <a href="{{ route('legal.marketplace-rules') }}" class="px-3 py-1.5 rounded-lg font-medium bg-[rgb(var(--color-surface-soft))] hover:bg-[rgb(var(--color-border))] text-[rgb(var(--color-text-secondary))] transition">قواعد سوق العمل</a>
    </div>

    <!-- Policy Content Sections -->
    <div class="space-y-6">
      <section class="p-5 rounded-2xl bg-[rgb(var(--color-surface-soft))] border border-[rgb(var(--color-border))] space-y-3">
        <h2 class="font-bold text-base text-[rgb(var(--color-text-primary))] flex items-center gap-2">
          <span class="w-6 h-6 rounded-full bg-[rgb(var(--color-copper))]/10 text-[rgb(var(--color-copper))] text-xs flex items-center justify-center font-black">1</span>
          الحالات المستحقة للاسترداد الكامل
        </h2>
        <ul class="text-xs text-[rgb(var(--color-text-secondary))] space-y-2 list-disc list-inside">
          <li>عدم بدء المستقل في تنفيذ المشروع وتجاوز المدة الزمنية المحددة للبدء دون إبداء عذر مقبول.</li>
          <li>اتفاق العميل والمستقل كتابياً داخل المنصة على إلغاء العقد قبل تسليم أي مراحل عمل جوهرية.</li>
          <li>صدور قرار إداري من لجنة التحكيم بالمنصة لصالح العميل نتيجة إخلال جسيم بشروط العقد.</li>
        </ul>
      </section>

      <section class="p-5 rounded-2xl bg-[rgb(var(--color-surface-soft))] border border-[rgb(var(--color-border))] space-y-3">
        <h2 class="font-bold text-base text-[rgb(var(--color-text-primary))] flex items-center gap-2">
          <span class="w-6 h-6 rounded-full bg-[rgb(var(--color-copper))]/10 text-[rgb(var(--color-copper))] text-xs flex items-center justify-center font-black">2</span>
          الاسترداد الجزئي وتسوية المراحل
        </h2>
        <p class="text-xs text-[rgb(var(--color-text-secondary))] leading-normal">
          في حال إنجاز جزء من المشروع بموافقة العميل ثم تعذر استكمال الباقي، تقوم المنصة بتقييم نسبة الإنجاز وصرف المستحقات العادلة للمستقل عن الجزء المكتمل، ورد المتبقي من الميزانية لحساب العميل لضمان عدم إهدار جهد أي من الطرفين.
        </p>
      </section>

      <section class="p-5 rounded-2xl bg-[rgb(var(--color-surface-soft))] border border-[rgb(var(--color-border))] space-y-3">
        <h2 class="font-bold text-base text-[rgb(var(--color-text-primary))] flex items-center gap-2">
          <span class="w-6 h-6 rounded-full bg-[rgb(var(--color-copper))]/10 text-[rgb(var(--color-copper))] text-xs flex items-center justify-center font-black">3</span>
          إجراءات تقديم النزاع والبت فيه
        </h2>
        <p class="text-xs text-[rgb(var(--color-text-secondary))] leading-normal">
          يتاح فتح تذكرة نزاع عبر صفحة تفاصيل العقد، حيث يقوم فريق الدعم الفني بمراجعة سجل المحادثات وتفاصيل التسليمات خلال 48 إلى 72 ساعة عمل وإصدار القرار النهائي الملزم لجميع الأطراف.
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
        <a href="{{ route('legal.marketplace-rules') }}" class="hover:text-[rgb(var(--color-copper))]">قواعد السوق</a>
      </div>
    </div>
  </div>
</x-guest-layout>
