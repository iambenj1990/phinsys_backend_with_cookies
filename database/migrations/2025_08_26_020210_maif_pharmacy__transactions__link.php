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
         Schema::create('tbl_maifp_trx',function (Blueprint $table){
            $table->id();
            $table-> string('maifp_id',200)->nullable();
            $table-> string('transaction_id',200)->nullable();
             $table-> string('pharma_trx_id',200)->nullable();

            $table->timestamps();
        });
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('tbl_maifp_trx');
    }
};
