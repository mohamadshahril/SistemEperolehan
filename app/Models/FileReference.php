<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileReference extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'file_code',
        'file_description',
        'parent_file_code',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];
}
