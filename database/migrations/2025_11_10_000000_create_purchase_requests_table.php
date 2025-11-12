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
            $table->timestamps();
            $table->index(['status', 'submitted_at']);
        });

        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->softDeletes();
        });

    }

    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::dropIfExists('purchase_requests');
    }
};
