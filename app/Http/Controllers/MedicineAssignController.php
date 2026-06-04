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

    public function assign_medicine(Request $request){
    try{
        $validatedData = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'item_id' => ['required', 'integer', 'exists:tbl_items,id',
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
    }
    catch(Exceptions $ex){
        Log::info($ex);
    }

    }

    public function ShowAssignedMedicine(Request $request){

            $validatedData = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
            ]);

        try{

            $assignedMedicines = UserStockAssignment::with('item')
                ->where('user_id', $validatedData['user_id'])
                ->get();

            return response()->json(['success' => true,
                'assigned_medicines' => $assignedMedicines
            ], 200);
        }
        catch(Exceptions $ex){
            Log::info($ex);
        }
    }
}
