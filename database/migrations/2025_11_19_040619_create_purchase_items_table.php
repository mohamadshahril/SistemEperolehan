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
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            // Link to purchase request
            $table->foreignId('purchase_request_id')->constrained()->cascadeOnDelete();
            // Store purchase reference number alongside the item for easy referencing/reporting
            $table->string('purchase_ref_no')->nullable()->index();
            // Item details
            $table->string('item_name');
            $table->string('item_code')->nullable();
            $table->text('purpose')->nullable();
            $table->string('unit')->nullable(); // e.g., pcs, box, kg
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('total_price', 14, 2)->nullable(); // optional if calculated
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
