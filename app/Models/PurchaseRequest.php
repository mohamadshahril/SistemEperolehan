<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PurchaseRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'applicant_id',
        'title',
        'type_procurement_id',
        'file_reference_id',
        'vot_id',
        'location_iso_code',
        'budget',
        'note',
        'status_id',
        'submitted_at',
        'attachment_path',
        'purchase_ref_no',
        'approval_remarks',
        'approved_by',
        'approved_at',
    ];


    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'budget' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Applicant user who submitted the purchase request.
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
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

    public function statusRef(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function items()
    {
        // A purchase request has many purchase items via purchase_items.purchase_request_id
        return $this->hasMany(PurchaseItem::class, 'purchase_request_id', 'id');
    }



    /**
     * Backward-compat: expose `purpose` while storing in `note` column.
     */
    public function getPurposeAttribute(): ?string
    {
        // Prefer attribute if already set on the model instance
        if (array_key_exists('note', $this->attributes)) {
            return $this->attributes['note'] ?? null;
        }
        // Fallback to attribute accessor
        return $this->getAttribute('note');
    }

    /**
     * Backward-compat: allow setting `purpose` which writes into `note`.
     */
    public function setPurposeAttribute($value): void
    {
        $this->attributes['note'] = $value;
    }

    /**
     * Accessor to expose legacy `status` attribute (name) backed by status_id.
     */
    public function getStatusAttribute(): ?string
    {
        return $this->relationLoaded('statusRef')
            ? ($this->statusRef?->name)
            : optional($this->statusRef()->first(['name']))?->name;
    }

    /**
     * Mutator to allow setting `status` by name and store into status_id.
     */
    public function setStatusAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['status_id'] = null;
            return;
        }
        $statusId = Status::query()->where('name', $value)->value('id');
        // If not found, leave as-is (null) to avoid invalid FK
        $this->attributes['status_id'] = $statusId;
    }

    /**
     * Default status_id to Pending on create if not provided.
     */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->status_id)) {
                $model->status_id = Status::query()->where('name', 'Pending')->value('id');
            }

            // Generate purchase_ref_no if not provided
            if (empty($model->purchase_ref_no)) {
                // Require essential parts to exist
                $locationIso = $model->location_iso_code;
                $fileId = $model->file_reference_id;
                $votId = $model->vot_id;

                if ($locationIso && $fileId && $votId) {
                    $fileCode = DB::table('file_references')->where('id', $fileId)->value('file_code');
                    $votCode = DB::table('vots')->where('id', $votId)->value('vot_code');

                    if ($fileCode && $votCode) {
                        $prefix = sprintf('AIM/%s/%s/%s/', $locationIso, $fileCode, $votCode);
                        // Find latest running number for this prefix
                        $latest = static::query()
                            ->where('purchase_ref_no', 'like', $prefix . '%')
                            ->orderByDesc('purchase_ref_no')
                            ->value('purchase_ref_no');

                        $next = 1;
                        if ($latest) {
                            $parts = explode('/', $latest);
                            $lastSegment = end($parts);
                            $next = ctype_digit($lastSegment) ? ((int) $lastSegment + 1) : 1;
                        }

                        $model->purchase_ref_no = $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
                    }
                }
            }
        });
    }
}
