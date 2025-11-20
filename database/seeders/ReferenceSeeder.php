<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Seed minimal reference data required by the app
        // Ensure there is an ID=1 for each table since factories default to 1

        // locations
        if (DB::getSchemaBuilder()->hasTable('locations')) {
            DB::table('locations')->upsert([
                [
                    'id' => 1,
                    'location_iso_code' => 'MY',
                    'location_name' => 'MALAYSIA',
                    // Top-level node: set parent to self to satisfy NOT NULL constraint
                    'parent_iso_code' => 'MY',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 2,
                    'location_iso_code' => 'MY-01',
                    'location_name' => 'JOHOR',
                    'parent_iso_code' => 'MY',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 3,
                    'location_iso_code' => 'MY-02',
                    'location_name' => 'KEDAH',
                    'parent_iso_code' => 'MY',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ], ['id'], ['location_iso_code', 'location_name', 'parent_iso_code', 'updated_at']);
        }

        // type_procurements
        if (DB::getSchemaBuilder()->hasTable('type_procurements')) {
            DB::table('type_procurements')->upsert([
                [
                    'id' => 1,
                    'procurement_code' => 'TP01',
                    'procurement_description' => 'Standard Procurement',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 2,
                    'procurement_code' => 'TP02',
                    'procurement_description' => 'Emergency Procurement',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 3,
                    'procurement_code' => 'TP03',
                    'procurement_description' => 'Direct Purchase',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 4,
                    'procurement_code' => 'TP04',
                    'procurement_description' => 'Quotation',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 5,
                    'procurement_code' => 'TP05',
                    'procurement_description' => 'Tender',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ], ['id'], ['procurement_code', 'procurement_description', 'updated_at']);
        }

        // file_references
        if (DB::getSchemaBuilder()->hasTable('file_references')) {
            DB::table('file_references')->upsert([
                [
                    'id' => 1,
                    'file_code' => 'AIM',
                    'file_description' => 'AIM General Reference',
                    // Top-level node: set parent to self to satisfy NOT NULL constraint
                    'parent_file_code' => 'AIM',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 2,
                    'file_code' => 'FIN',
                    'file_description' => 'Finance',
                    'parent_file_code' => 'AIM',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 3,
                    'file_code' => 'HR',
                    'file_description' => 'Human Resources',
                    'parent_file_code' => 'AIM',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ], ['id'], ['file_code', 'file_description', 'parent_file_code', 'updated_at']);
        }

        // vots
        if (DB::getSchemaBuilder()->hasTable('vots')) {
            DB::table('vots')->upsert([
                [
                    'id' => 1,
                    'vot_code' => 'V01',
                    'vot_description' => 'Operational Expenditure',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 2,
                    'vot_code' => 'V02',
                    'vot_description' => 'Development Expenditure',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 3,
                    'vot_code' => 'V03',
                    'vot_description' => 'Assets & Equipment',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 4,
                    'vot_code' => 'V04',
                    'vot_description' => 'Maintenance',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ], ['id'], ['vot_code', 'vot_description', 'updated_at']);
        }
    }
}
