<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vot_code',
        'vot_description',
        'status',
    ];

    protected $casts = [
        // vot_code is alphanumeric (e.g., V01)
        'vot_code' => 'string',
        'status' => 'integer',
    ];
}
