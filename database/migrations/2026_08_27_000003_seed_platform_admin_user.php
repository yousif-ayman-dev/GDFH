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
        $admin = User::firstOrCreate(
            ['email' => 'yousifaymand@gmail.com'],
            [
                'name' => 'يوسف دحلان (مدير المنصة)',
                'username' => 'yousif_admin',
                'password' => Hash::make('password'),
                'account_type' => 'client',
                'bio' => 'مدير منصة Tasker',
                'email_verified_at' => now(),
                'onboarded_at' => now(),
                'is_admin' => true,
                'is_verified' => true,
            ]
        );

        $admin->update([
            'name' => 'يوسف دحلان (مدير المنصة)',
            'bio' => 'مدير منصة Tasker',
            'is_admin' => true,
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
