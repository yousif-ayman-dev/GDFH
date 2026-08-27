<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Admin Account (مدير المنصة)
        User::updateOrCreate(
            ['email' => 'yousifaymand@gmail.com'],
            [
                'name' => 'يوسف دحلان (مدير المنصة)',
                'username' => 'yousif_admin',
                'password' => Hash::make('password'),
                'account_type' => 'client',
                'bio' => 'مدير وصاحب منصة Tasker Enterprise',
                'email_verified_at' => now(),
                'onboarded_at' => now(),
                'is_admin' => true,
                'is_verified' => true,
            ]
        );

        // 2. Client Account (حساب صاحب عمل للتست)
        User::updateOrCreate(
            ['email' => 'client@tasker.com'],
            [
                'name' => 'أحمد علي (صاحب عمل)',
                'username' => 'client_test',
                'password' => Hash::make('password'),
                'account_type' => 'client',
                'bio' => 'صاحب عمل يطرح مشاريع وتبسيط المهام على منصة Tasker',
                'email_verified_at' => now(),
                'onboarded_at' => now(),
                'is_admin' => false,
                'is_verified' => true,
            ]
        );

        // 3. Freelancer Account (حساب مستقل للتست)
        User::updateOrCreate(
            ['email' => 'freelancer@tasker.com'],
            [
                'name' => 'محمود حسن (مستقل محترف)',
                'username' => 'freelancer_test',
                'password' => Hash::make('password'),
                'account_type' => 'freelancer',
                'bio' => 'مستقل محترف وخبير في تقديم الخدمات البرمجية والتصميم',
                'email_verified_at' => now(),
                'onboarded_at' => now(),
                'is_admin' => false,
                'is_verified' => true,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
