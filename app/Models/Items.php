<?php

namespace App\Models;


use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class items extends Model
{
    //
    use HasFactory;

    protected $table ='tbl_items';

    protected $fillable =[
        'po_no',
        'brand_name',
        'generic_name',
        'dosage_form',
        'dosage',
        'category',
        'unit',
        'price',
        'price_per_pcs',
        'quantity',
        'box_quantity',
        'quantity_per_box',
        'expiration_date',
        'user_id',
    ];

   public function scopeDateBetween($query, $from, $to)
    {
        return $query->whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);
    }

    public function assignedUsers() {
            return $this->belongsToMany(User::class, 'UserStockAssignment', 'item_id', 'user_id')
                        ->using(UserStockAssignment::class);
        }


    protected static function booted()
    {
        static::created(function ($item) {
            // Create daily inventory record when new stock is added
            daily_inventory::create([
                'stock_id' => $item->id,
                'Openning_quantity' => $item->quantity,
                'Closing_quantity' => $item->quantity,
                'quantity_out' => 0, // Initial value for new stock
                'transaction_date' => now()->format('Y-m-d'),
                'remarks' => 'Initial stock entry',
                'status' => 'OPEN',
                'user_id' => $item->user_id
            ]);

            AuditTrail::create([
                'action' => 'Created',
                'table_name' => 'items',
                'user_id' => $item->user_id,
                'changes' => 'Created item: ' . $item->brand_name . ' - ' . $item->generic_name . ' - form ' . $item->dosage_form . ' - Dosage ' . $item->dosage . ' -  Quantity: ' . $item->quantity .' - Expiration Date: ' . $item->expiration_date,
            ]);

            $exists= Medicinelibrary::where('brand_name', $item->brand_name)
              ->where('generic_name', $item->generic_name)
                ->where('dosage_form', $item->dosage_form)
                ->where('dosage', $item->dosage)
                ->exists();

                if (!$exists) {

                Medicinelibrary::create([
                    'brand_name' => $item->brand_name,
                    'generic_name' => $item->generic_name,
                    'dosage_form' => $item->dosage_form,
                    'dosage' => $item->dosage,
                    'category' => $item->category,
                    'user_id' => $item->user_id,
                ]);

                AuditTrail::create([
                    'action' => 'Added to Library',
                    'table_name' => 'medicinelibrary',
                    'user_id' => $item->user_id,
                    'changes' => 'Added to library: ' . $item->brand_name . ' - ' . $item->generic_name . ' - form ' . $item->dosage_form . ' - Dosage ' . $item->dosage,
                ]);
            }
        });





    }

}
