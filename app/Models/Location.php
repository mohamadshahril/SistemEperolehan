<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'location_iso_code',
        'location_name',
        'parent_iso_code',
    ];

    /**
     * Automatically uppercase the ISO code when setting.
     */
    protected function locationIsoCode(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => is_null($value) ? null : mb_strtoupper(trim($value))
        );
    }

    /**
     * Automatically uppercase the parent ISO code when setting.
     */
    protected function location_name(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => is_null($value) ? null : mb_strtoupper(trim($value))
        );
    }

    /**
     * Automatically uppercase the parent ISO code when setting.
     */
    protected function parentIsoCode(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => is_null($value) ? null : mb_strtoupper(trim($value))
        );
    }

}

