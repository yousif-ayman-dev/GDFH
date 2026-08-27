<x-guest-layout>
  <div x-data="{
        accountType: '{{ old('account_type', 'freelancer') }}',
        username: '{{ old('username', '') }}'
    }" class="space-y-6">
    <div class="text-center">
      <h1 class="text-xl font-bold tracking-tight" style="color: rgb(var(--color-text-primary));">
        مرحباً بك في Tasker!
      </h1>
      <p class="mt-2 text-sm" style="color: rgb(var(--color-text-secondary));">
        اختر نوع حسابك واسم المستخدم الفريد الخاص بك لبدء تجربة العمل.
      </p>
    </div>

    @if ($errors->any())
    <div class="rounded-xl p-4 text-sm" style="background-color: rgb(var(--color-error) / 0.1); color: rgb(var(--color-error)); border: 1px solid rgb(var(--color-error) / 0.2);">
      <div class="font-semibold">يرجى تصحيح الأخطاء التالية:</div>
      <ul class="mt-2 list-inside list-disc space-y-1">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('onboarding.store') }}" class="space-y-6">
      @csrf

      {{-- Account Type Selection --}}
      <div>
        <label class="block text-sm font-semibold mb-3" style="color: rgb(var(--color-text-primary));">
          كيف تخطط لاستخدام Tasker؟ <span class="text-red-500">*</span>
        </label>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          {{-- Freelancer Option --}}
          <label @click="accountType = 'freelancer'"
            :class="accountType === 'freelancer' ? 'border-amber-500 bg-amber-500/5 dark:bg-amber-500/10' : ''"
            class="relative flex cursor-pointer flex-col rounded-2xl p-4 transition"
            style="border: 2px solid rgb(var(--color-border));">
            <input type="radio" name="account_type" value="freelancer" x-model="accountType" class="sr-only">
            
            <div class="flex items-center justify-between">
              <div class="flex h-10 w-10 items-center justify-center rounded-xl"
                style="background-color: rgb(var(--color-copper-soft)); color: rgb(var(--color-copper));">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
              </div>

              <div x-show="accountType === 'freelancer'" class="flex h-6 w-6 items-center justify-center rounded-full"
                style="background-color: rgb(var(--color-copper)); color: #ffffff;">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
              </div>
            </div>

            <div class="mt-4">
              <div class="font-bold text-sm" style="color: rgb(var(--color-text-primary));">
                مستقل (Freelancer)
              </div>
              <p class="mt-1 text-xs" style="color: rgb(var(--color-text-secondary));">
                تقديم الخدمات المصغرة، بناء معرض الأعمال (Portfolio)، تأسيس الفرق، والعمل على المشاريع.
              </p>
            </div>
          </label>

          {{-- Client Option --}}
          <label @click="accountType = 'client'"
            :class="accountType === 'client' ? 'border-amber-500 bg-amber-500/5 dark:bg-amber-500/10' : ''"
            class="relative flex cursor-pointer flex-col rounded-2xl p-4 transition"
            style="border: 2px solid rgb(var(--color-border));">
            <input type="radio" name="account_type" value="client" x-model="accountType" class="sr-only">

            <div class="flex items-center justify-between">
              <div class="flex h-10 w-10 items-center justify-center rounded-xl"
                style="background-color: rgb(var(--color-mineral-soft)); color: rgb(var(--color-mineral));">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
              </div>

              <div x-show="accountType === 'client'" class="flex h-6 w-6 items-center justify-center rounded-full"
                style="background-color: rgb(var(--color-copper)); color: #ffffff;">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
              </div>
            </div>

            <div class="mt-4">
              <div class="font-bold text-sm" style="color: rgb(var(--color-text-primary));">
                عميل (Client)
              </div>
              <p class="mt-1 text-xs" style="color: rgb(var(--color-text-secondary));">
                طرح المشاريع، تصفح المستقلين ومعارض أعمالهم (Portfolio)، وتوظيف المبدعين مباشرة.
              </p>
            </div>
          </label>
        </div>
      </div>

      {{-- Unique Username Input --}}
      <div>
        <label for="username" class="block text-sm font-semibold mb-2" style="color: rgb(var(--color-text-primary));">
          معرّف الحساب الفريد (اسم المستخدم) <span class="text-red-500">*</span>
        </label>
        <p class="text-xs mb-3" style="color: rgb(var(--color-text-secondary));">
          سيستخدمه الأعضاء والعملاء للبحث عنك وإضافتك إلى الفرق والمشاريع.
        </p>

        <div class="relative flex items-center dir-ltr">
          <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-sm font-bold"
            style="color: rgb(var(--color-copper));">
            @
          </div>

          <input id="username" type="text" name="username" x-model="username" required
            placeholder="john_doe"
            class="gdfh-input ps-8 font-mono text-sm tracking-wide"
            dir="ltr"
            autocomplete="username">
        </div>

        <div class="mt-2 flex items-center justify-between text-xs" style="color: rgb(var(--color-text-secondary));">
          <span>أحرف إنجليزية، أرقام، وشرطة سفلية فقط</span>
          <span x-show="username.length > 0" class="font-mono dir-ltr text-amber-600 dark:text-amber-400 font-semibold" x-text="'@' + username.toLowerCase()"></span>
        </div>
      </div>

      {{-- Submit Button --}}
      <button type="submit" class="gdfh-btn gdfh-btn-brand w-full font-bold">
        متابعة إلى مساحة العمل
        <svg class="h-4 w-4 rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
        </svg>
      </button>
    </form>

    <div class="pt-4 border-t text-center" style="border-color: rgb(var(--color-border));">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="text-xs text-red-600 hover:text-red-700 underline font-semibold">
          تسجيل الخروج
        </button>
      </form>
    </div>
  </div>
</x-guest-layout>
