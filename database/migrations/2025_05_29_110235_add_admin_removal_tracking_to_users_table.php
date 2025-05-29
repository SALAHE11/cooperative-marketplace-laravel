<?php
// ===== 1. DATABASE MIGRATION =====
// File: database/migrations/2025_05_29_000002_add_admin_removal_tracking_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('removed_from_coop_at')->nullable()->after('last_login_at');
            $table->unsignedBigInteger('removed_by')->nullable()->after('removed_from_coop_at');
            $table->text('removal_reason')->nullable()->after('removed_by');

            $table->foreign('removed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['cooperative_id', 'status', 'removed_from_coop_at']);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['removed_by']);
            $table->dropIndex(['cooperative_id', 'status', 'removed_from_coop_at']);
            $table->dropColumn(['removed_from_coop_at', 'removed_by', 'removal_reason']);
        });
    }
};
