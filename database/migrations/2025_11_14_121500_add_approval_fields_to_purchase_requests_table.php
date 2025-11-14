<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->text('approval_comment')->nullable()->after('attachment_path');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('approval_comment');
            $table->dateTime('approved_at')->nullable()->after('approved_by');
            $table->index(['approved_by', 'approved_at']);
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropIndex(['approved_by', 'approved_at']);
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['approval_comment', 'approved_by', 'approved_at']);
        });
    }
};
