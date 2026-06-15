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
        //
         Schema::create('tbl_StockAdjustment', function (Blueprint $table) {
            $table->id()->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('item_id')->index();
            $table-> string('type',200)->nullable();
            $table->integer('prev_quantity')->default(0);
            $table->integer('quantity')->default(0);
            $table->longText('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('tbl_StockAdjustment');
    }
};
