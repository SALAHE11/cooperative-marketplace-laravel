<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('session_data')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->integer('total_items')->default(0);
            $table->timestamp('last_activity')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'last_activity']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('carts');
    }
};
