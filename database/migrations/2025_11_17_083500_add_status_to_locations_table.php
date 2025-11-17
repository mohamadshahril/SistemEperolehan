<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            // Add status with default 1 (active). 2 = inactive
            $table->tinyInteger('status')
                ->default(1)
                ->comment('1 = active, 2 = inactive')
                ->after('parent_iso_code');
        });

        // Ensure existing rows have status = 1 (in case some DBs ignore default for existing data)
        if (Schema::hasColumn('locations', 'status')) {
            \DB::table('locations')->whereNull('status')->update(['status' => 1]);
        }
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
