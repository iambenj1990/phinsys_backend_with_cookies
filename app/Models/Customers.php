<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class customers extends Model
{
    //
    use HasFactory;

    protected $table ='tbl_customers';

    protected $fillable = [
        'firstname',
        'lastname',
        'middlename',
        'ext',
        'birthdate',
        'contact_number',
        'age',
        'gender',
        'is_not_tagum',
        'street',
        'purok',
        'barangay',
        'city',
        'province',
        'category',
        'is_pwd',
        'is_solo',
        'user_id',
        'origin',
        'maifp_id'
    ];

     public function scopeDateBetween($query, $from, $to)
    {
        return $query->whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);
    }
}
