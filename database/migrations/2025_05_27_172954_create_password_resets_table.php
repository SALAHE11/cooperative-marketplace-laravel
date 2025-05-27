<?php

// File: database/migrations/YYYY_MM_DD_HHMMSS_create_password_resets_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('password_resets');

        Schema::create('password_resets', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('code', 6); // 6-digit code
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->timestamps();

            $table->index(['email', 'code']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('password_resets');
    }
};

?>
