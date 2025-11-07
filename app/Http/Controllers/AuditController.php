<?php

namespace App\Http\Controllers;



use App\Models\AuditTrail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;


class AuditController extends Controller
{
    //
    // Get all audit logs
    public function index()
    {
        try{
            $logs = AuditTrail::latest()->get();

           foreach($logs as $log){
            $user=User::find($log->user_id);
            $log->username =$user ? $user->username:'unknown user';
           }

        return response()->json(['success' => true, 'logs'=>$logs], 200);
        } catch (\Throwable $th) {
         return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
        }

    }

    // Store a new audit log
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'action' => 'required|string',
                'table_name' => 'required|string',
                'user_id' => 'required|exists:users,id',
                'changes' => 'required|string',
            ]);

            $validated['user_id'] = Auth::id();

            $log = AuditTrail::create($validated);

            return response()->json([
                'message' => 'Audit log created successfully.',
                'data' => $log
            ], 201);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
        } catch (QueryException $qe) {
            return response()->json(['success' => false, 'message' => 'Database error: ' . $qe->getMessage()], 500);
        } catch (ValidationException $ve) {
            return response()->json(['success' => false, 'message' => 'Validation error: ' . $ve->errors()], 422);
        }
    }

    // Show a specific audit log
    public function show(Request $request)
    {

        try {
            $validation = $request->validate(['id' => 'required|integer|exists:audit_trails,id']);
            $log = AuditTrail::findOrFail($validation['id']);
            return response()->json(['success' => true, 'log' => $log], 200);
        } catch (ValidationException $ve) {
            return response()->json(['success' => false, 'message' => 'Validation error: ' . $ve->errors()], 422);
        } catch (QueryException $qe) {
            return response()->json(['success' => false, 'message' => 'Database error: ' . $qe->getMessage()], 500);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
        }
    }

    public function showAllLogs(Request $request)
    {

        try {
            $validator = $request->validate([ 'user_id' => 'required|integer|exists:users,id']);

            $logs = AuditTrail::where('user_id', $validator['user_id'])
            ->latest()
            ->get();

            return response()->json(['success' => true, 'logs' => $logs], 200);
        } catch (ValidationException $ve) {
            return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $ve->errors()
            ], 422);
        } catch (QueryException $qe) {
            return response()->json([
            'success' => false,
            'message' => 'Database error: ' . $qe->getMessage()
            ], 500);
        } catch (\Throwable $th) {
            return response()->json([
            'success' => false,
            'message' => $th->getMessage()
            ], 500);
        }
    }


}
