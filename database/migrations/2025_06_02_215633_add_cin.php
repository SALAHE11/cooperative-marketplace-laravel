<?php
// =====================================================================================
// FILE: database/migrations/2025_06_02_100000_add_cin_to_authorization_receipts_table.php
// =====================================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('authorization_receipts', function (Blueprint $table) {
            $table->string('authorized_person_cin', 20)->nullable()->after('authorized_person_name');
        });
    }

    public function down()
    {
        Schema::table('authorization_receipts', function (Blueprint $table) {
            $table->dropColumn('authorized_person_cin');
        });
    }
};
