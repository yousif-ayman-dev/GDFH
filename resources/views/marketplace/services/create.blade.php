<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-[rgb(var(--color-text-primary))]">
          إضافة خدمة جديدة إلى السوق (Add New Service)
        </h2>
        <p class="mt-1 text-xs text-[rgb(var(--color-text-secondary))]">
          اعرض خدماتك وحلولك البرمجية للعملاء والشركات في المنصة.
        </p>
      </div>

      <a href="{{ route('marketplace.index') }}" class="gdfh-btn gdfh-btn-secondary text-xs py-1.5 px-3">
        ← إلغاء والعودة للسوق
      </a>
    </div>
  </x-slot>

  <div class="px-4 py-8 sm:px-6 lg:px-8 lg:py-10 space-y-6">
    <div class="mx-auto max-w-3xl space-y-6">

      <div class="gdfh-card p-6 sm:p-8 space-y-6">
        <form method="POST" action="{{ route('marketplace.services.store') }}" enctype="multipart/form-data" class="space-y-5 text-xs">
          @csrf

          <div>
            <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">عنوان الخدمة *</label>
            <input type="text" name="title" value="{{ old('title') }}" required placeholder="مثال: تطوير واجهات مستخدم احترافية باستخدام Vue.js و Tailwind..." class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:border-[rgb(var(--color-copper))] focus:outline-none">
            <x-input-error class="mt-1" :messages="$errors->get('title')" />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">السعر (USD $) *</label>
              <input type="number" step="0.01" min="5" name="price" value="{{ old('price', 50) }}" required class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:border-[rgb(var(--color-copper))] focus:outline-none">
              <x-input-error class="mt-1" :messages="$errors->get('price')" />
            </div>

            <div>
              <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">مدة التسليم (أيام) *</label>
              <input type="number" min="1" max="365" name="delivery_days" value="{{ old('delivery_days', 3) }}" required class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:border-[rgb(var(--color-copper))] focus:outline-none">
              <x-input-error class="mt-1" :messages="$errors->get('delivery_days')" />
            </div>

            <div>
              <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">التصنيف *</label>
              <select name="category" required class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:border-[rgb(var(--color-copper))] focus:outline-none">
                <option value="تطوير البرمجيات" {{ old('category') === 'تطوير البرمجيات' ? 'selected' : '' }}>تطوير البرمجيات</option>
                <option value="تصميم واجهات UI/UX" {{ old('category') === 'تصميم واجهات UI/UX' ? 'selected' : '' }}>تصميم واجهات UI/UX</option>
                <option value="تطوير تطبيقات الجوال" {{ old('category') === 'تطوير تطبيقات الجوال' ? 'selected' : '' }}>تطوير تطبيقات الجوال</option>
                <option value="إدارة وتأمين قواعد البيانات" {{ old('category') === 'إدارة وتأمين قواعد البيانات' ? 'selected' : '' }}>إدارة وتأمين قواعد البيانات</option>
                <option value="استشارات تقنية وهندسية" {{ old('category') === 'استشارات تقنية وهندسية' ? 'selected' : '' }}>استشارات تقنية وهندسية</option>
              </select>
              <x-input-error class="mt-1" :messages="$errors->get('category')" />
            </div>
          </div>

          <div>
            <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">وصف تفصيلي للخدمة *</label>
            <textarea name="description" rows="5" required placeholder="اشرح للعملاء ما تتضمنه الخدمة، المخرجات، والمتطلبات البدائية..." class="w-full rounded-xl border border-[rgb(var(--color-border))] bg-[rgb(var(--color-surface))] p-2.5 text-xs text-[rgb(var(--color-text-primary))] focus:border-[rgb(var(--color-copper))] focus:outline-none">{{ old('description') }}</textarea>
            <x-input-error class="mt-1" :messages="$errors->get('description')" />
          </div>

          <div>
            <label class="block font-bold text-[rgb(var(--color-text-primary))] mb-1">صورة الغلاف (اختياري)</label>
            <input type="file" name="cover_image" accept="image/jpeg,image/png,image/jpg,image/webp" class="w-full text-xs text-[rgb(var(--color-text-secondary))] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[rgb(var(--color-copper-soft))] file:text-[rgb(var(--color-copper))] hover:file:opacity-80">
            <x-input-error class="mt-1" :messages="$errors->get('cover_image')" />
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-[rgb(var(--color-border))]">
            <a href="{{ route('marketplace.index') }}" class="gdfh-btn gdfh-btn-secondary py-2 px-5 font-bold">إلغاء</a>
            <button type="submit" class="gdfh-btn gdfh-btn-brand py-2 px-6 font-bold shadow-sm">
              نشر الخدمة في السوق
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</x-app-layout>
