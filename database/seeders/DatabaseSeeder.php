<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\PurchaseRequest;
use App\Models\Vendor;
use App\Models\PurchaseOrder;
use App\Models\DeliveryOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed lookup/reference tables first
        $this->call([
            StatusSeeder::class,
            ReferenceSeeder::class,
            ItemUnitSeeder::class,
        ]);

        // Users, Roles & Permissions
        $this->call([
            UserSeeder::class,
            RbacSeeder::class,
        ]);

        // Seed purchase requests via dedicated seeder (kept small and safe to re-run)
        $this->call([
            PurchaseRequestSeeder::class,
            PurchaseItemSeeder::class,
        ]);

        // Seed vendors
        $this->call([
            PurchaseOrderSeeder::class,
        ]);

        // Seed delivery orders
        $this->call([
            DeliveryOrderSeeder::class,
        ]);

    }
}
