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
            ['code' => 'pcs',   'name' => 'Pieces',         'description' => 'Individual pieces',                  'status' => 1],
            ['code' => 'box',   'name' => 'Box',            'description' => 'Box packaging unit',                 'status' => 1],
            ['code' => 'pack',  'name' => 'Pack',           'description' => 'Pack packaging unit',                'status' => 1],
            ['code' => 'set',   'name' => 'Set',            'description' => 'Set of items',                       'status' => 1],
            ['code' => 'kg',    'name' => 'Kilogram',       'description' => 'Weight in kilograms',                'status' => 1],
            ['code' => 'g',     'name' => 'Gram',           'description' => 'Weight in grams',                    'status' => 1],
            ['code' => 'l',     'name' => 'Litre',          'description' => 'Volume in litres',                   'status' => 1],
            ['code' => 'ml',    'name' => 'Millilitre',     'description' => 'Volume in millilitres',              'status' => 1],
            ['code' => 'm',     'name' => 'Meter',          'description' => 'Length in meters',                   'status' => 1],
            ['code' => 'cm',    'name' => 'Centimeter',     'description' => 'Length in centimeters',              'status' => 1],
            ['code' => 'roll',  'name' => 'Roll',           'description' => 'Rolled items (e.g., cables, paper)', 'status' => 1],
            ['code' => 'pair',  'name' => 'Pair',           'description' => 'Two items counted as a pair',        'status' => 1],
            ['code' => 'unit',  'name' => 'Unit',           'description' => 'Generic unit',                       'status' => 1],
        ];

        // Add timestamps
        $rows = array_map(function ($r) use ($now) {
            return array_merge($r, ['created_at' => $now, 'updated_at' => $now]);
        }, $rows);

        // Idempotent upsert by unique code; update name/description/status/updated_at when exists
        DB::table('item_units')->upsert($rows, ['code'], ['name', 'description', 'status', 'updated_at']);
    }
}
