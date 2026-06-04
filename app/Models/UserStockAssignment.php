<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserStockAssignment extends Model
{
    //
    use HasFactory;

    protected $table = 'user_stock_assignment';

    protected $fillable = ['user_id','item_id'];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function item()
    {
        return $this->belongsTo(Items::class, 'item_id');
    }
}
