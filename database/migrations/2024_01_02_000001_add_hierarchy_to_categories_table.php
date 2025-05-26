<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');
            $table->integer('level')->default(0)->after('parent_id');
            $table->string('path')->nullable()->after('level');
            $table->integer('children_count')->default(0)->after('path');
            $table->integer('sort_order')->default(0)->after('children_count');

            // Foreign key constraint
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');

            // Indexes for better performance
            $table->index('parent_id');
            $table->index('level');
            $table->index('path');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['level']);
            $table->dropIndex(['path']);
            $table->dropIndex(['sort_order']);
            $table->dropColumn(['parent_id', 'level', 'path', 'children_count', 'sort_order']);
        });
    }
};
