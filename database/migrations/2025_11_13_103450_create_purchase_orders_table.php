<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->unsignedBigInteger('approved_request_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->decimal('total_price', 12, 2)->default(0);
            $table->string('status')->default('Pending');
            $table->timestamps();
            $table->softDeletes();

            $table->index('po_number');
        });
    }

    public function down(): void {
        Schema::dropIfExists('purchase_orders');
    }
};
