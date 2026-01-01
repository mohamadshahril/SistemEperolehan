<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;

$adminRole = Role::where('name', 'Admin')->first();
if (!$adminRole) {
    echo "Admin role not found! Please run: php artisan db:seed --class=RbacSeeder\n";
    exit(1);
}

$users = User::all();
foreach ($users as $user) {
    $user->roles()->syncWithoutDetaching([$adminRole->id]);
    echo "✓ Admin role assigned to: {$user->email}\n";
}

echo "\nSuccess! Admin role assigned to all " . $users->count() . " users.\n";
echo "Please refresh your browser to see the sidebar menu items.\n";
