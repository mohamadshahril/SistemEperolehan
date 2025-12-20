<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        // Define permissions
        $permissions = [
            ['name' => 'view users', 'description' => 'View users list and details'],
            ['name' => 'manage users', 'description' => 'Create, update, and assign roles/permissions to users'],
            ['name' => 'view roles', 'description' => 'View roles list and details'],
            ['name' => 'manage roles', 'description' => 'Create, update and delete roles'],
            ['name' => 'view permissions', 'description' => 'View permissions list and details'],
            ['name' => 'manage permissions', 'description' => 'Create, update and delete permissions'],
        ];

        $permissionIdsByName = [];
        foreach ($permissions as $perm) {
            $p = Permission::updateOrCreate(
                ['name' => $perm['name']],
                ['description' => $perm['description']]
            );
            $permissionIdsByName[$p->name] = $p->id;
        }

        // Define roles and their permissions
        $roles = [
            'Admin' => array_values($permissionIdsByName), // all permissions
            'Manager' => [
                $permissionIdsByName['view users'] ?? null,
                $permissionIdsByName['view roles'] ?? null,
                $permissionIdsByName['view permissions'] ?? null,
            ],
            'Staff' => [
                // Keep minimal/no elevated permissions by default
            ],
        ];

        foreach ($roles as $roleName => $permIds) {
            $role = Role::updateOrCreate(
                ['name' => $roleName],
                ['description' => $roleName . ' role']
            );

            // Filter out nulls and sync
            $permIds = array_values(array_filter($permIds ?? [], fn($v) => !is_null($v)));
            if ($roleName === 'Admin' && empty($permIds)) {
                // In case permissions array was empty (shouldn't happen), sync all
                $permIds = Permission::pluck('id')->all();
            }
            $role->permissions()->sync($permIds);
        }

        // Attach Admin role to default admin user if exists
        $admin = User::where('email', 'admin@example.com')->first();
        $adminRole = Role::where('name', 'Admin')->first();
        if ($admin && $adminRole) {
            $admin->roles()->syncWithoutDetaching([$adminRole->id]);
        }
    }
}
