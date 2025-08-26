<?php

namespace App\Http\Controllers;


use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function store_medication(Request $request) {

        $validated = $request->validate([
            'transaction_id' => 'required|integer',
            'status' => 'required|string|max:255',
        ]);

        try {
            DB::connection('external_mysql')->table('medications')->insert([
                'transaction_id' => $validated['transaction_id'],
                'status' => $validated['status'],
            ]);

            return response()->json(['status' => 'success', 'message' => 'Medication stored successfully'], 201);
        } catch (QueryException $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to store medication', 'error' => $e->getMessage()], 500);
        }
    }

    public function store_medication_details(Request $request){



        $validated = $request->validate([
            'medication_id' => 'required|integer',
            'item_description' => 'required|string|max:150',
            'patient_id' => 'required|integer|max:100',
            'quantity' => 'required|integer',
            'unit' => 'required|string|max:100',
            'transaction_date' => 'required|date',
            'user_id' => 'required|integer',
            'amount' => 'required|numeric',
        ]);

        try {
            DB::connection('external_mysql')->table('medication_details')->insert([
                'medication_id' => $validated['medication_id'],
                'dosage' => $validated['dosage'],
                'frequency' => $validated['frequency'],
            ]);

            return response()->json(['status' => 'success', 'message' => 'Medication details stored successfully'], 201);
        } catch (QueryException $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to store medication details', 'error' => $e->getMessage()], 500);
        }
    }
}
