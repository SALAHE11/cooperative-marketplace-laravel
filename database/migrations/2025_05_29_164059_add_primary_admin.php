<?php
// File: database/migrations/2025_05_29_120000_add_primary_admin_to_cooperatives_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cooperatives', function (Blueprint $table) {
            $table->unsignedBigInteger('primary_admin_id')->nullable()->after('suspended_by');

            $table->foreign('primary_admin_id')->references('id')->on('users')->onDelete('set null');
            $table->index('primary_admin_id');
        });
    }

    public function down()
    {
        Schema::table('cooperatives', function (Blueprint $table) {
            $table->dropForeign(['primary_admin_id']);
            $table->dropIndex(['primary_admin_id']);
            $table->dropColumn('primary_admin_id');
        });
    }
};
