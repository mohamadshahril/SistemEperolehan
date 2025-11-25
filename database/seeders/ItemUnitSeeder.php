<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemUnitSeeder extends Seeder
{
    public function run(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('item_units')) {
            return; // table not migrated yet
        }

        $now = now();

        // Commonly used item units
        $rows = [
            ['unit_code' => 'pcs',   'name' => 'Pieces',         'description' => 'Individual pieces',                  'status' => 1],
            ['unit_code' => 'box',   'name' => 'Box',            'description' => 'Box packaging unit',                 'status' => 1],
            ['unit_code' => 'pack',  'name' => 'Pack',           'description' => 'Pack packaging unit',                'status' => 1],
            ['unit_code' => 'set',   'name' => 'Set',            'description' => 'Set of items',                       'status' => 1],
            ['unit_code' => 'kg',    'name' => 'Kilogram',       'description' => 'Weight in kilograms',                'status' => 1],
            ['unit_code' => 'g',     'name' => 'Gram',           'description' => 'Weight in grams',                    'status' => 1],
            ['unit_code' => 'l',     'name' => 'Litre',          'description' => 'Volume in litres',                   'status' => 1],
            ['unit_code' => 'ml',    'name' => 'Millilitre',     'description' => 'Volume in millilitres',              'status' => 1],
            ['unit_code' => 'm',     'name' => 'Meter',          'description' => 'Length in meters',                   'status' => 1],
            ['unit_code' => 'cm',    'name' => 'Centimeter',     'description' => 'Length in centimeters',              'status' => 1],
            ['unit_code' => 'roll',  'name' => 'Roll',           'description' => 'Rolled items (e.g., cables, paper)', 'status' => 1],
            ['unit_code' => 'pair',  'name' => 'Pair',           'description' => 'Two items counted as a pair',        'status' => 1],
            ['unit_code' => 'unit',  'name' => 'Unit',           'description' => 'Generic unit',                       'status' => 1],
        ];

        // Add timestamps
        $rows = array_map(function ($r) use ($now) {
            return array_merge($r, ['created_at' => $now, 'updated_at' => $now]);
        }, $rows);

        // Idempotent upsert by unique unit_code; update name/description/status/updated_at when exists
        DB::table('item_units')->upsert($rows, ['unit_code'], ['name', 'description', 'status', 'updated_at']);
    }
}
