<?php

namespace App\Http\Controllers;

use Throwable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\RequisitionIssuanceSlip;
use App\Models\daily_transactions;
use Illuminate\Auth\Events\Validated;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RequisitionIssuanceSlipController extends Controller
{
    //
    public function RIS_id()
    {
        $currendate = Carbon::today()->format("Ymd");
        $latestId = RequisitionIssuanceSlip::max('id') ?? 0;
        $nextId = $latestId + 1;
        $transactionNumber = str_pad($nextId, 6, '0', STR_PAD_LEFT);

        return "RIS-{$currendate}-{$transactionNumber}";
    }

    public function RIS_TransactionDate(Request $request)
    {

        $validated = $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        try {
            $list = RequisitionIssuanceSlip::dateBetween($validated['from'], $validated['to'])
                ->orderBy('created_at', 'asc')
                ->get();
            return response()->json(['list' => $list], 200);
        } catch (QueryException $qe) {

            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
        } catch (Throwable $th) {


            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }






    public function index()
    {

        try {
            $list = RequisitionIssuanceSlip::all();
            return response()->json(['list' => $list], 200);
        } catch (QueryException $qe) {

            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
        } catch (Throwable $th) {


            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function RIS_INFO(Request $request)
    {
        $validated = $request->validate([
            'ris_no' => 'required|string',
        ]);

        $list = RequisitionIssuanceSlip::where('ris_id', $validated['ris_no'])
            ->get();

        return response()->json(['info' => $list], 200);
    }



    public function RIS(Request $request)
    {
        $validated = $request->validate([
            'ris_no' => 'required|string',
        ]);

        // $list = daily_transactions::where('transaction_id', $validated['ris_no'])
        //                             ->get();

        $list = DB::table('vw_orders_information')
            ->where('transaction_id', $validated['ris_no'])
            ->get();

        return response()->json(['list' => $list], 200);
    }

    public function show(Request $request)
    {
        $validated = $request->validate(
            [
                'transaction_date_from' => 'required|date',
                'transaction_date_to' => 'required|date',
            ]
        );

        $list = RequisitionIssuanceSlip::whereBetween('transaction_date', [$validated['transaction_date_from'], $validated['transaction_date_to']])
            ->get();

        return Response()->json(['list' => $list], 200);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $currendate = Carbon::today()->format('Ymd');

            $latestId = RequisitionIssuanceSlip::max('id') ?? 0;
            $nextId = $latestId + 1;
            $transactionNumber = str_pad($nextId, 6, '0', STR_PAD_LEFT);
            $ris_id = "RIS-{$currendate}-{$transactionNumber}";


            // Properly merge Auth ID into request data
            $request->merge([
                'userid' => Auth::id(),
                'transaction_date' => $currendate,
                'ris_id' => $ris_id
            ]);


            $validated = $request->validate(
                [
                    'transaction_date' => 'required|date',
                    'purpose' => 'required|string|max:500|unique:tbl_ris,purpose',
                    'ris_id' => 'required|string',
                    'userid' => 'required|exists:users,id'
                ]
            );

            $ris = RequisitionIssuanceSlip::create($validated);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Successfuly Saved.', 'ris' => $ris, 'id' => $ris->id, 'ris_id' => $ris->ris_id], 201);
        } catch (ValidationException $ve) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
            //throw $th;
        } catch (QueryException $qe) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (Throwable $th) {
            DB::rollBack();
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            // Validate only the 'purpose' field
            $validated = $request->validate([
                'purpose' => 'required|string|max:500',
                'id' => 'required|numeric|exists:tbl_ris,id'
            ]);

            // Find the record
            $ris = RequisitionIssuanceSlip::findOrFail($validated['id']);

            // Update only the purpose
            $ris->purpose = $validated['purpose'];
            $ris->save();

            return response()->json([
                'success' => true,
                'message' => 'Purpose updated successfully.',
                'updated' => $ris
            ], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'RIS record not found.'
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error occurred.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function remove_item(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|numeric',
            'transaction_id' => 'required|string'
        ]);

        try {
            $ris = daily_transactions::where('transaction_id', $validated['transaction_id'])
                ->where('item_id', $validated['item_id'])
                ->first();
            $ris->delete();

            return response()->json([
                'success' => true,
                'message' => 'RIS record deleted successfully.'
            ], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error occurred.',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
