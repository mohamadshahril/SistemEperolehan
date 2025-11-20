<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\PurchaseRequest;
use App\Models\Vendor;
use App\Models\PurchaseOrder;
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

        // Create or update a default admin/test user
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => 'password', // will be hashed by model cast
                'ic_no' => '800101016666',
                'staff_id' => 'A0001',
                'location_iso_code' => 'MY-01',
                'email_verified_at' => now(),
            ]
        );

        // Additional sample users
        User::factory(5)->create();

        // Seed purchase requests via dedicated seeder (kept small and safe to re-run)
        $this->call([
            PurchaseRequestSeeder::class,
            PurchaseItemSeeder::class,
        ]);

        // Seed vendors
        try {
            if (DB::getSchemaBuilder()->hasTable('vendors')) {
                $vendorsData = [
                    [
                        'name' => 'Acme Supplies Sdn Bhd',
                        'email' => 'sales@acme.example',
                        'phone' => '+60-3-1111-2222',
                        'address' => 'Lot 1, Jalan Industri 1, 43000 Kajang, Selangor',
                    ],
                    [
                        'name' => 'Borneo Tech Enterprise',
                        'email' => 'hello@borneo-tech.example',
                        'phone' => '+60-82-123456',
                        'address' => 'No. 12, Jalan Tun Jugah, 93350 Kuching, Sarawak',
                    ],
                    [
                        'name' => 'Metro Office Solutions',
                        'email' => 'contact@metro-office.example',
                        'phone' => '+60-3-2222-3333',
                        'address' => '23-1, Jalan Ampang, 50450 Kuala Lumpur',
                    ],
                ];

                $vendors = [];
                foreach ($vendorsData as $data) {
                    $vendors[] = Vendor::updateOrCreate(
                        ['name' => $data['name']],
                        $data
                    );
                }

                // Seed purchase orders linked to the above vendors
                if (DB::getSchemaBuilder()->hasTable('purchase_orders')) {
                    // Ensure we have at least one vendor to attach
                    if (!empty($vendors)) {
                        $pos = [
                            [
                                'order_number' => 'PO-2025-0001',
                                'vendor' => $vendors[0],
                                'details' => 'Office stationery purchase',
                                'status' => 'Pending',
                            ],
                            [
                                'order_number' => 'PO-2025-0002',
                                'vendor' => $vendors[1] ?? $vendors[0],
                                'details' => 'IT peripherals (mice, keyboards)',
                                'status' => 'Approved',
                            ],
                            [
                                'order_number' => 'PO-2025-0003',
                                'vendor' => $vendors[2] ?? $vendors[0],
                                'details' => 'Printer toner cartridges',
                                'status' => 'Completed',
                            ],
                        ];

                        foreach ($pos as $po) {
                            PurchaseOrder::updateOrCreate(
                                ['order_number' => $po['order_number']],
                                [
                                    'vendor_id' => $po['vendor']->id,
                                    'details' => $po['details'],
                                    'status' => $po['status'],
                                ]
                            );
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore if tables not migrated yet
        }

    }
}
