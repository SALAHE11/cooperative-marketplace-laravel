<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cooperatives', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_status');
            $table->date('date_created');
            $table->text('address');
            $table->string('phone');
            $table->string('email');
            $table->string('logo_path')->nullable();
            $table->text('description')->nullable();
            $table->string('sector_of_activity');
            $table->enum('status', ['pending', 'approved', 'suspended'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cooperatives');
    }
};
