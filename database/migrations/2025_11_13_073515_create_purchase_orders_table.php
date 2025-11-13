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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->text('details')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Completed'])->default('Pending');
            $table->timestamps();
            $table->index(['status', 'order_number']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::dropIfExists('purchase_orders');
    }
};
