<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Log;

class MaifController extends Controller

{
    //

    public function index()
    {
        // Example query to fetch data from the external database
        $data = DB::connection('external_mysql')
            ->table('patient as p')
            ->join('transaction as trx', 'p.id', '=', 'trx.patient_id')
            ->select('p.*', 'trx.transaction_type', 'trx.status', 'trx.transaction_number')
            ->where('trx.transaction_type', 'Medication')
            ->where('trx.status', 'qualified')
            ->get();

        return response()->json(['status' => 'success', 'customers' => $data], 200);
    }

    public function getPatientLatestTransactions(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|integer',
        ]);

        $transactionNumber = DB::connection('external_mysql')
            ->table('transaction as trx')
            ->where('trx.patient_id', $validated['patient_id'])
            ->orderByDesc('trx.transaction_date') // or trx.id if that's reliable
            ->value('trx.transaction_number');

        if (!$transactionNumber) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'No transactions found for this patient'
            ], 404);
        }

        return response()->json(['status' => 'success', 'trx_num' => $transactionNumber], 200);
    }

    public function store_medication(Request $request)
    {
        try {
            $validated = $request->validate([
                'transaction_id' => 'required|string',
                'status' => 'required|string|max:255',
            ]);


 $get_ID = DB::connection('external_mysql')->table('transaction')
                ->where('transaction_number', $validated['transaction_id'])
                ->value('id');


            DB::connection('external_mysql')->table('medication')->insert([
                'transaction_id' => $get_ID,
                'status' => $validated['status'],
                'created_at' => now(),
                'updated_at' =>now()
            ]);

            return response()->json(['status' => 'success', 'message' => 'Medication status Done'], 201);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database query failed',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store_medication_details(Request $request)
    {

        Log::info('Storing medication details', $request->all());

        try {

            $validated = $request->validate([

                'item_description' => 'required|string|max:150',
                'patient_id' => 'required|integer|max:100',
                'quantity' => 'required|integer',
                'unit' => 'required|string|max:100',
                'transaction_date' => 'required|date',
                'transaction_id' => 'required|string',
                'amount' => 'required|numeric',
                'item_id' => 'required|integer'


            ]);

            $get_ID = DB::connection('external_mysql')->table('transaction')
                ->where('transaction_number', $validated['transaction_id'])
                ->value('id');



            DB::connection('external_mysql')->table('medication_details')->insert([
                'item_id'=> $validated['item_id'],
                'item_description' => $validated['item_description'],
                'patient_id' => $validated['patient_id'],
                'quantity' => $validated['quantity'],
                'unit' => $validated['unit'],
                'transaction_date' => $validated['transaction_date'],
                'transaction_id' => $get_ID,
                'amount' => $validated['amount'],
                'created_at' => now(),
                'updated_at' => now()

            ]);

            return response()->json(['status' => 'success', 'message' => 'Medication details stored successfully'], 201);
        } catch (QueryException $e) {
            Log::info('Database query failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Database query failed',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::info('Unexpected error occurred', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function remove_order_medication_details(Request $request)
    {
        try {

            $validated = $request->validate([
                'transaction_id' => 'required|string',
                'item_id' => 'required|integer'
            ]);

             Log::info('Removing medication details', ['transaction_id' => $validated['transaction_id'], 'item_id' => $validated['item_id']]);
             
            $get_ID = DB::connection('external_mysql')->table('transaction')
                ->where('transaction_number', $validated['transaction_id'])
                ->value('id');

                Log::info('Removing medication details', ['transaction_id' => $get_ID, 'item_id' => $validated['item_id']]);

            DB::connection('external_mysql')->table('medication_details')
                ->where('transaction_id', $get_ID)
                ->where('item_id', $validated['item_id'])
                ->delete();

            return response()->json(['status' => 'success', 'message' => 'Medication details removed successfully'], 200);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database query failed',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
