<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\UserStockAssignment;
use illuminate\Support\Facades\Exceptions;
use Illuminate\Validation\Rule;

class MedicineAssignController extends Controller
{
    //

    public function medicineAssignedExist(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'item_id' => 'required|integer|exists:tbl_items,id',
            ]);

            $assigned = UserStockAssignment::where('user_id', $validatedData['user_id'])
                ->where('item_id', $validatedData['item_id'])
                ->exists();

            return response()->json([
                'success' => true,
                'assigned' => $assigned
            ], 200);

        } catch (Exceptions $ex) {
            Log::info($ex);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }


    }

    public function assign_medicine(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'item_id' => [
                    'required',
                    'integer',
                    'exists:tbl_items,id',
                    Rule::unique('user_stock_assignment')
                        ->where('user_id', $request->user_id),
                ]
            ]);

            $assignment = UserStockAssignment::create([
                'user_id' => $validatedData['user_id'],
                'item_id' => $validatedData['item_id'],
            ]);

            return response()->json([
                'message' => 'Medicine assigned successfully',
                'assignment' => $assignment
            ], 201);
        } catch (Exceptions $ex) {
            Log::info($ex);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }

    }

    public function ShowAssignedMedicine(Request $request)
    {

        $validatedData = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        try {


            $assignedMedicines = UserStockAssignment::join('tbl_items', 'user_stock_assignment.item_id', '=', 'tbl_items.id')
                ->where('user_stock_assignment.user_id', $validatedData['user_id'])
                ->select('tbl_items.generic_name', 'tbl_items.po_no', 'tbl_items.id', 'user_stock_assignment.item_id')
                ->get();


            return response()->json([
                'success' => true,
                'assigned_medicines' => $assignedMedicines
            ], 200);
        } catch (Exceptions $ex) {
            Log::info($ex);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    public function RemoveAssignedMedicine(Request $request)
    {

        $validatedData = $request->validate([
            'user_id' => 'required|integer|exists:user_stock_assignment,user_id',
            'item_id' => 'required|integer|exists:user_stock_assignment,item_id',
        ]);

        try {

            $assigned = UserStockAssignment::where('user_id', $validatedData['user_id'])
                ->where('item_id', $validatedData['item_id'])
                ->exists();

            // if (!$assigned) {
            //     return response()->json(['message' => 'Item is not assigned to this user.'], 422);
            // }

            UserStockAssignment::where('user_id', $validatedData['user_id'])
                ->where('item_id', $validatedData['item_id'])
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Medicine removed successfully'
            ], 200);
        } catch (Exceptions $ex) {
            Log::info($ex);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }
}
