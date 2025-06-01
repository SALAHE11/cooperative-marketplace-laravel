<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('cooperative_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_price', 8, 2);
            $table->decimal('subtotal', 10, 2);
            $table->json('product_snapshot')->nullable(); // Store product details at time of adding
            $table->timestamps();

            $table->unique(['cart_id', 'product_id']);
            $table->index(['cart_id', 'cooperative_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cart_items');
    }
};
