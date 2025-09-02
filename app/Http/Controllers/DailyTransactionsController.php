<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Daily_transactions as Transactions;
use App\Models\items;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use function PHPUnit\Framework\isEmpty;

class DailyTransactionsController extends Controller
{
    //

    public function newTransactionID($id)
    {
        $dateNow = now()->format('Ymd');  // Get date as YYYYMMDD

        // Find the last transaction for this customer on the same date
        $lastTransaction = Transactions::where('customer_id', $id)
            ->whereDate('created_at', now()->toDateString()) // Ensures it's from the same date
            ->max('transaction_id');

        // Extract the last numeric part and increment
        if ($lastTransaction && preg_match('/\d{8}-\d+-\d+/', $lastTransaction)) {
            $parts = explode('-', $lastTransaction);
            $nextTransactionNumber = isset($parts[2]) ? (intval($parts[2]) + 1) : 1;
        } else {
            $nextTransactionNumber = 1; // Start from 1 if no transactions exist for today
        }

        // Ensure numbering is 6-digit padded (e.g., 000001)
        $transactionNumber = str_pad($nextTransactionNumber, 6, '0', STR_PAD_LEFT);

        return $dateNow . '-' . $id . '-' . $transactionNumber;
    }



    public function index()
    {
        try {

            $transactions = Transactions::orderBy('id', 'desc')
                ->get();
            return response()->json(['success' => true, 'transactions' => $transactions]);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
            //throw $th;
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {

        try {

            $transactions = Transactions::where('id', $id)
                ->get();
            return response()->json(['success' => true, 'transactions' => $transactions]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
                'error' => 'Record not found ' . $e,
            ], 404);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
            //throw $th;
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function showLatestOrder($transactionID)
    {
        try {

            // $data = DB::table('tbl_items')
            //     ->join('tbl_daily_inventory', 'tbl_items.id', '=', 'tbl_daily_inventory.stock_id') // Joining on the common column
            //     ->select(
            //         'tbl_items.id as item_id',
            //         'tbl_items.po_no',
            //         'tbl_items.brand_name',
            //         'tbl_items.generic_name',
            //         'tbl_items.dosage',
            //         'tbl_items.dosage_form',
            //         'tbl_items.unit',
            //         'tbl_items.quantity',
            //         'tbl_daily_inventory.Closing_quantity',
            //         'tbl_items.expiration_date',
            //     ) // Selecting specific columns
            //     ->get();

            $transactions = Transactions::where('transaction_id', $transactionID)
                ->join('tbl_items', 'tbl_daily_transactions.item_id', '=', 'tbl_items.id')
                ->select(
                    'tbl_daily_transactions.quantity',
                    'tbl_daily_transactions.customer_id',
                    'tbl_daily_transactions.id as table_id_transactions',
                    'tbl_items.brand_name',
                    'tbl_items.generic_name',
                    'tbl_items.dosage',
                    'tbl_items.dosage_form',
                    'tbl_items.unit',
                    'tbl_items.id as item_id'
                )
                ->get();
            return response()->json(['success' => true, 'transactions' => $transactions]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
                'error' => 'Record not found ' . $e,
            ], 404);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
            //throw $th;
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {

        try {

            $validationInput = $request->validate(
                [
                    'item_id' => 'required|exists:tbl_items,id',
                    'transaction_id' => 'required|string',
                    'customer_id' => 'required|numeric',
                    'quantity' => 'required|numeric|min:1',
                    'unit' => 'nullable|string',
                    'transaction_date' => 'required|date',
                    'origin' => 'nullable|string',
                    'maifp_id' => 'nullable|string',
                    'user_id' => 'required|exists:users,id'
                ]
            );

            $transactions = Transactions::create($validationInput);
            return response()->json([
                'success' => true,
                'customers' => $transactions
            ]);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
            //throw $th;
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $transactions = Transactions::find($id);
            if (!$transactions) {
                return response()->json(['success' => false, 'message' => 'transaction not found'], 404);
            }


            $validationInput = $request->validate(
                [
                    'item_id' => 'required|exists:tbl_items,id',
                    'transaction_id' => 'required|string',
                    'customer_id' => 'required|numeric',
                    'quantity' => 'required|numeric|min:1',
                    'unit' => 'nullable|string',
                    'transaction_date' => 'required|date',
                    'origin' => 'nullable|string',
                    'maifp_id' => 'nullable|string',
                    'user_id' => 'required|exists:users,id'
                ]
            );

            $transactions->update($validationInput);
            return response()->json([
                'success' => true,
                'customers' => $transactions
            ]);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
            //throw $th;
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $transactions = Transactions::where('id', $id)->firstOrFail();
            $transactions->delete();
            return response()->json([
                'success' => true,
                'message' => 'transaction deleted.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
                'error' => 'Record not found : ' . $e,
            ], 404);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
            //throw $th;
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getTransactionID($userid)
    {
        try {
            $transactions = Transactions::where('customer_id', $userid)
                ->distinct()
                ->pluck('transaction_id');

            return response()->json($transactions, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
                'error' => 'Record not found : ' . $e,
            ], 404);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
            //throw $th;
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getCustomersWithLatestTransactions(Request $request)
    {
        try {

            $ValidateRequest = $request->validate(
                [
                    'customer_id' => 'required|numeric',
                ]
            );

            $Latest_transaction_id = DB::table('tbl_daily_transactions')
                ->where('customer_id', $ValidateRequest['customer_id'])
                ->where('transaction_id', 'not like', '%RIS%')
                ->orderByDesc('id')
                ->value('transaction_id');


            if (!$Latest_transaction_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No transactions found for this customer',
                ], 404);
            }

            // Get items of that transaction
            $get_items_of_this_transaction = DB::table('vw_orders_information')
                ->where('transaction_id', $Latest_transaction_id)
                ->get();


            return response()->json([
                'success' => true,
                'data' => $get_items_of_this_transaction,
                'message' => 'Customers with latest transactions retrieved successfully',
            ], 200);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred',
                'error' => $qe->getMessage(),
            ], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage(),
            ], 500);
        }
    }



    public function CustomersWithTransactionsToday($id)
    {


        try {
            //  $today = Carbon::today()->toDateString(); // e.g. '2025-05-02'

            // Subquery to fetch the latest transaction for each customer **on the current day**

            $getLatestTransactionDate = DB::table('vw_patients_overall_transactions')
                ->where('transaction_id', 'not like', '%RIS%')
                ->where('customer_id', '=', $id)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('transaction_id', 'desc')
                ->first();

            $latestTransactionQuery = DB::table('tbl_daily_transactions as t1')
                ->select(
                    't1.customer_id',
                    't1.transaction_id',
                    't1.transaction_date',
                    DB::raw('ROW_NUMBER() OVER (
                    PARTITION BY t1.customer_id
                    ORDER BY t1.transaction_date DESC, t1.transaction_id DESC
                ) AS rn')
                )
                ->whereDate('t1.transaction_date', $getLatestTransactionDate->transaction_id) // Filter transactions for today
                ->where('t1.transaction_id', 'not like', '%RIS%');  // Exclude RIS transaction IDs

            // Join with customers table and filter for the latest transaction (rn = 1)
            $customersWithLatestTransactions = DB::table('tbl_customers')
                ->joinSub($latestTransactionQuery, 'latest_transactions', function ($join) {
                    $join->on('tbl_customers.id', '=', 'latest_transactions.customer_id');
                })
                ->select(
                    'tbl_customers.id as customer_id',
                    'tbl_customers.firstname',
                    'tbl_customers.lastname',
                    'tbl_customers.middlename',
                    'tbl_customers.ext',
                    'tbl_customers.birthdate',
                    'tbl_customers.age',
                    'tbl_customers.contact_number',
                    'tbl_customers.barangay',
                    'latest_transactions.transaction_id',
                    'latest_transactions.transaction_date'
                )
                ->where('latest_transactions.rn', 1) // Select only the latest transaction per customer
                ->get();

            return response()->json([
                'success' => true,
                'data' => $customersWithLatestTransactions,
                'message' => 'Customers with latest transactions for today retrieved successfully',
            ], 200);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred',
                'error' => $qe->getMessage(),
            ], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function Customer_Transaction_List($id)
    {

        try {

            $list_level = DB::table('vw_patients_overall_transactions')
                ->where('customer_id', '=', $id)
                ->where('transaction_id', 'not like', '%RIS%')
                ->get();

            if ($list_level->isEmpty()) {
                return response()->json('No list Available...', 400);
            }

            return response()->json([
                'success' => true,
                'result' => $list_level
            ], 200);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred',
                'error' => $qe->getMessage(),
            ], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function Customer_Transaction_List_Breakdown($id, $transaction_id)
    {

        try {
            $list_breakdown = DB::table('vw_patient_transactions_list')
                ->where('customer_id', '=', $id)
                ->where('transaction_id', '=', $transaction_id)
                ->where('transaction_id', 'not like', '%RIS%')
                ->get();

            if ($list_breakdown->isEmpty()) {
                return response()->json('No list Available...', 400);
            }

            return response()->json([
                'success' => true,
                'result' => $list_breakdown
            ], 200);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred',
                'error' => $qe->getMessage(),
            ], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function Customer_Transaction(Request $request)
    {


        try {

            $validateRequest = $request->validate(
                [
                    'transaction_id' => 'required|string',
                ]
            );


            $list_breakdown = DB::table('vw_orders_information')
                ->where('transaction_id', '=', $validateRequest['transaction_id'])
                ->get();

            if ($list_breakdown->isEmpty()) {
                return response()->json('No list Available...', 400);
            }

            return response()->json([
                'success' => true,
                'result' => $list_breakdown
            ], 200);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred',
                'error' => $qe->getMessage(),
            ], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
