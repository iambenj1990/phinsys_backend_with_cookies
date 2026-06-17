<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemQuantityAdjustment extends Model
{
    //

    protected $table = 'tbl_StockAdjustment';

    protected $fillable = [
        'user_id',
        'item_id',
        'type',
        'prev_quantity',
        'quantity',
        'remarks',
        'is_approved',
        'approved_by'
    ];
}
