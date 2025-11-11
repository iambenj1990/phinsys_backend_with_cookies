<?php

namespace App\Http\Controllers;
use App\Models\Unit;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UnitController extends Controller
{


    public function show($id)
    {
        try {
            $unit = Unit::findorFail($id);
            if (!$unit) {
                return response()->json(['success'=>false,'message' => 'Unit not found'], 404);
            }
            return response()->json(['success'=>true,'unit'=>$unit],200);
        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message' => 'Failed to fetch unit', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'description' => 'required|string',
                'symbol' => 'required|string',
            ]);

            $unit = Unit::create($validatedData);
            return response()->json(['success'=>true,'unit'=>$unit, 'message'=> 'Unit entry successful'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create unit', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $unit = Unit::find($id);
            if (!$unit) {
                return response()->json(['message' => 'Unit not found'], 404);
            }

            $validatedData = $request->validate([
            'description' => 'required|string',
            'symbol' => 'required|string',
            ]);

            $unit->update($validatedData);
            return response()->json(['success'=>true, 'unit'=>$unit, 'message' => 'Unit updated successfully'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update unit', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request)
    {
        $validationInput = $request->validate(
           [
               'id' => 'required|numeric',
           ]
       );
        try {
            $unit = Unit::find($validationInput['id']);
            if (!$unit) {
                return response()->json(['message' => 'Unit not found'], 404);
            }

            $unit->delete();
            return response()->json(['message' => 'Unit deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete unit', 'error' => $e->getMessage()], 500);
        }
    }

    public function getUnits()
    {
        try {
            //code...
            $unit = Unit::orderby('id', 'asc')->get();


            if ($unit->isEmpty()) {
                return response()->json(['success'=>false,'message' => 'No units found'], 404);
            }
            return response()->json(['success'=>true,'units'=>$unit], 200);
        } catch (\Throwable $th) {
             return response()->json(['success'=>false, 'message' => 'Failed to delete unit', 'error' => $th->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['success'=>false, 'message' => 'Failed to delete unit', 'error' => $e->getMessage()], 500);
        }


    }

}
