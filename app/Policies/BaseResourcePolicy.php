<?php

namespace App\Policies;

use App\Models\User;

class BaseResourcePolicy
{
    /**
     * Map model class basenames to permission resource keys.
     */
    protected function resourceFor(object|string $model): string
    {
        $map = [
            'Vendor' => 'vendors',
            'Location' => 'locations',
            'PurchaseOrder' => 'purchase orders',
            'DeliveryOrder' => 'delivery orders',
            'Vot' => 'vots',
            'TypeProcurement' => 'type procurements',
            'ItemUnit' => 'item units',
            'Tender' => 'tenders',
            'TenderBid' => 'tender bids',
        ];

        $key = is_string($model) ? $model : class_basename($model);
        return $map[$key] ?? str(class_basename($key))->snake(' ')->plural()->toString();
    }

    public function viewAny(User $user): bool
    {
        // resource not known here; allow via broad view permissions checked in controllers/routes
        return true;
    }

    public function view(User $user, object $model): bool
    {
        $res = $this->resourceFor($model);
        return $user->hasPermission("view {$res}");
    }

    public function create(User $user): bool
    {
        // Default create maps to manage <resource>; specific policies can override
        return false; // let controllers use middleware or specialized policies
    }

    public function update(User $user, object $model): bool
    {
        $res = $this->resourceFor($model);
        return $user->hasPermission("manage {$res}");
    }

    public function delete(User $user, object $model): bool
    {
        $res = $this->resourceFor($model);
        return $user->hasPermission("manage {$res}");
    }
}
