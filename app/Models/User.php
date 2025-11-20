<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

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
     * Relationship via staff_id to the user_locations mapping table.
     * Links users.staff_id -> user_locations.staff_id
     */
    public function userLocation(): HasOne
    {
        return $this->hasOne(UserLocation::class, 'staff_id', 'staff_id');
    }
}
