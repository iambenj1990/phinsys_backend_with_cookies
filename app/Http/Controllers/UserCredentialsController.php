<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\UserCredentials;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Validated;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserCredentialsController extends Controller
{
    //


    public function index()
    {
        try {
            //code...
            $credentials = UserCredentials::with('user')->get();
            if ($credentials->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No user credentials found.'], 404);
            }
            return response()->json(['success' => true, 'credential' => $credentials], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            // 🔍 Extract all input data
            $credentials = $request->all();

            // ❗ Ensure data is an array (for bulk insert/update)
            if (!is_array($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payload format. Expected array.'
                ], 400);
            }

            $saved = [];

            // 🌀 Loop through each credential payload
            foreach ($credentials as $item) {
                // 🧪 Validate each item
                $data = validator($item, [
                    'userid' => 'required|exists:users,id',
                    'module' => 'required|string',
                    'view' => 'nullable|boolean',
                    'add' => 'nullable|boolean',
                    'edit' => 'nullable|boolean',
                    'delete' => 'nullable|boolean',
                    'export' => 'nullable|boolean',
                ])->validate();

                // 📝 Create or update credentials
                $saved[] = UserCredentials::updateOrCreate(
                    ['userid' => $data['userid'], 'module' => $data['module']], // Unique key
                    [ // Fields to update
                        'view' => $data['view'] ?? false,
                        'add' => $data['add'] ?? false,
                        'edit' => $data['edit'] ?? false,
                        'delete' => $data['delete'] ?? false,
                        'export' => $data['export'] ?? false,
                    ]
                );
            }

            // ✅ Return success response
            return response()->json([
                'success' => true,
                'credential' => $saved,
                'message' => 'User credential(s) created or updated successfully.'
            ], 201);
        } catch (ValidationException $ve) {
            // 🛑 Validation error
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
        } catch (QueryException $qe) {
            // ⚠️ DB-level error
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
        } catch (\Throwable $th) {
            // 💥 Catch-all for unexpected issues
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function show($id)
    {
        try {
            $credentials = UserCredentials::findOrFail($id);
            if (!$credentials) {
                return response()->json(['success' => false, 'message' => 'User credential not found.'], 404);
            }
            return response()->json(['success' => true, 'credential' => $credentials,], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function showByUserId($userId)
    {
        try {
            $credentials = UserCredentials::where('userid', $userId)->get();
            if ($credentials->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No user credentials found for this user.'], 404);
            }
            return response()->json(['success' => true, 'credential' => $credentials], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function showModuleCredentialbyUser($userId, $module)
    {
        try {
            $credentials = UserCredentials::where('userid', $userId)
                ->where('module', $module)
                ->get();
            if ($credentials->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No user credentials found for this user.'], 404);
            }
            return response()->json(['success' => true, 'credential' => $credentials], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function GetMyModule(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'module' => 'required|string',
        ]);

        // Handle validation failure manually
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = Auth::user()->id;
        $module = $validator->validated()['module'];

        try {
            $credentials = UserCredentials::where('userid', $userId)
                ->where('module', $module)
                ->get();
            if ($credentials->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No user credentials found for this user.'], 404);
            }
            return response()->json(['success' => true, 'credential' => $credentials], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $credential = UserCredentials::findOrFail($id);

            if (!$credential) {
                return response()->json(['success' => false, 'message' => 'User credential not found.'], 404);
            }

            $data = $request->validate([
                'view' => 'boolean',
                'add' => 'boolean',
                'edit' => 'boolean',
                'delete' => 'boolean',
                'export' => 'boolean',
            ]);

            $credential->update($data);
            
            return response()->json(['success' => true, 'credential' => $credential, 'message' => 'User credential updated successfully.'], 201);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $credential = UserCredentials::findOrFail($id);

            if (!$credential) {
                return response()->json(['success' => false, 'message' => 'User credential not found.'], 404);
            }

            $credential->delete();
            return response()->json(['success' => true, 'credential' => $credential, 'message' => 'User credential deleted successfully.'], 201);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
