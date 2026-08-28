<section>
    <header>
        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">
            المعلومات الشخصية
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            قم بتحديث معلومات حسابك الشخصي وعنوان البريد الإلكتروني.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        {{-- Avatar Image Upload --}}
        <div>
            <x-input-label for="avatar" value="الصورة الشخصية" class="dark:text-gray-200" />
            <div class="mt-3 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                @if ($user->avatar_url)
                    <div class="relative shrink-0">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-16 w-16 rounded-full object-cover border-2 border-indigo-500 shadow-md">
                    </div>
                @else
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full text-xl font-bold bg-indigo-100 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 border-2 border-indigo-300 dark:border-indigo-700 shadow-sm overflow-hidden select-none whitespace-nowrap leading-none">
                        {{ mb_substr(trim($user->name), 0, 1) }}
                    </div>
                @endif
                <div class="flex-1 w-full min-w-0">
                    <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/jpg,image/webp" class="block w-full text-xs text-gray-500 dark:text-gray-400 file:me-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900/60 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-800/80 transition cursor-pointer">
                    <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">الصيغ المسموحة: PNG, JPG, WEBP (حجم أقصى 10MB)</p>
                        @if ($user->avatar_url)
                            <button type="button" onclick="document.getElementById('delete-avatar-form').submit();" class="text-[11px] text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-semibold underline">
                                حذف الصورة الحالية
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
        </div>

        <div>
            <x-input-label for="name" value="الاسم الكامل" class="dark:text-gray-200" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="البريد الإلكتروني" class="dark:text-gray-200" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        عنوان بريدك الإلكتروني غير مفعّل.

                        <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            انقر هنا لإعادة إرسال رابط التفعيل.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            تم إرسال رابط تفعيل جديد إلى عنوان بريدك الإلكتروني.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>حفظ التغييرات</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600 dark:text-green-400 font-semibold"
                >تم الحفظ بنجاح.</p>
            @endif
        </div>
    </form>

    <form id="delete-avatar-form" method="post" action="{{ route('profile.avatar.destroy') }}" class="hidden">
        @csrf
        @method('delete')
    </form>
</section>
