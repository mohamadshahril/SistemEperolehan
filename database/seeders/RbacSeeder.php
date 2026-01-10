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
        // Load the static catalog from config and ensure every permission exists
        $catalog = (array) config('permissions.catalog', []);
        $flat = [];
        foreach ($catalog as $group => $items) {
            foreach ($items as $perm) {
                $flat[] = $perm;
            }
        }

        $permissionIdsByName = [];
        foreach ($flat as $perm) {
            $p = Permission::updateOrCreate(
                ['name' => $perm['name']],
                ['description' => $perm['description'] ?? null]
            );
            $permissionIdsByName[$p->name] = $p->id;
        }

        // Define roles and their permissions
        $roles = [
            'Admin' => array_values($permissionIdsByName), // all permissions
            'Manager' => [
                // Approvals
                $permissionIdsByName['view approvals'] ?? null,
                $permissionIdsByName['approve purchase requests'] ?? null,
                $permissionIdsByName['reject purchase requests'] ?? null,
                // Read access to modules
                $permissionIdsByName['view users'] ?? null,
                $permissionIdsByName['view roles'] ?? null,
                $permissionIdsByName['view permissions'] ?? null,
                $permissionIdsByName['view vendors'] ?? null,
                $permissionIdsByName['view purchase orders'] ?? null,
                $permissionIdsByName['view delivery orders'] ?? null,
                $permissionIdsByName['view delivery reports'] ?? null,
                $permissionIdsByName['view locations'] ?? null,
                $permissionIdsByName['view vots'] ?? null,
                $permissionIdsByName['view type procurements'] ?? null,
                $permissionIdsByName['view item units'] ?? null,
                $permissionIdsByName['view tenders'] ?? null,
                $permissionIdsByName['view tender bids'] ?? null,
                // Special non-manage
                $permissionIdsByName['export delivery reports'] ?? null,
                $permissionIdsByName['print delivery orders'] ?? null,
            ],
            'Staff' => [
                // Staff can create purchase requests by default
                $permissionIdsByName['create purchase requests'] ?? null,
            ],
            'Procurement Officer' => [
                // Procurement related
                $permissionIdsByName['view vendors'] ?? null,
                $permissionIdsByName['manage vendors'] ?? null,
                $permissionIdsByName['view purchase orders'] ?? null,
                $permissionIdsByName['manage purchase orders'] ?? null,
                $permissionIdsByName['print purchase orders'] ?? null,
                $permissionIdsByName['view delivery orders'] ?? null,
                $permissionIdsByName['manage delivery orders'] ?? null,
                $permissionIdsByName['confirm delivery orders'] ?? null,
                $permissionIdsByName['print delivery orders'] ?? null,
                $permissionIdsByName['view tenders'] ?? null,
                $permissionIdsByName['manage tenders'] ?? null,
                $permissionIdsByName['award tenders'] ?? null,
                $permissionIdsByName['view tender bids'] ?? null,
                $permissionIdsByName['manage tender bids'] ?? null,
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

        // Attach Roles to specific users
        $userRoleMappings = [
            'admin@example.com' => 'Admin',
            'manager@example.com' => 'Manager',
            'staff@example.com' => 'Staff',
            'procurement@example.com' => 'Procurement Officer',
        ];

        foreach ($userRoleMappings as $email => $roleName) {
            $user = User::where('email', $email)->first();
            $role = Role::where('name', $roleName)->first();

            if ($user && $role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }
        }
    }
}
