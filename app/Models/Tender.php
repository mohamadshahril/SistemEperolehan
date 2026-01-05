<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tender extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tender_number',
        'title',
        'description',
        'estimated_budget',
        'opening_date',
        'closing_date',
        'status',
        'requirements',
        'terms_conditions',
        'created_by',
        'awarded_bid_id',
        'awarded_at',
    ];

    protected $casts = [
        'opening_date' => 'date',
        'closing_date' => 'date',
        'estimated_budget' => 'decimal:2',
        'awarded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created the tender
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all bids for this tender
     */
    public function bids(): HasMany
    {
        return $this->hasMany(TenderBid::class);
    }

    /**
     * Get all documents for this tender
     */
    public function documents(): HasMany
    {
        return $this->hasMany(TenderDocument::class);
    }

    /**
     * Get the awarded bid
     */
    public function awardedBid(): BelongsTo
    {
        return $this->belongsTo(TenderBid::class, 'awarded_bid_id');
    }

    /**
     * Check if tender is open for bidding
     */
    public function isOpen(): bool
    {
        return $this->status === 'Published' 
            && now()->between($this->opening_date, $this->closing_date);
    }

    /**
     * Check if tender is closed
     */
    public function isClosed(): bool
    {
        return $this->status === 'Closed' 
            || now()->greaterThan($this->closing_date);
    }

    /**
     * Check if tender has been awarded
     */
    public function isAwarded(): bool
    {
        return $this->status === 'Awarded' && $this->awarded_bid_id !== null;
    }

    /**
     * Generate unique tender number
     */
    public static function generateTenderNumber(): string
    {
        $year = now()->year;
        $month = now()->format('m');
        $prefix = "TND-{$year}{$month}";
        
        $lastTender = static::where('tender_number', 'like', "{$prefix}%")
            ->orderBy('tender_number', 'desc')
            ->first();

        if ($lastTender) {
            $lastNumber = (int) substr($lastTender->tender_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}-{$newNumber}";
    }
}
