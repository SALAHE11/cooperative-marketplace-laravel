<?php
// database/migrations/2024_01_15_000000_add_stock_alert_threshold_to_products_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock_alert_threshold')->default(5)->after('stock_quantity');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('stock_alert_threshold');
        });
    }
};
