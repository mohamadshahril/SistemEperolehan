<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorsSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            [
                'name' => 'Alpha Supplies Sdn Bhd',
                'email' => 'sales@alpha-supplies.my',
                'phone' => '+60 3-1234 5678',
                'address' => 'Lot 12, Jalan Industri 3, 47000 Sungai Buloh, Selangor',
            ],
            [
                'name' => 'Borneo Tech Enterprise',
                'email' => 'hello@borneotech.my',
                'phone' => '+60 88-765 432',
                'address' => 'Mile 3, Jalan Penampang, 88200 Kota Kinabalu, Sabah',
            ],
            [
                'name' => 'Cendana Office Mart',
                'email' => 'support@cendanaoffice.my',
                'phone' => '+60 7-555 9012',
                'address' => 'No. 8, Taman Perindustrian Tebrau, 81100 Johor Bahru, Johor',
            ],
            [
                'name' => 'Daya Engineering Works',
                'email' => 'contact@dayaeng.my',
                'phone' => '+60 3-7890 1234',
                'address' => '27, Jalan Teknologi, Kota Damansara, 47810 Petaling Jaya, Selangor',
            ],
        ];

        foreach ($vendors as $data) {
            Vendor::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
