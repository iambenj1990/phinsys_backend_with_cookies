<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    //

    public function Monthly_Dispense()
    {
        // Fetch all montly dispense reports
        try {

            $year = date('Y');
            $reports_monthly_dispense = DB::table('vw_monthly_dispense_report')
            ->where('Trans_year', $year)
            ->get();
            return response()->json(['dispense' => $reports_monthly_dispense], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['error' => 'Database query error', 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    public function Monthly_Dispense_By_Year($year)
    {
        // Fetch monthly dispense reports by year
        try {
            $reports_monthly_dispense = DB::table('vw_monthly_dispense_report')
                ->whereYear('Trans_year', $year)
                ->get();
            return response()->json(['dispense' => $reports_monthly_dispense], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['error' => 'Database query error', 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }
    public function Monthly_Dispense_By_Month($year, $month)
    {
        // Fetch monthly dispense reports by year and month
        try {
            $reports_monthly_dispense = DB::table('vw_monthly_dispense_report')
                ->where('Trans_year', $year)
                ->where('month_name', $month)
                ->get();
            return response()->json(['dispense' => $reports_monthly_dispense], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['error' => 'Database query error', 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    public function Recipients_Report(Request $request)
    {
        // Fetch all recipients reports
        try {

            $validatedInput = $request->validate([
                'item_id' => 'integer|required',
            ]);

            // if(!isset($validatedInput['item_id'])){
            //     return response()->json(['success'=> false,'message' => 'item_id is required'], 400);
            // }

            $reports_recipients = DB::table('vw_recipient_dispense')
            ->where('item_id', $validatedInput['item_id'])
            ->where('transaction_id','NOT LIKE','%RIS%')
            ->get();
            return response()->json(['success'=> true,'recipients' => $reports_recipients], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['error' => 'Database query error', 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }
    public function Recipients_Report_By_Year($year)
    {
        // Fetch recipients reports by year
        try {
            $reports_recipients = DB::table('vw_recipient_dispense')
                ->whereYear('transaction_date', $year)
                ->get();
            return response()->json(['recipients' => $reports_recipients], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['error' => 'Database query error', 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }
    public function Recipients_Report_By_Month($year, $month)
    {
        // Fetch recipients reports by year and month
        try {
            $reports_recipients = DB::table('vw_recipient_dispense')
                ->whereYear('transaction_date', $year)
                ->whereMonth('transaction_date', $month)
                  ->where('transaction_id','NOT LIKE','%RIS%')
                ->get();
            return response()->json(['recipients' => $reports_recipients], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['error' => 'Database query error', 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }
}
