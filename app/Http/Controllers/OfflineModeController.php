<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\items;
use App\Models\customers;
use App\Models\daily_transactions;
use App\Models\daily_inventory;
use App\Models\User;
use App\Models\UserCredentials;
use App\Models\Modes;



class OfflineModeController extends Controller
{
    //

    public function GetDBData(){

        try {

            $items = items::all();
            $customers = customers::all();
            $users = User::all()->makeVisible('password');
            $lastInventory = daily_inventory::all();
            $transactions = daily_transactions::all();

            return response()->json(['success'=> true, 'items'=> $items, 'customers'=> $customers,'users'=> $users,'lastInventory'=> $lastInventory,'transactions'=> $transactions],200);
        } catch (\Throwable $th) {
            return response()->json(['success'=> false, 'message'=> $th],500);
        }
    }

    public function InsertToOfflineDB(Request $request){



    }
}
