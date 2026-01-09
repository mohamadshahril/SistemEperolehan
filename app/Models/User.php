<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        // Newly added fields to support seeding and mass assignment
        'ic_no',
        'staff_id',
        'location_iso_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime'
        ];
    }

    /**
     * Purchase requests where this user is the applicant (submitted by).
     */
    public function purchaseRequestsAsApplicant(): HasMany
    {
        // applicant_id on purchase_requests now stores users.staff_id (string)
        return $this->hasMany(PurchaseRequest::class, 'applicant_id', 'staff_id');
    }

    /**
     * Purchase requests created by this user (owner via user_id).
     */
    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'user_id', 'id');
    }

    /**
     * Purchase requests this user has approved.
     */
    public function purchaseRequestsApproved(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'approved_by', 'id');
    }

    /**
     * Relationship via staff_id to the user_locations mapping table.
     * Links users.staff_id -> user_locations.staff_id
     */
    public function userLocation(): HasOne
    {
        return $this->hasOne(UserLocation::class, 'staff_id', 'staff_id');
    }

    /**
     * Roles assigned to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Direct permissions assigned to the user.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles->contains(fn ($r) => strcasecmp($r->name, $roleName) === 0);
    }

    /**
     * Check if the user has a specific permission (direct or via roles).
     */
    public function hasPermission(string $permissionName): bool
    {
        if ($this->permissions->contains(fn ($p) => strcasecmp($p->name, $permissionName) === 0)) {
            return true;
        }

        return $this->roles()->with('permissions')->get()
            ->flatMap->permissions
            ->contains(fn ($p) => strcasecmp($p->name, $permissionName) === 0);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }
}
