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
        Schema::table('tbl_StockAdjustment', function (Blueprint $table) {
            //
            $table->string('method', 200)->nullable()->after('type');
                $table->integer('new_quantity')->nullable()->after('quantity');
                $table->date('approve_date')->nullable()->after('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_StockAdjustment', function (Blueprint $table) {
            //
             $table->dropColumn('method');
             $table->dropColumn('new_quantity');
             $table->dropColumn('approve_date');
        });
    }
};
