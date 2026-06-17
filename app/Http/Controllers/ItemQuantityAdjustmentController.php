<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class ItemQuantityAdjustmentController extends Controller
{
    //

     public function getCurrentStockQuantity($id)
    {
        try {
            
           

            $today = Carbon::today()->toDateString();

            $CloseStocks = DB::table('tbl_daily_inventory')
                ->where('status', 'CLOSE')
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

    public function getItemDescription($id){

        try {
            $itemDescription = DB::table('tbl_items')
                ->where('id', $id)
                ->select(['description', 'brand_name','generic_name','dosage','expiration_date'])
                ->first();

            if ($itemDescription) {
                return $itemDescription ;
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

    public function MedicalDescription(Request $request){
          try {

          $validatedData = $request->validate([
            'item_id' => 'required|integer',
        ]);

            return response()->json([
                'success' => true,
                'message' => 'Validation successful',
                'item_description' => $this->getItemDescription($validatedData['item_id']),
                'current_stock_quantity' => $this->getCurrentStockQuantity($validatedData['item_id']),
            ], 200);

          }catch (ValidationException $ve) {
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
}
