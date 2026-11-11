<x-guest-layout>
  <x-slot name="title">عن منصة Tasker Enterprise (GDFH) | سوق المستقلين وإدارة المشاريع</x-slot>
  <x-slot name="metaDescription">تعرف على منصة تاسكر (Tasker Enterprise / GDFH) - المنصة المتكاملة لإدارة المشاريع والتوظيف المستقل وتتبع الوقت والذكاء الاصطناعي.</x-slot>

  <div class="space-y-6 text-center">
    <div class="space-y-2">
      <span class="gdfh-badge gdfh-badge-copper text-xs px-3 py-1 font-bold">عن المنصة — Tasker Enterprise</span>
      <h1 class="text-2xl font-black tracking-tight text-[rgb(var(--color-text-primary))] sm:text-3xl">
        المنصة الرقمية المتكاملة لإدارة العمل والتوظيف المستقل
      </h1>
      <p class="text-xs leading-relaxed text-[rgb(var(--color-text-secondary))]">
        Tasker (GDFH) تجمع بين قوة أنظمة إدارة المشاريع المؤسسية (Enterprise Project Management SaaS) ومربعات العمل الحر وسوق الخدمات المصغرة (Freelance Marketplace).
      </p>
    </div>

    <div class="grid grid-cols-1 gap-4 text-start text-xs border-t border-b border-[rgb(var(--color-border))] py-6">
      <div class="p-3 rounded-xl bg-[rgb(var(--color-surface-soft))] space-y-1">
        <div class="font-bold text-[rgb(var(--color-copper))]">🚀 إدارة المشاريع والمهام الشاملة</div>
        <p class="text-[rgb(var(--color-text-secondary))]">لوحات كانبان تفاعلية، مخططات غانت (Gantt Charts)، وتتبع الوقت المباشر مع تقارير أداء حيّة.</p>
      </div>

      <div class="p-3 rounded-xl bg-[rgb(var(--color-surface-soft))] space-y-1">
        <div class="font-bold text-[rgb(var(--color-mineral))]">💼 سوق المستقلين والعقود</div>
        <p class="text-[rgb(var(--color-text-secondary))]">توظيف أفضل الخبرات والمستقلين، إرسال العروض، اتفاقيات وعقود موثقة ومتابعة الإنجاز.</p>
      </div>

      <div class="p-3 rounded-xl bg-[rgb(var(--color-surface-soft))] space-y-1">
        <div class="font-bold text-amber-500">🤖 المساعد الذكي Tasker Copilot</div>
        <p class="text-[rgb(var(--color-text-secondary))]">تحليل مخاطر المشاريع، تفكيك المهام، واقتراح الكفاءات عبر خوارزميات الذكاء الاصطناعي.</p>
      </div>
    </div>

    <div class="flex items-center justify-center gap-3 pt-2">
      <a href="{{ route('register') }}" class="gdfh-btn gdfh-btn-brand text-xs font-bold px-6 py-2.5 shadow-md">
        انضم كعميل أو مستقل ✨
      </a>
      <a href="{{ route('public.features') }}" class="gdfh-btn gdfh-btn-secondary text-xs font-bold px-4 py-2.5">
        استكشف الميزات ←
      </a>
    </div>
  </div>
</x-guest-layout>
