<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('orders', 'pickup_location')) {
                $table->string('pickup_location')->nullable()->after('shipping_address');
            }
            if (!Schema::hasColumn('orders', 'pickup_instructions')) {
                $table->text('pickup_instructions')->nullable()->after('pickup_location');
            }
            if (!Schema::hasColumn('orders', 'estimated_ready_at')) {
                $table->timestamp('estimated_ready_at')->nullable()->after('pickup_instructions');
            }
            if (!Schema::hasColumn('orders', 'ready_at')) {
                $table->timestamp('ready_at')->nullable()->after('estimated_ready_at');
            }
            if (!Schema::hasColumn('orders', 'picked_up_at')) {
                $table->timestamp('picked_up_at')->nullable()->after('ready_at');
            }
            if (!Schema::hasColumn('orders', 'picked_up_by')) {
                $table->string('picked_up_by')->nullable()->after('picked_up_at'); // 'client' or 'authorized_person'
            }
            if (!Schema::hasColumn('orders', 'client_phone')) {
                $table->string('client_phone')->nullable()->after('picked_up_by');
            }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'pickup_location',
                'pickup_instructions',
                'estimated_ready_at',
                'ready_at',
                'picked_up_at',
                'picked_up_by',
                'client_phone'
            ]);
        });
    }
};
