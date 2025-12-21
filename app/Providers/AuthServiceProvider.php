<?php

namespace App\Providers;

use App\Models\DeliveryOrder;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Tender;
use App\Models\TenderBid;
use App\Models\TypeProcurement;
use App\Models\Vot;
use App\Models\ItemUnit;
use App\Models\Location;
use App\Models\Vendor;
use App\Policies\BaseResourcePolicy;
use App\Policies\DeliveryOrderPolicy;
use App\Policies\PurchaseRequestPolicy;
use App\Policies\TenderPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Permission;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Generic CRUD (maps to view/manage <resource>)
        Vendor::class => BaseResourcePolicy::class,
        Location::class => BaseResourcePolicy::class,
        PurchaseOrder::class => BaseResourcePolicy::class,
        Vot::class => BaseResourcePolicy::class,
        TypeProcurement::class => BaseResourcePolicy::class,
        ItemUnit::class => BaseResourcePolicy::class,
        TenderBid::class => BaseResourcePolicy::class,

        // Specialized
        PurchaseRequest::class => PurchaseRequestPolicy::class,
        DeliveryOrder::class => DeliveryOrderPolicy::class,
        Tender::class => TenderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Admin allow-all
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole('Admin') ? true : null;
        });

        // Dynamically register gates for each permission name
        try {
            Permission::query()->pluck('name')->each(function (string $name) {
                if (! Gate::has($name)) {
                    Gate::define($name, function (User $user) use ($name) {
                        return $user->hasPermission($name);
                    });
                }
            });
        } catch (\Throwable $e) {
            // During migrations/first boot, permissions table may not exist; ignore.
        }
    }
}
