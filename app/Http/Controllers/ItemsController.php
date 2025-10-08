<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Items;
use App\Models\AuditTrail;
use GrahamCampbell\ResultType\Success;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;


class ItemsController extends Controller
{
    public function get_name(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|numeric',
            ]);

            $item = Items::where('id', $validated['id'])->first();

            return response()->json([
                'success' => true,
                'item_name' => $item->generic_name,
                'amount' => $item->price_per_pcs,
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

    public function itemList()
    {
        try {
            $items = DB::table('vw_item_info')->get();
            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items found'
                ], 404);
            }
            return response()->json([
                'success' => true,
                'items' => $items
            ]);
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
    public function TemporaryID()
    {
        // $dateNow = now()->format('Ymd');  // Get date as YYYYMMDD
        // $string_id = (string) Str::uuid();
        // $temporary_id = 'TEMP' .'-'. $dateNow .'-'. $string_id ;
        // return response()->json($temporary_id);

        $dateNow = now()->format('Ymd'); // Current date as YYYYMMDD

        // Find the latest PO number for today with 'TEMP' prefix
        $latestItem = Items::where('po_no', 'like', "TEMP-$dateNow-%")
            ->orderByDesc('po_no')
            ->first();

        if ($latestItem) {
            // Extract the last incremental number and increment it
            $lastNumber = (int) substr($latestItem->po_no, -6);
            $lastNumber += 1;
            $newNumber = $lastNumber;
        } else {
            $newNumber = 1;
        }

        $temporary_id = 'TEMP-' . $dateNow . '-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

        return response()->json($temporary_id);
    }


    //
    public function index(Request $request)
    {
        try {

            $validated = $request->validate([
                'from' => 'required|date',
                'to' => 'required|date|after_or_equal:from',
            ]);


            $Items = Items::dateBetween($validated['from'], $validated['to'])
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json(
                [
                    'success' => true,
                    'items' => $Items
                ],
                200
            );
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

            $item = Items::where('id', $id)->get();
            if (!$item) {
                return response()->json(['success' => false, 'message' => 'Item not found'], 404);
            }
            return response()->json(['success' => true, 'items' => $item]);
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

    public function TempPOlist()
    {
        try {

            $po_list = Items::where('po_no', 'like', 'TEMP-%')
                ->select('po_no', DB::raw('MAX(created_at) as created_at'))
                ->groupBy('po_no')
                ->orderBy('created_at', 'desc')
                ->get();

            if (!$po_list) {
                return response()->json(['success' => false, 'message' => 'No temporary P.O. created',], 404);
            }

            return response()->json([
                'success' => true,
                'count' => $po_list->count(),
                'list' => $po_list
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

    public function UpdateTempPO($temp_po, Request $request)
    {
        try {

            $request->validate([
                'po_no' => 'required|string|unique:tbl_items,po_no'
            ]);

            // Find matching TEMP PO items
            $items = Items::where('po_no', $temp_po)->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items found with the specified PO number.'
                ], 404);
            }

            // Update each item with the new PO number
            foreach ($items as $item) {
                $item->po_no = $request->po_no;
                $item->save();
            }
            
            AuditTrail::create([
                'action' => 'Updated',
                'table_name' => 'items',
                'user_id' => $request->user_id,
                'changes' => 'Updated PO number: ' . $temp_po . ' to ' . $request->po_no
            ]);
            return response()->json([
                'success' => true,
                'message' => 'PO number updated successfully.'
            ], 201);


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

    public function showItemsByPO($po_number)
    {

        try {

            $items = Items::where('po_no', $po_number)->get();
            if (!$items) {
                return response()->json(['success' => false, 'message' => 'Items not found'], 404);
            }
            return response()->json(['success' => true, 'items' => $items]);
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


    public function batchstore(Request $request)
    {
        DB::beginTransaction();
        try {

            foreach ($request->input('medicines', []) as $index => $medicine) {
                if (isset($medicine['dosage']) && !is_string($medicine['dosage']) && !is_null($medicine['dosage'])) {
                    logger("Invalid dosage at index {$index}: " . json_encode($medicine['dosage']));
                }
            }

            $validated = $request->validate([
                'medicines' => 'required|array',
                'medicines.*.po_no' => 'nullable|string',
                'medicines.*.brand_name' => 'nullable|string',
                'medicines.*.generic_name' => 'nullable|string',
                'medicines.*.dosage_form' => 'nullable|string',
                'medicines.*.dosage' => 'nullable|string',
                'medicines.*.category' => 'nullable|string',
                'medicines.*.unit' => 'nullable|string',
                'medicines.*.price' => 'nullable|numeric',
                'medicines.*.price_per_pcs' => 'nullable|numeric',
                'medicines.*.quantity' => 'nullable|integer',
                'medicines.*.box_quantity' => 'nullable|integer',
                'medicines.*.quantity_per_box' => 'nullable|integer',
                'medicines.*.expiration_date' => 'nullable|date',
                'medicines.*.user_id' => 'required|integer',
            ]);

            $inserted = [];


            foreach ($validated['medicines'] as $medicine) {
                $inserted[] = Items::create($medicine);
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => count($inserted) . ' Items added successfully',
                'items' => $inserted
                // 'skipped' => $skipped
            ]);
        } catch (ValidationException $ve) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors()
            ], 422);
            //throw $th;
        } catch (QueryException $qe) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
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
                    'po_no' => 'required|string|max:50',
                    'brand_name' => 'required|string|max:100',
                    'generic_name' => 'required|string|max:100',
                    'dosage_form' => 'nullable|string|max:50',
                    'dosage' => 'required|string|max:50',
                    'category' => 'nullable|string|max:50',
                    'unit' => 'required|string|max:50',
                    'quantity' => 'required|numeric|min:1',
                    'box_quantity' => 'nullable|numeric',
                    'quantity_per_box' => 'nullable|numeric',
                    'price' => 'nullable|numeric',
                    'price_per_pcs' => 'nullable|numeric',
                    'expiration_date' => 'required|date|after:today',
                    'user_id' => 'required|exists:users,id',
                ]
            );

            $Items = Items::create($validationInput);
            return response()->json([
                'success' => true,
                'item' => $Items,
                'message' => 'Item registration Successful'
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

    public function update(Request $request, $id)
    {
        try {
            $item = Items::where('id', $id)->first();
            if (!$item) {
                return response()->json(['success' => false, 'message' => 'item not found'], 404);
            }

            $validationInput = $request->validate(
                [
                    'po_no' => 'required|string|max:50',
                    'brand_name' => 'required|string|max:100',
                    'generic_name' => 'required|string|max:100',
                    'dosage_form' => 'nullable|string|max:50',
                    'dosage' => 'required|string|max:50',
                    'category' => 'nullable|string|max:50',
                    'unit' => 'required|string|max:50',
                    'price' => 'nullable|numeric',
                    'quantity' => 'required|numeric|min:1',
                    'box_quantity' => 'nullable|numeric',
                    'quantity_per_box' => 'nullable|numeric',
                    'price_per_pcs' => 'nullable|numeric',
                    'expiration_date' => 'required|date|after:today',
                    'user_id' => 'required|exists:users,id',
                ]
            );

            $item->update($validationInput);

            return response()->json([
                'success' => true,
                'item' => $item,
                'message' => 'Item updating Successful'
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


    public function destroyItemsByPO($po_number)
    {
        try {
            $items = Items::where('po_no', $po_number)
                ->get();
            if ($items->isEmpty()) //Used isEmpty() to check if the collection is empty.
            {
                return response()->json(['success' => false, 'message' => 'items not found'], 404);
            }
            // $item->delete();
            // Items::where('po_no',$po_number)->delete();
            $items->each->delete();  //Removed the redundant query by using $items->each->delete() to delete the items directly

            return response()->json([
                'success' => true,
                'message' => "Items under PO-number $po_number have been removed."
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

    public function destroy($id)
    {
        try {
            Items::where('id', $id)->delete();
            return response()->json([
                'success' => true,
                'message' => 'item deleted successfully'
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

    public function getExpiringStock()
    {
        try {
            $today = now()->toDateString();
            $monthFromNow = now()->addDays(30)->toDateString();



            $expiredItems = DB::table('tbl_items')
                ->select([
                    'po_no',
                    'brand_name',
                    'generic_name',
                    'dosage',
                    'dosage_form',
                    'category',
                    'expiration_date'
                ])
                // ->whereDate('expiration_date', '>=', $today)
                ->whereDate('expiration_date', '<=', $monthFromNow)
                ->orderBy('expiration_date', 'asc')
                ->get();



            return response()->json([
                'message' => 'success',
                'items' => $expiredItems,
                'month' => $monthFromNow,
                'count' => $expiredItems->count(),
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

    public function getJoinedItemswitInventory()
    {

        $latestInventoryQuery = DB::table('tbl_daily_inventory as inv1')
            ->select('inv1.id', 'inv1.stock_id', 'inv1.Closing_quantity', 'inv1.Openning_quantity', 'inv1.transaction_date')
            ->whereRaw('inv1.transaction_date = (
            SELECT MAX(inv2.transaction_date)
            FROM tbl_daily_inventory as inv2
            WHERE inv2.stock_id = inv1.stock_id
        )')
            ->where('inv1.status', 'OPEN'); // Filter by OPEN status;

        $data = DB::table('tbl_items')
            ->leftJoinSub($latestInventoryQuery, 'latest_inventory', function ($join) {
                $join->on('tbl_items.id', '=', 'latest_inventory.stock_id');
            })
            ->select(
                'latest_inventory.id as inventory_id',
                'tbl_items.id as item_id',
                'tbl_items.po_no',
                'tbl_items.brand_name',
                'tbl_items.generic_name',
                'tbl_items.dosage',
                'tbl_items.dosage_form',
                'tbl_items.unit',
                'tbl_items.quantity as item_quantity',
                'latest_inventory.Openning_quantity',
                'latest_inventory.Closing_quantity',
                'tbl_items.expiration_date',
                'latest_inventory.transaction_date as last_inventory_date',

            )
            ->orderBy('tbl_items.brand_name')
            ->orderBy('tbl_items.expiration_date', 'asc')
            ->get();
        return $data;
    }

    public function getJoinedItemswitInventoryfiltered()
    {

        $today = Carbon::now()->toDateString(); // Get today's date
        $latestInventoryQuery = DB::table('tbl_daily_inventory as inv1')
            ->select('inv1.id', 'inv1.stock_id', 'inv1.Closing_quantity', 'inv1.Openning_quantity', 'inv1.transaction_date')
            ->whereRaw('inv1.transaction_date = (
            SELECT MAX(inv2.transaction_date)
            FROM tbl_daily_inventory as inv2
            WHERE inv2.stock_id = inv1.stock_id
        )')
            ->where('inv1.status', 'OPEN') // Filter by OPEN status;
            ->where('inv1.Closing_quantity', '>', 0); // 👈 Exclude zero Closing_quantity

        $data = DB::table('tbl_items')
            ->JoinSub($latestInventoryQuery, 'latest_inventory', function ($join) {
                $join->on('tbl_items.id', '=', 'latest_inventory.stock_id');
            })
            ->whereDate('tbl_items.expiration_date', '>=', $today)
            ->select(
                'latest_inventory.id as inventory_id',
                'tbl_items.id as item_id',
                'tbl_items.po_no',
                'tbl_items.brand_name',
                'tbl_items.generic_name',
                'tbl_items.dosage',
                'tbl_items.dosage_form',
                'tbl_items.unit',
                'tbl_items.quantity as item_quantity',
                'latest_inventory.Openning_quantity',
                'latest_inventory.Closing_quantity',
                'tbl_items.expiration_date',
                'latest_inventory.transaction_date as last_inventory_date',

            )
            ->orderBy('tbl_items.generic_name')
            ->orderBy('tbl_items.expiration_date', 'asc')
            ->get();

        //  return $data;

        // Group by generic_name and keep only the item with the earliest expiration
        $filtered = $data
            ->groupBy('generic_name')
            ->map(function ($itemsPerGeneric) {
                return $itemsPerGeneric->sortBy('expiration_date')->first();
            })
            ->values();

        return $filtered;
    }


    public function stockCard(Request $request)
    {

        $request->validate([
            'generic_name' => 'required|exists:tbl_items,generic_name',
            'brand_name' => 'required|exists:tbl_items,brand_name',
        ]);

        $generic_name = $request->generic_name;
        $brand_name = $request->brand_name;
        $stockCard = DB::table('vw_stock_card')
            ->where('generic_name', $generic_name)
            ->where('brand_name', $brand_name)
            // ->orderBy('transaction_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'stockCard' => $stockCard,
            'message' => 'Stock card retrieved successfully'
        ], 200);
    }

    public function InventoryRangeDate(Request $request)
    {

        $validated = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $from = $validated['from'];
        $to = $validated['to'];

        $report = DB::table('tbl_items as itms')
            ->select(
                'itms.po_no',
                'itms.brand_name',
                'itms.generic_name',
                'itms.dosage',
                'itms.dosage_form',
                'itms.quantity as current_quantity',
                'itms.expiration_date',
                'inv_from.Openning_quantity as opening_quantity',
                'inv_to.Closing_quantity as closing_quantity',
                DB::raw('SUM(dtxn.quantity) as total_out_quantity')
            )

            // Opening quantity subquery
            ->leftJoin(DB::raw("
            (
                SELECT inv1.stock_id, inv1.Openning_quantity
                FROM tbl_daily_inventory inv1
                INNER JOIN (
                    SELECT stock_id, MAX(transaction_date) AS max_date
                    FROM tbl_daily_inventory
                    WHERE transaction_date <= ?
                    GROUP BY stock_id
                ) inv2
                ON inv1.stock_id = inv2.stock_id AND inv1.transaction_date = inv2.max_date
            ) as inv_from
        "), function ($join) {
                $join->on('inv_from.stock_id', '=', 'itms.id');
            })

            // Closing quantity subquery
            ->leftJoin(DB::raw("
            (
                SELECT inv1.stock_id, inv1.Closing_quantity
                FROM tbl_daily_inventory inv1
                INNER JOIN (
                    SELECT stock_id, MAX(transaction_date) AS max_date
                    FROM tbl_daily_inventory
                    WHERE transaction_date <= ?
                    GROUP BY stock_id
                ) inv2
                ON inv1.stock_id = inv2.stock_id AND inv1.transaction_date = inv2.max_date
            ) as inv_to
        "), function ($join) {
                $join->on('inv_to.stock_id', '=', 'itms.id');
            })

            // Transactions in range
            ->leftJoin('tbl_daily_transactions as dtxn', function ($join) use ($from, $to) {
                $join->on('dtxn.item_id', '=', 'itms.id')
                    ->whereBetween('dtxn.transaction_date', [$from, $to]);
            })

            ->setBindings([$from, $to]) // Bind $from to first subquery, $to to second
            ->groupBy(
                'itms.po_no',
                'itms.id',
                'itms.brand_name',
                'itms.generic_name',
                'itms.dosage',
                'itms.dosage_form',
                'itms.quantity',
                'itms.expiration_date',
                'inv_from.Openning_quantity',
                'inv_to.Closing_quantity'
            )
            ->orderBy('itms.brand_name')
            ->orderBy('itms.generic_name')
            ->get();


        return response()->json(['items' => $report, 'success' => true], 200);
    }


    public function medicinesUnderPO()
    {
        try {
            $items = DB::table('tbl_items')
                ->select('po_no', DB::raw('count(id) as items_count'), DB::raw('MAX(created_at) as created_at'))
                ->groupBy('po_no')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'po_no' => $items
            ], 200);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $th->getMessage()
            ], 500);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => $qe->getMessage()
            ], 500);
            //throw $th;
        }
    }
}
