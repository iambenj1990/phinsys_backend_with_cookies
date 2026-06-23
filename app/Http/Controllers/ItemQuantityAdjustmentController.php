<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use App\Models\ItemQuantityAdjustment;

class ItemQuantityAdjustmentController extends Controller
{
    //

    public const AdjustmentTypeList = [
        'Physical Count Adjustment',
        'Inventory Reconciliation',
        'Encoding Error Correction',
    ];

    public function getAdjustmentTypes()
    {
        return response()->json(['success' => true, 'adjustmentType' => self::AdjustmentTypeList]);
    }

    public function getCurrentStockQuantity($id)
    {
        try {



            $today = Carbon::today()->toDateString();

            $CloseStocks = DB::table('tbl_daily_inventory')
                ->where('status', 'OPEN')
                ->whereDate('transaction_date', $today)
                ->where('stock_id', $id)
                ->get();




            return $CloseStocks;
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
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

    public function getItemDescription($id)
    {

        try {
            $itemDescription = DB::table('tbl_items')
                ->where('id', $id)
                ->select(['id', 'po_no', 'brand_name', 'generic_name', 'dosage', 'expiration_date'])
                ->first();

            if ($itemDescription) {
                return $itemDescription;
            }
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
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

    public function getItemInformation($ItemId)
    {
        try {


            if (!is_numeric($ItemId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid item id'
                ], 422);
            }

            $itemDescription = $this->getItemDescription($ItemId);
            $currentStockQuantity = $this->getCurrentStockQuantity($ItemId);

            Log::info('Current Stock Quantity', [
                'item_description' => $itemDescription,
                'current_stock_quantity' => $currentStockQuantity
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Validation successful',
                'item_description' => $itemDescription,
                'current_stock_quantity' => $currentStockQuantity,
            ], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $ve->errors(),
            ], 422);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
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


    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'item_id' => 'required|integer|exists:tbl_daily_inventory,stock_id',
            'type' => 'required|string',
            'prev_quantity' => 'required|integer',
            'quantity' => 'required|integer',
            'remarks' => 'required|string',
            'is_approved' => 'required|boolean',
            'approved_by' =>  'required|integer|exists:users,id'
        ]);
        try {

            $requested = ItemQuantityAdjustment::create($validatedData);
            if (!$requested) {
         return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
            ], 500);
            }

             return response()->json([
                'success' => true,
                'message' => 'Request to adjust has been saved, waiting for supervisors approval',
            ], 200);

        } catch (\Throwable $th) {


            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage(),
            ], 500);
        }
    }


    public function index(){
         try {

            $requested = ItemQuantityAdjustment::orderByDesc('id');
            if (!$requested) {
         return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
            ], 500);
            }

             return response()->json([
                'success' => true,
                'list' => $requested,
            ], 200);

        } catch (\Throwable $th) {


            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage(),
            ], 500);
        } catch ( QueryException $qe){
              return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $qe->getMessage(),
            ], 500);
        }
    }
}
