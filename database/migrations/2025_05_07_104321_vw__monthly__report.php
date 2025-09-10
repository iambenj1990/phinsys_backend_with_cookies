<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //     //
        //     DB::statement("
        //     CREATE OR REPLACE VIEW vw_monthly_dispense_report AS
        //         SELECT
        // i.po_no,
        // di.stock_id,
        // concat( i.brand_name,' ',
        // i.generic_name,' ',
        // i.dosage,' ',
        // i.dosage_form) as item,
        // MONTHNAME(di.transaction_date) AS month_name,
        // MONTH(di.transaction_date) AS month_number,
        // i.quantity,
        // (i.quantity - sum(di.quantity_out)) as balance,
        // SUM(di.quantity_out) AS total_dispensed,
        // YEAR(di.transaction_date) AS Trans_year

        //     FROM
        //         tbl_daily_inventory di
        //     JOIN
        //         tbl_items i ON di.stock_id = i.id

        //     GROUP BY
        //         i.po_no, di.stock_id, month_number, month_name, Trans_year, i.brand_name, i.generic_name, i.dosage, i.dosage_form,i.quantity
        //     ORDER BY
        //     month_number
        //     ");

        DB::statement("
                    CREATE OR REPLACE VIEW vw_monthly_dispense_report AS
                    SELECT
                i.po_no AS po_no,
                di.stock_id AS stock_id,
                CONCAT(i.brand_name, ' ', i.generic_name, ' ', i.dosage, ' ', i.dosage_form) AS item,
                MONTHNAME(di.transaction_date) AS month_name,
                MONTH(di.transaction_date) AS month_number,
                i.quantity AS quantity,
                (
                    SELECT d2.Closing_quantity
                    FROM tbl_daily_inventory d2
                    WHERE d2.stock_id = di.stock_id
                    ORDER BY d2.transaction_date DESC, d2.id DESC
                    LIMIT 1
                ) AS latest_balance,
                SUM(di.quantity_out) AS total_dispensed,
                YEAR(di.transaction_date) AS Trans_year
            FROM
                tbl_daily_inventory di
            JOIN tbl_items i
                ON di.stock_id = i.id
            GROUP BY
                i.po_no, di.stock_id, MONTH(di.transaction_date),
                MONTHNAME(di.transaction_date), YEAR(di.transaction_date),
                i.brand_name, i.generic_name, i.dosage, i.dosage_form, i.quantity
            ORDER BY
                YEAR(di.transaction_date), MONTH(di.transaction_date);


        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        DB::statement("DROP VIEW IF EXISTS vw_monthly_report;");
    }
};
