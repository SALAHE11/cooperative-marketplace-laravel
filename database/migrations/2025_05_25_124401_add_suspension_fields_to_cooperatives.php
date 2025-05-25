<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cooperatives', function (Blueprint $table) {
            $table->timestamp('suspended_at')->nullable()->after('rejection_reason');
            $table->string('suspension_reason')->nullable()->after('suspended_at');
            $table->unsignedBigInteger('suspended_by')->nullable()->after('suspension_reason');

            $table->foreign('suspended_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('cooperatives', function (Blueprint $table) {
            $table->dropForeign(['suspended_by']);
            $table->dropColumn(['suspended_at', 'suspension_reason', 'suspended_by']);
        });
    }
};
