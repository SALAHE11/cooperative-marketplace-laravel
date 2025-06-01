<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('client_receipts', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('client_receipts', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('total_amount');
            }
            if (!Schema::hasColumn('client_receipts', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('client_receipts', 'issued_at')) {
                $table->timestamp('issued_at')->nullable()->after('payment_reference');
            }
        });
    }

    public function down()
    {
        Schema::table('client_receipts', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_reference', 'issued_at']);
        });
    }
};
