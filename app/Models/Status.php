<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    use HasFactory;

    protected $table = 'statuses';

    protected $fillable = [
        'name',
        'description',
    ];

    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'status_id');
    }
}
