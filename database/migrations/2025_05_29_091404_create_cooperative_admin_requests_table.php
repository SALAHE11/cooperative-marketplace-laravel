<?php
// ===== 1. DATABASE MIGRATION =====
// File: database/migrations/2025_05_29_000001_create_cooperative_admin_requests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cooperative_admin_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // The user requesting to join
            $table->unsignedBigInteger('cooperative_id'); // The cooperative they want to join
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('message')->nullable(); // Optional message from requester
            $table->text('response_message')->nullable(); // Response from cooperative admin
            $table->unsignedBigInteger('responded_by')->nullable(); // Who approved/rejected
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('cooperative_id')->references('id')->on('cooperatives')->onDelete('cascade');
            $table->foreign('responded_by')->references('id')->on('users')->onDelete('set null');

            $table->unique(['user_id', 'cooperative_id']); // Prevent duplicate requests
            $table->index(['cooperative_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cooperative_admin_requests');
    }
};

