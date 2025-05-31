<?php
// database/migrations/2025_05_31_000002_enhance_product_images_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->bigInteger('file_size')->nullable()->after('thumbnail_path');
            $table->integer('width')->nullable()->after('file_size');
            $table->integer('height')->nullable()->after('width');
            $table->string('mime_type', 100)->nullable()->after('height');
            $table->string('original_filename')->nullable()->after('mime_type');
            $table->enum('processing_status', ['pending', 'processing', 'ready', 'failed'])->default('ready')->after('original_filename');
            $table->text('failure_reason')->nullable()->after('processing_status');
            $table->string('file_hash', 64)->nullable()->after('failure_reason');
            $table->timestamp('deleted_at')->nullable()->after('file_hash');

            $table->index('file_hash');
            $table->index('deleted_at');
            $table->index('processing_status');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex(['file_hash']);
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['processing_status']);

            $table->dropColumn([
                'file_size',
                'width',
                'height',
                'mime_type',
                'original_filename',
                'processing_status',
                'failure_reason',
                'file_hash',
                'deleted_at'
            ]);
        });
    }
};
