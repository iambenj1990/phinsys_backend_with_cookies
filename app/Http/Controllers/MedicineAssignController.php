<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use app\Models\UserStockAssignment;
use illuminate\Support\Facades\Exceptions;

class MedicineAssignController extends Controller
{
    //

    public function assign_medicine(Request $request){
    Try{
        $validatedData = $request->validate([
            'userid' => 'required|integer|exists:users,id',
            'stock_id' => 'required|integer|exists:',
        ]);
    }
    catch(Exceptions $ex){
        Log::info($ex);
    }

    }
}
