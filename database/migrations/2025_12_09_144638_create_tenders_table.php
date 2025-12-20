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
        Schema::create('tenders', function (Blueprint $table) {
            $table->id();
            $table->string('tender_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->decimal('estimated_budget', 15, 2)->nullable();
            $table->date('opening_date');
            $table->date('closing_date');
            $table->enum('status', ['Draft', 'Published', 'Closed', 'Awarded', 'Cancelled'])->default('Draft');
            $table->text('requirements')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('awarded_bid_id')->nullable();
            $table->timestamp('awarded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenders');
    }
};
