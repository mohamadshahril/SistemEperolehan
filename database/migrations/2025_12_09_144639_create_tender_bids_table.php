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
        Schema::create('tender_bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_id')->constrained('tenders')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->decimal('bid_amount', 15, 2);
            $table->text('proposal')->nullable();
            $table->text('technical_specifications')->nullable();
            $table->integer('delivery_timeline_days')->nullable();
            $table->enum('status', ['Submitted', 'Under Review', 'Accepted', 'Rejected', 'Withdrawn'])->default('Submitted');
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Ensure one bid per vendor per tender
            $table->unique(['tender_id', 'vendor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tender_bids');
    }
};
