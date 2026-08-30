<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== SETTING UP CLEAN DEMO ACCOUNTS ===" . PHP_EOL;

// 1. Admin Account
$admin = User::firstOrCreate(
    ['email' => 'admin@tasker.com'],
    [
        'name' => 'مدير النظام (Admin)',
        'account_type' => 'client',
        'is_admin' => true,
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
    ]
);

$admin->is_admin = true;
$admin->password = Hash::make('password123');
$admin->save();

// 2. Client Account
$client = User::firstOrCreate(
    ['email' => 'client@tasker.com'],
    [
        'name' => 'أحمد علي (صاحب عمل)',
        'account_type' => 'client',
        'is_admin' => false,
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
    ]
);
$client->password = Hash::make('password123');
$client->save();

// 3. Freelancer Account
$freelancer = User::firstOrCreate(
    ['email' => 'freelancer@tasker.com'],
    [
        'name' => 'محمود حسن (مستقل محترف)',
        'account_type' => 'freelancer',
        'is_admin' => false,
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
    ]
);
$freelancer->password = Hash::make('password123');
$freelancer->save();

echo "SUCCESS! Clean demo accounts ready:" . PHP_EOL;
echo "Admin: admin@tasker.com | password123" . PHP_EOL;
echo "Client: client@tasker.com | password123" . PHP_EOL;
echo "Freelancer: freelancer@tasker.com | password123" . PHP_EOL;
