<?php
// database/migrations/2025_05_31_000003_add_primary_image_to_products.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('primary_image_id')->nullable()->after('admin_notes')->constrained('product_images')->onDelete('set null');
            $table->integer('images_count')->default(0)->after('primary_image_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('primary_image_id');
            $table->dropColumn('images_count');
        });
    }
};
