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
        //
        DB::statement("
        CREATE OR REPLACE VIEW vw_recipient_dispense AS
        SELECT

            dt.transaction_id,
            i.po_no AS `po`,
            i.id AS `item_id`,
            CONCAT_WS(' ', i.brand_name, i.generic_name, i.dosage, i.dosage_form) AS `item`,
            i.expiration_date,
            dt.quantity,

            CONCAT_WS(' ', c.lastname, c.firstname, c.middlename, c.ext) AS `recipient_name`,
            c.gender,
            c.age,
            c.category AS `cus_category`,
            c.barangay,
            dt.transaction_date

        FROM
            tbl_items i
        JOIN
            tbl_daily_transactions dt ON dt.item_id = i.id
        JOIN
            tbl_customers c ON c.id = dt.customer_id
        ORDER BY
            dt.id, dt.transaction_date DESC;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        DB::statement("DROP VIEW IF EXISTS vw_recipient_dispense;");
    }
};
