<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeProcurement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'procurement_code',
        'procurement_description',
        'status',
    ];

    protected $casts = [
        // procurement_code is alphanumeric (e.g., TP01)
        'procurement_code' => 'string',
        'status' => 'integer',
    ];
}
