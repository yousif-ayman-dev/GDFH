<section class="space-y-6">
    <header>
        <h2 class="text-lg font-bold text-red-600 dark:text-red-400">
            حذف الحساب
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            بمجرد حذف حسابك، سيتم حذف جميع الموارد والبيانات المرتبطة به بشكل نهائي. يُرجى تنزيل أي معلومات أو بيانات يرغب في الاحتفاظ بها قبل حذف الحساب.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >حذف الحساب نهائياً</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 dark:bg-gray-900">
            @csrf
            @method('delete')

            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                هل أنت تأكد من أنك تريد حذف حسابك؟
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                سيؤدي حذف الحساب إلى مسح كافة مشاريعك وخدماتك وبياناتك نهائياً. يُرجى إدخال كلمة المرور لتأكيد رغبتك في حذف الحساب.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="كلمة المرور" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700"
                    placeholder="أدخل كلمة المرور للتأكيد"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    إلغاء
                </x-secondary-button>

                <x-danger-button>
                    تأكيد حذف الحساب
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
