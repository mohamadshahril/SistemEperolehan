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
        Schema::create('file_references', function (Blueprint $table) {
            $table->id();
            $table->string('file_code')->unique();
            $table->string('file_description',50);
            $table->string('parent_file_code')->index();
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
        Schema::dropIfExists('file_references');
    }
};
