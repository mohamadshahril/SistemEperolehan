<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderBid extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tender_id',
        'vendor_id',
        'bid_amount',
        'proposal',
        'technical_specifications',
        'delivery_timeline_days',
        'attachment_path',
        'status',
        'notes',
        'submitted_at',
    ];

    protected $casts = [
        'bid_amount' => 'decimal:2',
        'delivery_timeline_days' => 'integer',
        'submitted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the tender this bid belongs to
     */
    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class);
    }

    /**
     * Get the vendor who submitted this bid
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Check if bid is submitted
     */
    public function isSubmitted(): bool
    {
        return $this->status === 'Submitted';
    }

    /**
     * Check if bid is under review
     */
    public function isUnderReview(): bool
    {
        return $this->status === 'Under Review';
    }

    /**
     * Check if bid is accepted
     */
    public function isAccepted(): bool
    {
        return $this->status === 'Accepted';
    }

    /**
     * Check if bid is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'Rejected';
    }

    /**
     * Check if bid is withdrawn
     */
    public function isWithdrawn(): bool
    {
        return $this->status === 'Withdrawn';
    }

    /**
     * Mark bid as submitted
     */
    public function markAsSubmitted(): void
    {
        $this->update([
            'status' => 'Submitted',
            'submitted_at' => now(),
        ]);
    }

    /**
     * Mark bid as under review
     */
    public function markAsUnderReview(): void
    {
        $this->update(['status' => 'Under Review']);
    }

    /**
     * Mark bid as accepted
     */
    public function markAsAccepted(): void
    {
        $this->update(['status' => 'Accepted']);
    }

    /**
     * Mark bid as rejected
     */
    public function markAsRejected(string $reason = null): void
    {
        $this->update([
            'status' => 'Rejected',
            'notes' => $reason,
        ]);
    }

    /**
     * Mark bid as withdrawn
     */
    public function markAsWithdrawn(): void
    {
        $this->update(['status' => 'Withdrawn']);
    }
}
