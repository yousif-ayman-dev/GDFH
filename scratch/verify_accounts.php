<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== TASKER USERS VERIFICATION ===" . PHP_EOL;

$users = User::all();
foreach ($users as $user) {
    $passwordStatus = Hash::check('password123', $user->password) ? 'password123 WORKS' : 'OTHER PASSWORD';
    echo sprintf(
        "ID: %d | Email: %-30s | Name: %-20s | Type: %-10s | Admin: %-3s | Password: %s\n",
        $user->id,
        $user->email,
        $user->name,
        $user->account_type ?? 'none',
        $user->is_admin ? 'YES' : 'NO',
        $passwordStatus
    );
}
