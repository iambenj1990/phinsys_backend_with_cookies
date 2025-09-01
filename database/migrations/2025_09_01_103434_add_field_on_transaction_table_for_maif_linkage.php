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
        Schema::table('tbl_daily_transactions', function (Blueprint $table) {

            //

            $table->string('origin',200)->nullable()->after('transaction_date');
            $table->string('maifp_id',200)->nullable()->after('origin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_daily_transactions', function (Blueprint $table) {
            //
             $table->dropColumn(['origin', 'maifp_id']);
        });
    }
};
