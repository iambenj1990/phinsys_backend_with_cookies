<?php

namespace App\Http\Controllers;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;


class DashboardController extends Controller
{
    //
    public function dashboard_registered_customers()
    {
        try {
            // Validate the request parameters
            $validatedData = request()->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            if (!$validatedData) {
                return response()->json(['error' => 'Invalid date range'], 400);
            }
            // Fetch the registered customers within the specified date range



            return $this->getRegisteredCustomers($validatedData['start_date'], $validatedData['end_date']);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation error', 'messages' => $e->errors()], 422);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Unexpected error', 'message' => $e->getMessage()], 500);
        }
    }

    private function getRegisteredCustomers($start_date, $end_date)
    {
        try {

            $customers = DB::table('tbl_customers')
                ->select(
                    DB::raw('COUNT(*) as registered_Customers')
                )
                ->whereBetween('created_at', [$start_date, $end_date])
                ->first();
            if (!$customers) {
                return response()->json(['message' => 'No customers found for the specified date range'], 404);
            }
            // Format the response
            return response()->json($customers);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Database query error', 'message' => $e->getMessage()], 500);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Unexpected error', 'message' => $e->getMessage()], 500);
        }
    }


    public function dashboard_served_customers()
    {
        try {
            // Validate the request parameters
            $validatedData = request()->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            if (!$validatedData) {
                return response()->json(['error' => 'Invalid date range'], 400);
            }

            // Fetch the served customers within the specified date range
            // return $this->getServedCustomers($validatedData['start_date'], $validatedData['end_date']);

            $servedCustomers = DB::table('vw_recipient_dispense')
                ->select(DB::raw('COUNT(DISTINCT transaction_id) as served_Customers'))
                ->whereBetween('transaction_date', [$validatedData['start_date'], $validatedData['end_date']])
                ->first();

            if (!$servedCustomers) {
                return response()->json(['message' => 'No served customers found for the specified date range'], 404);
            }

            return response()->json($servedCustomers);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation error', 'messages' => $e->errors()], 422);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Unexpected error', 'message' => $e->getMessage()], 500);
        }
    }

    public function dashboard_customers_genders()
    {
        try {
            // Validate the request parameters
            $validatedData = request()->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            if (!$validatedData) {
                return response()->json(['error' => 'Invalid date range'], 400);
            }

            // Fetch the served customers within the specified date range
            // return $this->getServedCustomers($validatedData['start_date'], $validatedData['end_date']);

            $servedCustomers = DB::table('vw_recipient_dispense')

                ->select('gender', DB::raw('COUNT(DISTINCT transaction_id) as count'))
                ->whereBetween('transaction_date', [$validatedData['start_date'], $validatedData['end_date']])
                ->groupBy('gender')
                ->get();

            if (!$servedCustomers) {
                return response()->json(['message' => 'No served customers found for the specified date range'], 404);
            }

            $totalServed = $servedCustomers->sum('count');

            return response()->json([
                'total' => $totalServed,
                'by_gender' => $servedCustomers
            ]);

            // return response()->json($servedCustomers);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation error', 'messages' => $e->errors()], 422);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Unexpected error', 'message' => $e->getMessage()], 500);
        }
    }

    public function dashboard_customers_ages()
    {

        try {
            // Validate the request parameters
            $validatedData = request()->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $CustomerAges = DB::table('vw_recipient_dispense')
                ->whereBetween('transaction_date', [$validatedData['start_date'], $validatedData['end_date']])
                ->selectRaw('
                COUNT(DISTINCT transaction_id) as total,
                COUNT(DISTINCT CASE WHEN age BETWEEN 0 AND 17 THEN transaction_id END) AS age_0_17,
                COUNT(DISTINCT CASE WHEN age BETWEEN 18 AND 59 THEN transaction_id END) AS age_18_59,
                COUNT(DISTINCT CASE WHEN age >= 60 THEN transaction_id END) AS age_60_above
            ')
                ->first();

            if (!$CustomerAges) {
                return response()->json(['message' => 'No served customers found for the specified date range'], 404);
            }

            return response()->json([
                'total' => $CustomerAges->total,
                'children' => $CustomerAges->age_0_17,
                'adults' => $CustomerAges->age_18_59,
                'seniors' => $CustomerAges->age_60_above,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }


    public function dashboard_customers_barangay()
    {
        try {
            //code...

            $validatedData = request()->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            if (!$validatedData) {
                return response()->json(['error' => 'Invalid date range'], 400);
            }

            $customersByBarangay = DB::table('vw_recipient_dispense')
                ->select('barangay', DB::raw('COUNT(DISTINCT transaction_id) as count'))
                ->whereBetween('transaction_date', [$validatedData['start_date'], $validatedData['end_date']])
                ->groupBy('barangay')
                ->get();

            if ($customersByBarangay->isEmpty()) {
                return response()->json(['message' => 'No served customers found for the specified date range'], 404);
            }

            return response()->json(['success' => true, 'barangay' => $customersByBarangay], 200);
        } catch (Throwable $th) {
            return response()->json(['error' => 'Server failed', 'message' => $th], 500);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }

    // --------------------------------------------------------------------------------------------------------------------------------------------------------


    // public function dashboard_medicines_incoming_expired()
    // {
    //     try {
    //         $today = now()->toDateString();
    //         $monthFromNow = now()->addDays(30)->toDateString();

    //         $expiringItems = DB::table('vw_dailyinventoryinfo')
    //             ->whereDate('expiration_date', '>=', $today)
    //             ->whereDate('expiration_date', '<=', $monthFromNow)
    //             ->where('Openning_quantity', '<>', 0)
    //             ->where('Closing_quantity', '<>', 0)
    //             ->where('status', '=', 'OPEN')
    //             ->count();

    //         $expiredItems = DB::table('vw_dailyinventoryinfo')
    //             ->whereDate('expiration_date', '<', $today)
    //             ->where('Openning_quantity', '<>', 0)
    //             ->where('Closing_quantity', '<>', 0)
    //             ->where('status', '=', 'OPEN')
    //             ->count();

    //         return response()->json([
    //             'expiring' => $expiringItems,
    //             'expired' => $expiredItems
    //         ], 200);
    //     } catch (ValidationException $ve) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation error',
    //             'errors' => $ve->errors()
    //         ], 422);
    //     } catch (QueryException $qe) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Database error',
    //             'error' => $qe->getMessage()
    //         ], 500);
    //     } catch (Throwable $th) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An unexpected error occurred',
    //             'error' => $th->getMessage()
    //         ], 500);
    //     }
    // }

    public function dashboard_medicines_expired()
    {
        try {
            $today = now()->toDateString();
            $monthFromNow = now()->addDays(30)->toDateString();

            $expiringItems = DB::table('vw_dailyinventoryinfo')
                ->whereDate('expiration_date', '>=', $today)
                ->whereDate('expiration_date', '<=', $monthFromNow)
                ->where('Openning_quantity', '<>', 0)
                ->where('Closing_quantity', '<>', 0)
                ->where('status', '=', 'OPEN')
                ->count();

            $expiredItems = DB::table('vw_dailyinventoryinfo')
                ->whereDate('expiration_date', '<', $today)
                ->where('Openning_quantity', '<>', 0)
                ->where('Closing_quantity', '<>', 0)
                ->where('status', '=', 'OPEN')
                ->count();

            return response()->json([
                'expiring' => $expiringItems,
                'expired' => $expiredItems
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
        } catch (Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function dashboard_medicines_instock()
    {
        try {
            $today = Carbon::today()->toDateString();
            $monthFromNow = Carbon::today()->addDays(30)->toDateString();

            $inStockCount = DB::table('vw_dailyinventoryinfo')
                ->whereDate('transaction_date', '=', $today)
                ->where('status', 'OPEN')
                ->where('Openning_quantity', '>', 0)
                ->where('Closing_quantity', '>', 0)
                ->whereDate('expiration_date', '>', $monthFromNow) // Exclude expired & near-expiry
                ->count();

            return response()->json([

                'instock' => $inStockCount
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
        } catch (Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function dashboard_medicines_outOfStock()
    {
        try {
            $today = now()->toDateString();
            $monthFromNow = now()->addDays(30)->toDateString();



            $countOutOfStockItems = DB::table('vw_dailyinventoryinfo')
                // ->whereDate('expiration_date', '>', $monthFromNow)
                ->where('status', 'OPEN')
                ->where(function ($query) {
                    $query->where('Openning_quantity', '<=', 0)
                        ->orWhere('Closing_quantity', '<=', 0);
                })

                ->count();

            return response()->json([
                'noStock' => $countOutOfStockItems
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
        } catch (Throwable $th) {
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

            // if (!is_numeric($threshold) || $threshold <= 0) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Invalid threshold value. Must be a positive number.'
            //     ], 422);
            // }

            // $latestInventoryQuery = DB::table('tbl_daily_inventory as inv1')
            //     ->select(
            //         'inv1.id',
            //         'inv1.stock_id',
            //         'inv1.Openning_quantity',
            //         'inv1.Closing_quantity',
            //         'inv1.quantity_out',
            //         'inv1.transaction_date',
            //         'inv1.remarks',
            //         'inv1.status',
            //         'inv1.user_id'
            //     )
            //     ->whereRaw('inv1.transaction_date = (
            //     SELECT MAX(inv2.transaction_date)
            //     FROM tbl_daily_inventory as inv2
            //     WHERE inv2.stock_id = inv1.stock_id
            // )')
            //     ->where(function ($query) use ($threshold) {     // Filter for low quantity stocks that are below the threshold
            //         $query->whereBetween('inv1.Openning_quantity', [1, $threshold])
            //             ->whereBetween('inv1.Closing_quantity', [1, $threshold]);
            //     });

            // // Join with tbl_items to get item info
            // $data = DB::table('tbl_items as i')
            //     ->joinSub($latestInventoryQuery, 'latest_inv', function ($join) {
            //         $join->on('i.id', '=', 'latest_inv.stock_id');
            //     })
            //     ->select(
            //         'latest_inv.id as inventory_id',
            //         'i.id as item_id',
            //         'i.po_no',
            //         'i.brand_name',
            //         'i.generic_name',
            //         'i.dosage',
            //         'i.dosage_form',
            //         'i.unit',
            //         'i.quantity as item_quantity',
            //         'latest_inv.Openning_quantity',
            //         'latest_inv.Closing_quantity',
            //         'i.expiration_date',
            //         'latest_inv.transaction_date as last_inventory_date',
            //         'latest_inv.status',
            //         'latest_inv.remarks'
            //     )
            //     ->get();



            // return response()->json([
            //     'success' => true,
            //     'stocks' => $data->count()
            // ], 200);

            $threshold = (float) $threshold;

            if ($threshold <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid threshold value. Must be a positive number.'
                ], 422);
            }

            // Get the latest inventory date per stock item 
            $latestDatePerStock = DB::table('tbl_daily_inventory')
                ->select('stock_id', DB::raw('MAX(transaction_date) as max_date'))
                ->where('status', 'OPEN')
                ->groupBy('stock_id');

            // Get the latest inventory row per stock using the above subquery
            $latestInventory = DB::table('tbl_daily_inventory as inv')
                ->joinSub($latestDatePerStock, 'latest', function ($join) {
                    $join->on('inv.stock_id', '=', 'latest.stock_id')
                        ->on('inv.transaction_date', '=', 'latest.max_date');
                })
                ->select(
                    'inv.id',
                    'inv.stock_id',
                    'inv.Openning_quantity',
                    'inv.Closing_quantity',
                    'inv.quantity_out',
                    'inv.transaction_date',
                    'inv.remarks',
                    'inv.status',
                    'inv.user_id'
                )
                ->where(function ($query) use ($threshold) {
                    // Either opening OR closing quantity is low (but not zero/out-of-stock)
                    $query->whereBetween('inv.Openning_quantity', [1, $threshold])
                        ->orWhereBetween('inv.Closing_quantity', [1, $threshold]);
                });

            // Join with items table
            $data = DB::table('tbl_items as i')
                ->joinSub($latestInventory, 'latest_inv', function ($join) {
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
                ->orderBy('i.generic_name')
                ->get();

            return response()->json([
                'success' => true,
                'threshold' => $threshold,
                // 'count' => $data->count(),
                'stocks' => $data ->count()              // return actual data, not just count
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
        } catch (Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function dashboard_medicines_countTemp()
    {
        try {
            // $today = now()->toDateString();
            // $monthFromNow = now()->addDays(30)->toDateString();


            $countTempPOno = DB::table('vw_dailyinventoryinfo')
                // ->where('status', 'OPEN')
                // ->whereDate('transaction_date',  $today)
                ->where('po_no', 'like', 'TEMP-%')
                ->distinct()
                ->count('po_no');

            return response()->json([
                'count' => $countTempPOno
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
        } catch (Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function dashboard_medicines_TopTen()
    {
        try {
            $topQuantities = DB::table('vw_dailyinventoryinfo')
                ->select('stock_id', 'brand_name', 'generic_name', DB::raw('SUM(quantity_out) as total_quantity_out'))
                ->where('status', 'CLOSE')
                ->groupBy('stock_id')
                ->orderByDesc('total_quantity_out')
                ->limit(10)
                ->get();

            return response()->json([
                'top_ten_medicines' => $topQuantities
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
        } catch (Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function Dash_low_Quantity()
    {
        try {
            $low_stocks = DB::table('tbl_items as i')
                ->join('tbl_daily_inventory as dt', 'i.id', '=', 'dt.stock_id')
                ->select([
                    'i.id',
                    'i.po_no',
                    'i.brand_name',
                    'i.generic_name',
                    'i.dosage',
                    'i.dosage_form',
                    'i.expiration_date',
                    'i.quantity',
                    'dt.Openning_quantity',
                    'dt.Closing_quantity',
                    'dt.transaction_date'
                ])
                ->whereDate('dt.transaction_date', '=', Carbon::today())  // Only today's snapshot
                ->where(function ($query) {
                    $query->where('dt.Openning_quantity', '<=', 0)
                        ->orWhere('dt.Closing_quantity', '<=', 0);
                })
                ->orderBy('i.generic_name')
                ->get();

            // De-duplicate by item ID at the collection level (safe fallback)
            $low_stocks = $low_stocks->unique('id')->values();

            return response()->json([
                'success' => true,
                'result' => $low_stocks,
                'count' => $low_stocks->count()
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
        } catch (Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
