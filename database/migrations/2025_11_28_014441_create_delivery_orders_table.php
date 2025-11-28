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
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            // Link to the Purchase Order
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->string('do_number')->unique(); // Delivery Order Number
            $table->date('delivery_date');
            $table->string('file_path')->nullable(); // Path to the uploaded DO document
            $table->boolean('is_received')->default(false); // For "Confirm Delivery"
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('delivery_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};