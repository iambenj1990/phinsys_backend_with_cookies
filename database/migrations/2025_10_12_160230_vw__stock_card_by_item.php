<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        DB::statement("
          CREATE OR REPLACE VIEW vw_StockCardByItem  AS

        WITH daily_transactions AS (
                    SELECT
                        item_id,
                        transaction_date,
                        SUM(quantity) AS total_quantity
                    FROM tbl_daily_transactions
                    GROUP BY item_id, transaction_date
                ),
                daily_summary AS (
                    SELECT
                        di.transaction_date AS date,
                        i.po_no,
                        i.id AS item_id,
                        i.generic_name,
                        i.brand_name,

                        CASE
                            WHEN SUM(CASE WHEN LOWER(di.remarks) LIKE '%initial stock entry%' THEN 1 ELSE 0 END) > 0 THEN 'IN'
                            WHEN SUM(COALESCE(dt.total_quantity, 0)) > 0 THEN 'OUT'
                            ELSE 'OPENING'
                        END AS transaction_type,

                        SUM(CASE WHEN LOWER(di.remarks) LIKE '%initial stock entry%' THEN di.Openning_quantity ELSE 0 END) AS quantity_in,
                        SUM(COALESCE(dt.total_quantity, 0)) AS quantity_out,
                        MIN(di.Openning_quantity) AS openning,
                        MAX(di.Closing_quantity) AS closing,
                        GROUP_CONCAT(DISTINCT COALESCE(di.remarks, '') SEPARATOR '; ') AS remarks

                    FROM tbl_daily_inventory di
                    JOIN tbl_items i
                        ON i.id = di.stock_id
                    LEFT JOIN daily_transactions dt
                        ON dt.item_id = di.stock_id
                    AND dt.transaction_date = di.transaction_date

                    GROUP BY di.transaction_date, i.id, i.po_no, i.generic_name, i.brand_name
                )

                SELECT
                    ds.date,
                    ds.po_no,
                    ds.item_id,
                    ds.generic_name,
                    ds.brand_name,
                    ds.transaction_type,
                    ds.quantity_in,
                    ds.quantity_out,
                    ds.openning,
                    ds.closing,
                    ds.remarks,
                    SUM(ds.quantity_in - ds.quantity_out)
                        OVER (
                            PARTITION BY ds.item_id
                            ORDER BY ds.date
                            ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                        ) AS running_balance

                FROM daily_summary ds
                ORDER BY ds.item_id, ds.date;
         ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        DB::statement("drop view if exists vw_StockCardByItem;");
    }
};
