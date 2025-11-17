<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('item_name');
            $table->unsignedInteger('quantity');
            $table->decimal('price', 12, 2);
            $table->text('purpose')->nullable();
            $table->string('status')->default('Pending'); // Pending, Approved, Rejected
            $table->datetime('submitted_at')->nullable();
            $table->string('attachment_path')->nullable();
            // Approval fields (merged from 2025_11_14_121500_add_approval_fields_to_purchase_requests_table)
            $table->text('approval_comment')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'submitted_at']);
            $table->index(['approved_by', 'approved_at']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
