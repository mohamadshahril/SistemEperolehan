<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();   // e.g., Approved, Pending, Rejected
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insert default statuses
        DB::table('statuses')->insert([
            ['name' => 'Pending', 'description' => 'Waiting for approval'],
            ['name' => 'Approved', 'description' => 'Request has been approved'],
            ['name' => 'Rejected', 'description' => 'Request has been rejected'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
