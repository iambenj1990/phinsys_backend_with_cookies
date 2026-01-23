<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use App\Models\Daily_inventory as Inventory;
use Illuminate\Validation\ValidationException;

class DailyInventoryController extends Controller
{
    //
    public function index()
    {
        try {

            $Inventory = Inventory::orderBy('transaction_date', 'desc')
                ->get();
            return response()->json(['success' => true, 'transactions' =>  $Inventory]);
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
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
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

            $Inventory = Inventory::where('id', $id)
                ->get();
            return response()->json(['success' => true, 'transaction' =>  $Inventory]);
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
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function showLatest()
    {
        try {
            $latestInventoryQuery = DB::table('tbl_daily_inventory as inv1')
                ->select('inv1.id', 'inv1.stock_id', 'inv1.Openning_quantity', 'inv1.Closing_quantity', 'inv1.quantity_out', 'inv1.transaction_date', 'inv1.remarks', 'inv1.status', 'inv1.user_id')
                ->whereRaw('inv1.transaction_date = (
                SELECT MAX(inv2.transaction_date)
                FROM tbl_daily_inventory as inv2
                WHERE inv2.stock_id = inv1.stock_id
            )');

            $data = $latestInventoryQuery->get();
            return response()->json($data);
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
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function showTodayInventory($transaction_date)
    {

        try {
            $Inventory = Inventory::where('transaction_date', $transaction_date)
                ->get();
            return response()->json(['success' => true, 'transaction' =>  $Inventory]);
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
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
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

            $validationInput = $request->validate(
                [
                    'stock_id' => 'required|exists:tbl_items,id',
                    'Openning_quantity' => 'required|numeric|min:1',
                    'Closing_quantity' => 'nullable|numeric',
                    'quantity_out' => 'nullable|numeric',
                    'transaction_date' => 'required|date',
                    'remarks'   => 'nullable|string|max:250',
                    'status'   => 'nullable|string|max:250',
                    'user_id' => 'required|exists:users,id',
                ]
            );

            $System_users = Inventory::create($validationInput);
            return response()->json([
                'success' => true,
                'dailyInventory' =>  $System_users
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
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // public function update_openningQuantity(Request $request, $id){

    // }

    public function update(Request $request, $id)
    {
        try {
            $Inventory = Inventory::where('id', $id);
            if (!$Inventory) {
                return response()->json(['success' => false, 'message' => 'transaction not found'], 404);
            }

            $validationInput = $request->validate(
                [
                    'stock_id' => 'required|exists:tbl_items,id',
                    'Openning_quantity' => 'required|numeric|min:1',
                    'Closing_quantity' => 'required|numeric|min:1',
                    'quantity_out' => 'required|numeric',
                    'transaction_date' => 'required|date',
                    'user_id' => 'required|exists:users,id',
                    'remarks'   => 'nullable|string|max:250',
                    'status'   => 'nullable|string|max:250',
                ]
            );

            $Inventory->update($validationInput);
            return response()->json([
                'success' => true,
                'transaction' =>  $Inventory
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
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
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
            Inventory::where('id', $id)->delete();
            // if (!$Inventory) {
            //     return response()->json(['success' => false, 'message' => 'transaction not found'], 404);
            // }

            return response()->json([
                'success' => true,
                'message' => 'record deleted successful'
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
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getEmptyQuantityStocks()
    {
        try {

            $latestInventoryQuery = DB::table('tbl_daily_inventory as inv1')
                ->select(
                    'inv1.id',
                    'inv1.stock_id',
                    'inv1.Openning_quantity',
                    'inv1.Closing_quantity',
                    'inv1.quantity_out',
                    'inv1.transaction_date',
                    'inv1.remarks',
                    'inv1.status',
                    'inv1.user_id'
                )
                ->whereRaw('inv1.transaction_date = (
                SELECT MAX(inv2.transaction_date)
                FROM tbl_daily_inventory as inv2
                WHERE inv2.stock_id = inv1.stock_id
            )')
                ->where(function ($query) {
                    $query->where('inv1.Openning_quantity', '=', 0)
                        ->orWhere('inv1.Closing_quantity', '=', 0);
                });

            // Join with tbl_items to get item info
            $data = DB::table('tbl_items as i')
                ->joinSub($latestInventoryQuery, 'latest_inv', function ($join) {
                    $join->on('i.id', '=', 'latest_inv.stock_id');
                })
                ->select(
                    'latest_inv.id as inventory_id',
                    'i.id as item_id',
                    'i.po_no',
                    'i.brand_name',
                    'i.generic_name',
                    'i.dosage',
                    'i.dosage_form',
                    'i.unit',
                    'i.quantity as item_quantity',
                    'latest_inv.Openning_quantity',
                    'latest_inv.Closing_quantity',
                    'i.expiration_date',
                    'latest_inv.transaction_date as last_inventory_date',
                    'latest_inv.status',
                    'latest_inv.remarks'
                )
                ->get();



            return response()->json([
                'success' => true,
                'stocks' => $data
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
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getLowQuantityStocks($threshold)
    {
        try {

            if (!is_numeric($threshold) || $threshold <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid threshold value. Must be a positive number.'
                ], 422);
            }

            $latestInventoryQuery = DB::table('tbl_daily_inventory as inv1')
                ->select(
                    'inv1.id',
                    'inv1.stock_id',
                    'inv1.Openning_quantity',
                    'inv1.Closing_quantity',
                    'inv1.quantity_out',
                    'inv1.transaction_date',
                    'inv1.remarks',
                    'inv1.status',
                    'inv1.user_id'
                )
                ->whereRaw('inv1.transaction_date = (
                SELECT MAX(inv2.transaction_date)
                FROM tbl_daily_inventory as inv2
                WHERE inv2.stock_id = inv1.stock_id
            )')
                ->where(function ($query) use ($threshold) {     // Filter for low quantity stocks that are below the threshold
                    $query->whereBetween('inv1.Openning_quantity', [1, $threshold])
                        ->whereBetween('inv1.Closing_quantity', [1, $threshold]);
                });

            // Join with tbl_items to get item info
            $data = DB::table('tbl_items as i')
                ->joinSub($latestInventoryQuery, 'latest_inv', function ($join) {
                    $join->on('i.id', '=', 'latest_inv.stock_id');
                })
                ->select(
                    'latest_inv.id as inventory_id',
                    'i.id as item_id',
                    'i.po_no',
                    'i.brand_name',
                    'i.generic_name',
                    'i.dosage',
                    'i.dosage_form',
                    'i.unit',
                    'i.quantity as item_quantity',
                    'latest_inv.Openning_quantity',
                    'latest_inv.Closing_quantity',
                    'i.expiration_date',
                    'latest_inv.transaction_date as last_inventory_date',
                    'latest_inv.status',
                    'latest_inv.remarks'
                )
                ->get();



            return response()->json([
                'success' => true,
                'stocks' => $data
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
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }




    public function QuantityOutOfStocks()
    {
        try {
            $lowStocks =  Inventory::where('Openning_quantity', '=', 0)
                ->orWhere('Closing_quantity', '=', 0)
                ->get();

            return response()->json([
                'success' => true,
                'stocks' => $lowStocks
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
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function regenerateInventory($userID)
    {
        try {

            // Check if there is already an OPEN status
            $hasOpenStatus = DB::table('tbl_daily_inventory')
                ->where('status', 'OPEN')
                ->exists();

            // If OPEN status exists, abort the operation
            if ($hasOpenStatus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Regeneration aborted: Some stock entries are still open. Please verify if previous days stocks have been closed.',
                ], 409); // HTTP 409 Conflict
            }

            // Check if there is a CLOSE status for the current date
            $currentDate = Carbon::today()->toDateString();
            $hasClosingStatusToday = DB::table('tbl_daily_inventory')
                ->where('status', 'CLOSE')
                ->whereDate('transaction_date', $currentDate)
                ->exists();

            // If CLOSE status exists for today, abort the operation
            if ($hasClosingStatusToday) {
                return response()->json([
                    'success' => false,
                    'message' => 'Regeneration aborted:Stocks already closed for current transaction date. Please verify the inventory records.',
                ], 409); // HTTP 409 Conflict
            }


            $subQuery = DB::table('tbl_daily_inventory')
                ->select('stock_id', DB::raw('MAX(transaction_date) as max_date'))
                ->where('status', 'CLOSE')
                ->groupBy('stock_id')
                ->distinct();

            $latestInventoryQuery = DB::table('tbl_daily_inventory as inv1')
                ->joinSub($subQuery, 'latest', function ($join) {
                    $join->on('inv1.stock_id', '=', 'latest.stock_id')
                        ->on('inv1.transaction_date', '=', 'latest.max_date');
                })
                ->where('inv1.status', 'CLOSE')
                ->select('inv1.id', 'inv1.stock_id', 'inv1.Closing_quantity', 'inv1.transaction_date');

            $latestData = $latestInventoryQuery->get();

            $user_ID = $userID;

            $insertData = $latestData->map(function ($item) use ($user_ID) {
                return [
                    'stock_id' => $item->stock_id,
                    'Openning_quantity' => $item->Closing_quantity,
                    'Closing_quantity' => $item->Closing_quantity,
                    'quantity_out' => 0,
                    'transaction_date' => Carbon::today(),
                    'remarks' => 'Auto-generated from previous closing',
                    'status' => 'OPEN',
                    'user_id' => $user_ID,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            })->toArray();

            DB::table('tbl_daily_inventory')->insert($insertData);

                AuditTrail::create([
                'action' => 'Regenerated Inventory',
                'table_name' => 'tbl_daily_inventory',
                'user_id' => $user_ID,
                'changes' => 'Regenerated inventory by user ID: ' . $user_ID
            ]);

            return response()->json([
                'success' => true,
                'message' => 'New inventory records generated successfully',
                'count' => count($insertData),
            ], 201);


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

    public function closeInventory($id)
    {
        try {
            $user_ID = $id; // Assuming the user is authenticate
            $subQuery = DB::table('tbl_daily_inventory')
                ->select('stock_id', DB::raw('MAX(transaction_date) as max_date'))
                ->where('status', 'OPEN')          // Only consider open status in subquery
                ->groupBy('stock_id');

            $latestInventoryQuery = DB::table('tbl_daily_inventory as inv1')
                ->joinSub($subQuery, 'latest', function ($join) {
                    $join->on('inv1.stock_id', '=', 'latest.stock_id')
                        ->on('inv1.transaction_date', '=', 'latest.max_date');
                })
                ->where('inv1.status', 'OPEN')     // Filter main query for OPEN status as well
                ->select('inv1.id', 'inv1.stock_id', 'inv1.Closing_quantity', 'inv1.transaction_date', 'inv1.status');


            $latestData = $latestInventoryQuery->get();

            // Validate if there are records to update
            if ($latestData->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No latest inventory records found to close.',
                ], 404);
            }

            // Prepare data for update
            $updateData = $latestData->map(function ($item) {
                return [
                    'id' => $item->id,
                    'status' => 'CLOSE',
                    'updated_at' => now(), // Update the timestamp
                ];
            })->toArray();

            // Perform the bulk update using a transaction for atomicity
            DB::transaction(function () use ($updateData) {
                foreach ($updateData as $data) {
                    DB::table('tbl_daily_inventory')
                        ->where('id', $data['id'])
                        ->update([
                            'status' => $data['status'],
                            'updated_at' => $data['updated_at'],
                        ]);
                }
            });

            AuditTrail::create([
                'action' => 'Closed Inventory',
                'table_name' => 'tbl_daily_inventory',
                'user_id' => $user_ID, // Assuming the user is authenticated
                'changes' => 'Closed inventory records by user ID: ' . $user_ID
            ]);


            return response()->json([
                'success' => true,
                'message' => 'Latest inventory records closed successfully.',
                'count' => count($updateData),
            ], 200);
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

    public function closeInventoryByDate($Date)
    {
        // Optional: Validate the dates format (YYYY-MM-DD)
        $validator = Validator::make(
            // ['stock_status' => $status, 'Date' => $Date],
            // ['stock_status' => 'required|string', 'Date' => 'required|date']
            ['Date' => $Date],
            ['Date' => 'required|date']
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        return $this->getCloseInventory($Date);
    }

    public function getCloseInventory($Date)
    {
        try {

            $inventoryCloseList = DB::table('vw_inventory_close_list')
                ->whereDate('transaction_date', $Date)
                // ->where('stock_status', $status)
                ->get();
            return response()->json([
                'success' => true,
                'list' => $inventoryCloseList
            ], 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Database query error', 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }


    public function testQuery()
    {
        try {
            //code...

            // $subQuery = DB::table('tbl_daily_inventory')
            //     ->select('stock_id', DB::raw('MAX(transaction_date) as max_date'))
            //     ->where('status', 'CLOSE')          // Only consider close status in subquery
            //     ->groupBy('stock_id');

            // $latestInventoryQuery = DB::table('tbl_daily_inventory as inv1')
            //     ->joinSub($subQuery, 'latest', function ($join) {
            //         $join->on('inv1.stock_id', '=', 'latest.stock_id')
            //             ->on('inv1.transaction_date', '=', 'latest.max_date');
            //     })
            //     ->where('inv1.status', 'CLOSE')     // Filter main query for OPEN status as well
            //     ->select('inv1.id', 'inv1.stock_id', 'inv1.Closing_quantity', 'inv1.transaction_date', 'inv1.status');

            // $latestData = $latestInventoryQuery->get();
            $AuthenticatedUser = Auth::user();
            $AuthenticatedUserCredentials = Auth::user()->credentials;

            if (!$AuthenticatedUser) {
                return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
            }

            return response()->json(["Authenticated User"=>$AuthenticatedUser], 200);
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

    public function OpenTransactionLookUp()
    {
        try {
            //code...
            $today = Carbon::today()->toDateString();

            $openInventories = DB::table('tbl_daily_inventory')
                ->where('status', 'OPEN')
                ->whereDate('transaction_date', $today)
                ->get();

            if ($openInventories->isEmpty()) {
                return response()->json(['status' => true, 'message' => 'Stocks closed. Open today’s stocks to proceed.' . $today], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'Some stock entries are still open. Please verify if previous days stocks have been closed.'], 200);
            }
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
