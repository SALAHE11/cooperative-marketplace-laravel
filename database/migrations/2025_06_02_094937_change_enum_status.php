<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First update any existing 'confirmed' or 'processing' records to 'ready'
        DB::statement("UPDATE orders SET status = 'ready' WHERE status IN ('confirmed', 'processing')");

        // Then alter the enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','ready','completed','cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','confirmed','processing','completed','cancelled') NOT NULL DEFAULT 'pending'");
    }
};
