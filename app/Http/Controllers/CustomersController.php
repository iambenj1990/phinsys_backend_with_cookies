<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\Customers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;



class CustomersController extends Controller
{
    //

    public function index()
    {
        try {

            $customers = Customers::orderBy('id', 'desc')
                ->get();
            return response()->json(['success' => true, 'customers' =>  $customers]);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
            //throw $th;
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'errors' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function CustomerByDate(Request $request)
    {
        try {


            $validated = $request->validate([
                'from' => 'required|date',
                'to'   => 'required|date|after_or_equal:from',
            ]);

            $customers = Customers::dateBetween($validated['from'], $validated['to'])
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json(['success' => true, 'customers' =>  $customers]);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
            //throw $th;
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'errors' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'errors' => $th->getMessage()
            ], 500);
        }
    }


    public function show($id)
    {

        try {
            $customers = Customers::where('id', $id)
                ->get();
            return response()->json($customers, 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
            //throw $th;
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'errors' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {


            $validationInput = $request->validate(
                [
                    'firstname' => 'required|string|max:255',
                    'lastname' => 'required|string|max:255',
                    'middlename' => 'nullable|string|max:255',
                    'ext' => 'nullable|string|max:255',
                    'birthdate' => 'required|date',
                    'contact_number' => 'nullable|string|max:11',
                    'age' => 'integer',
                    'gender' => 'required|string|max:11',
                    'is_not_tagum' => 'boolean',
                    'street' => 'nullable|string|max:255',
                    'purok'  => 'nullable|string|max:255',
                    'barangay' => 'required|string|max:255',
                    'city' => 'nullable|string|max:255',
                    'province' => 'nullable|string|max:255',
                    'category' => 'required|in:Child,Adult,Senior',
                    'is_pwd' => 'boolean',
                    'is_solo' => 'boolean',
                    'origin' => 'nullable|string|max:200',   // 👈 add this
                    'maifp_id' => 'nullable|string|max:200', // 👈 add this

                ]
            );
            $validationInput['user_id'] = Auth::id();

            // 🔹 Check if customer already exists
            $exists = Customers::where('firstname', $validationInput['firstname'])
                ->where('lastname', $validationInput['lastname'])
                ->where('birthdate', $validationInput['birthdate'])
                ->exists();

            if ($exists) {
                    // 🔹 Check if customer already exists
            $existing_client = Customers::where('firstname', $validationInput['firstname'])
                ->where('lastname', $validationInput['lastname'])
                ->where('birthdate', $validationInput['birthdate'])
                ->get();
                return response()->json([
                    'success' => false,
                    'message' => 'Customer already exists',
                    'existing_name' => $existing_client,
                ], 200); // 409 Conflict
            }

            Log::info('Incoming validated data:', $validationInput);
            $customers = Customers::create($validationInput);

            return response()->json([
                'success' => true,
                'customers' =>  $customers
            ]);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
            //throw $th;
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'errors' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function store_bulk(Request $request)
    {
        try {

            // Validate input as an array of customers
            $validated = $request->validate([
                'customers' => 'required|array',
                'customers.*.firstname' => 'required|string|max:255',
                'customers.*.lastname' => 'required|string|max:255',
                'customers.*.middlename' => 'nullable|string|max:255',
                'customers.*.ext' => 'nullable|string|max:255',
                'customers.*.birthdate' => 'required|date',
                'customers.*.contact_number' => 'nullable|string|max:11',
                'customers.*.age' => 'integer',
                'customers.*.gender' => 'required|string|max:11',
                'customers.*.is_not_tagum' => 'boolean',
                'customers.*.street' => 'nullable|string|max:255',
                'customers.*.purok'  => 'nullable|string|max:255',
                'customers.*.barangay' => 'required|string|max:255',
                'customers.*.city' => 'nullable|string|max:255',
                'customers.*.province' => 'nullable|string|max:255',
                'customers.*.category' => 'required|in:Child,Adult,Senior',
                'customers.*.is_pwd' => 'boolean',
                'customers.*.is_solo' => 'boolean',
                'customers.*.user_id' => 'required|exists:users,id'
            ]);

            $inserted = [];
            $skipped = [];

            foreach ($validated['customers'] as $customerData) {
                $customerData['user_id'] = Auth::id();
                // Example uniqueness check: firstname + lastname + birthdate
                $exists = Customers::where('firstname', $customerData['firstname'])
                    ->where('lastname', $customerData['lastname'])
                    ->where('birthdate', $customerData['birthdate'])
                    ->exists();

                if ($exists) {
                    $skipped[] = $customerData;
                    continue; // Skip if already exists
                }

                $inserted[] = Customers::create($customerData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Bulk insert completed',
                'inserted_count' => count($inserted),
                'skipped_count' => count($skipped),
                'inserted' => $inserted,
                'skipped' => $skipped,
            ]);
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
                'errors' => $qe->getMessage()
            ], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'errors' => $th->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $customer = Customers::where('id', $id)->first();
            if (!$customer) {
                return response()->json(['success' => false, 'message' => 'Client not found'], 404);
            }

            $validationInput = $request->validate(
                [
                    'firstname' => 'required|string|max:255',
                    'lastname' => 'required|string|max:255',
                    'middlename' => 'nullable|string|max:255',
                    'ext' => 'nullable|string|max:255',
                    'birthdate' => 'required|date',
                    'contact_number' => 'nullable|string|max:11',
                    'age' => 'integer',
                    'gender' => 'required|string|max:11',
                    'is_not_tagum' => 'boolean',
                    'street' => 'nullable|string|max:255',
                    'purok'  => 'nullable|string|max:255',
                    'barangay' => 'required|string|max:255',
                    'city' => 'nullable|string|max:255',
                    'province' => 'nullable|string|max:255',
                    'category' => 'required|in:Child,Adult,Senior',
                    'is_pwd' => 'boolean',
                    'is_solo' => 'boolean',
                    'user_id' => 'required|exists:users,id'
                ]
            );

            $customer->update($validationInput);
            return response()->json([
                'success' => true,
                'customers' =>  $customer
            ]);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
            //throw $th;
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'errors' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $customer = Customers::where('id', $id);
            if (!$customer) {
                return response()->json(['success' => false, 'message' => 'Client not found'], 404);
            }
            $customer->delete();
            return response()->json([
                'success' => true,
                'customers' =>  $customer,
                'message' => 'Customer information deleted'
            ], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
            //throw $th;
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'errors' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
