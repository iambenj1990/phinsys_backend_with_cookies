<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
        CREATE OR REPLACE VIEW vw_orders_information AS
        select
               i.id,
               i.brand_name,
               i.generic_name,
               i.dosage,
               i.dosage_form,

               dt.quantity,
               ROUND(i.price_per_pcs * dt.quantity, 2) AS amount,
               dt.transaction_id
        from tbl_items i
        inner join
        tbl_daily_transactions dt on i.id=dt.item_id;
       ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //

        DB::statement("drop view if exists vw_orders_information;");
    }
};
