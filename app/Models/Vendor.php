<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address', // Deprecated - kept for backward compatibility
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postcode',
        'country',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get formatted full address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->state,
            $this->postcode,
            $this->getCountryName(),
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get country name from ISO code
     */
    public function getCountryName(): ?string
    {
        if (!$this->country) {
            return null;
        }

        $countries = [
            'MY' => 'Malaysia',
            'SG' => 'Singapore',
            'ID' => 'Indonesia',
            'TH' => 'Thailand',
            'PH' => 'Philippines',
            'VN' => 'Vietnam',
            'BN' => 'Brunei',
            'KH' => 'Cambodia',
            'LA' => 'Laos',
            'MM' => 'Myanmar',
            'CN' => 'China',
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'AU' => 'Australia',
            'JP' => 'Japan',
            'KR' => 'South Korea',
            'IN' => 'India',
        ];

        return $countries[$this->country] ?? $this->country;
    }
}
