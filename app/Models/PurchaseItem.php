<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;


class PurchaseItem extends Model
{
    //
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'purchase_request_id',
        'purchase_ref_no',
        'item_name',
        'item_code',
        'purpose',
        'unit',
        'quantity',
        'unit_price',
        'total_price',
        ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Relationship: belongs to a purchase request.
     */
    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    /**
     * Auto-calculate total_price if not set.
     */
    protected static function booted(): void
    {
        static::saving(function (self $item) {
            if (is_null($item->total_price) && $item->unit_price && $item->quantity) {
                $item->total_price = $item->unit_price * $item->quantity;
            }
        });
    }
}
