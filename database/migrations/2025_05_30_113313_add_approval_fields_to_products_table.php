<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Add approval workflow fields
            $table->datetime('submitted_at')->nullable()->after('is_active');
            $table->datetime('reviewed_at')->nullable()->after('submitted_at');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->after('reviewed_at');
            $table->text('admin_notes')->nullable()->after('reviewed_by');

            // Update status enum to include new states
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'needs_info'])->default('draft')->change();
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['submitted_at', 'reviewed_at', 'reviewed_by', 'admin_notes']);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->change();
        });
    }
};
