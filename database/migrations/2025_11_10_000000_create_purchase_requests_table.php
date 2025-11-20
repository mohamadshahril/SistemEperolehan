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
            // Applicant (the user who submits the request)
            $table->foreignId('applicant_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->foreignId('type_procurement_id')->index();
            $table->foreignId('file_reference_id')->index();
            $table->foreignId('vot_id')->index();
            $table->string('location_iso_code')->index();
            $table->decimal('budget', 12, 2);
            $table->text('note')->nullable();
            // Items are stored in purchase_items table; keep nullable reference for potential linking
            $table->foreignId('item_id')->nullable()->index(); // optional link to a primary item if needed
            $table->foreignId('status_id')->index(); // references statuses.id
            $table->dateTime('submitted_at')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('purchase_ref_no')->nullable()->unique();
            $table->text('approval_remarks')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status_id', 'submitted_at']);
            $table->index(['approved_by', 'approved_at']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
