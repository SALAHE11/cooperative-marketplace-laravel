<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('authorization_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('auth_number')->unique();
            $table->foreignId('client_receipt_id')->constrained()->onDelete('cascade');
            $table->string('authorized_person_name');
            $table->timestamp('validity_start')->nullable();
            $table->timestamp('validity_end')->nullable();
            $table->string('unique_code')->unique();
            $table->text('qr_code_data')->nullable();
            $table->boolean('is_revoked')->default(false);
            $table->boolean('is_used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('authorization_receipts');
    }
};
