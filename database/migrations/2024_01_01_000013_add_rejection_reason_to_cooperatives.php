<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cooperatives', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('status');
            $table->enum('status', ['pending', 'approved', 'suspended', 'rejected'])->default('pending')->change();
        });
    }

    public function down()
    {
        Schema::table('cooperatives', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
            $table->enum('status', ['pending', 'approved', 'suspended'])->default('pending')->change();
        });
    }
};
