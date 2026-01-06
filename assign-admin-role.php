<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;

// Get all users
$users = User::all();

echo "Available users:\n";
foreach ($users as $user) {
    echo "ID: {$user->id} - Email: {$user->email} - Name: {$user->name}\n";
}

echo "\nEnter the user ID to assign Admin role: ";
$userId = trim(fgets(STDIN));

$user = User::find($userId);
if (!$user) {
    echo "User not found!\n";
    exit(1);
}

$adminRole = Role::where('name', 'Admin')->first();
if (!$adminRole) {
    echo "Admin role not found! Please run: php artisan db:seed --class=RbacSeeder\n";
    exit(1);
}

// Assign Admin role
$user->roles()->syncWithoutDetaching([$adminRole->id]);

echo "\nSuccess! Admin role assigned to {$user->email}\n";
echo "Please refresh your browser to see the sidebar menu items.\n";
