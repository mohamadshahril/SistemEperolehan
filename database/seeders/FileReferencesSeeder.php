<?php

namespace Database\Seeders;

use App\Models\FileReference;
use Illuminate\Database\Seeder;

class FileReferencesSeeder extends Seeder
{
    public function run(): void
    {
        // Sample canonical file references; adjust as needed.
        $items = [
            ['file_code' => 'FIN', 'file_description' => 'Finance', 'parent_file_code' => 'ROOT', 'status' => 1],
            ['file_code' => 'FIN-INV', 'file_description' => 'Invoices', 'parent_file_code' => 'FIN', 'status' => 1],
            ['file_code' => 'FIN-PO', 'file_description' => 'Purchase Orders', 'parent_file_code' => 'FIN', 'status' => 1],
            ['file_code' => 'HR', 'file_description' => 'Human Resources', 'parent_file_code' => 'ROOT', 'status' => 1],
            ['file_code' => 'HR-LEAVE', 'file_description' => 'Leave Applications', 'parent_file_code' => 'HR', 'status' => 2],
        ];

        foreach ($items as $row) {
            $existing = FileReference::withTrashed()->where('file_code', $row['file_code'])->first();
            if ($existing) {
                $existing->fill([
                    'file_description' => $row['file_description'],
                    'parent_file_code' => $row['parent_file_code'],
                    'status' => $row['status'],
                ]);
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->save();
            } else {
                FileReference::create($row);
            }
        }
    }
}
