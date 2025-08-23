<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class MaifController extends Controller
{
    //

    public function index()
    {
        // Example query to fetch data from the external database
        $data = DB::connection('external_mysql')
                                ->table('patient as p')
                                ->join('transaction as trx', 'p.id', '=', 'trx.patient_id')
                                ->select('p.*', 'trx.transaction_type','trx.status','trx.transaction_number')
                                ->where('trx.transaction_type','Medication')
                                ->where('trx.status', 'qualified')
                                ->get();

        return response()->json($data);
    }
}
