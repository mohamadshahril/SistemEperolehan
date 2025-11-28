<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_order_id',
        'do_number',
        'delivery_date',
        'file_path',
        'is_received',
        'notes',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'is_received' => 'boolean',
    ];

    public function purchaseOrder(): BelongsTo
    {
        // Assuming your PurchaseOrder model is named PurchaseOrder
        return $this->belongsTo(PurchaseOrder::class);
    }
}