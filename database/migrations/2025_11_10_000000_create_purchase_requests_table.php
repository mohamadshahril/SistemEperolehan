<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            // Request owner
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Header fields
            $table->string('title');
            // Using plain foreign keys with index to avoid migration order constraints
            $table->foreignId('type_procurement_id')->index();
            $table->foreignId('file_reference_id')->index();
            $table->foreignId('vot_id')->index();
            // Store user location at the time of request for code generation/reporting
            $table->string('location_iso_code')->index();
            // Budget for the request
            $table->decimal('budget', 12, 2);
            // Optional high-level purpose/remarks
            $table->text('purpose')->nullable();

            // Multiple items payload (array of item_no, details, purpose, quantity, price)
            $table->json('items');

            $table->string('status')->default('Pending'); // Pending, Approved, Rejected
            $table->dateTime('submitted_at')->nullable();
            $table->string('attachment_path')->nullable();

            // Generated purchase code: AIM/{location_iso_code}/{file_code}/{vot_code}/{running}
            $table->string('purchase_code')->nullable()->unique();
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
