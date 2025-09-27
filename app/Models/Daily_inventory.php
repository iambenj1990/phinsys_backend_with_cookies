<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class daily_inventory extends Model
{
    //
    use HasFactory;

    protected $table ='tbl_daily_inventory';

    protected $fillable = [
        'stock_id',
        'Openning_quantity',
        'Closing_quantity',
        'quantity_out',
        'transaction_date',
        'remarks',
        'status',
        'user_id',
    ];

    


}
