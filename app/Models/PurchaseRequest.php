<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'type_procurement_id',
        'file_reference_id',
        'vot_id',
        'location_iso_code',
        'budget',
        'purpose',
        'items',
        'status',
        'submitted_at',
        'attachment_path',
        'purchase_code',
        'approval_comment',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'budget' => 'decimal:2',
        'items' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fileReference(): BelongsTo
    {
        return $this->belongsTo(\App\Models\FileReference::class);
    }

    public function vot(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Vot::class);
    }

    public function typeProcurement(): BelongsTo
    {
        return $this->belongsTo(\App\Models\TypeProcurement::class);
    }
}
