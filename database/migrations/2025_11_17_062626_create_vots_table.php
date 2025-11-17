<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vots', function (Blueprint $table) {
            $table->id();
            $table->integer('vot_code')->unique();
            $table->string('vot_name');
            $table->tinyInteger('status')->default(1)->comment('1 = active, 2 = inactive');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vots');
    }
};
