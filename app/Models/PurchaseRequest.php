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
        'title',
        'type_procurement_id',
        'file_reference_id',
        'vot_id',
        'location_iso_code',
        'budget',
        'purpose',
        'items',
        // store status by id
        'status_id',
        // keep virtual status (name) fillable for backward compatibility via mutator
        'status',
        'submitted_at',
        'attachment_path',
        'purchase_code',
        'approval_comment',
        'approved_by',
        'approved_at',
    ];

    /**
     * Ensure the virtual status (name) is included when serializing the model.
     */
    protected $appends = ['status'];

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

    public function statusRef(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
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

            // Generate purchase_code if not provided
            if (empty($model->purchase_code)) {
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
                            ->where('purchase_code', 'like', $prefix . '%')
                            ->orderByDesc('purchase_code')
                            ->value('purchase_code');

                        $next = 1;
                        if ($latest) {
                            $parts = explode('/', $latest);
                            $lastSegment = end($parts);
                            $next = ctype_digit($lastSegment) ? ((int) $lastSegment + 1) : 1;
                        }

                        $model->purchase_code = $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
                    }
                }
            }
        });
    }
}
