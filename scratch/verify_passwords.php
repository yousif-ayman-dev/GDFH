<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== TESTING ALL PASSWORDS ===" . PHP_EOL;

$passwords = ['password', 'password123', '12345678', 'Admin123!'];

foreach (User::all() as $user) {
    echo "User: " . $user->email . PHP_EOL;
    foreach ($passwords as $p) {
        if (Hash::check($p, $user->password)) {
            echo "  --> MATCH: " . $p . PHP_EOL;
        }
    }
}
