<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('location_iso_code')->unique();
            $table->string('location_name', 100)->unique();;
            $table->string('parent_iso_code');
            $table->timestamps();
            $table->softDeletes();
            $table->index('parent_iso_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
