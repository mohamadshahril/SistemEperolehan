<?php

namespace App\Policies;

use App\Models\DeliveryOrder;
use App\Models\User;

class DeliveryOrderPolicy extends BaseResourcePolicy
{
    public function confirm(User $user, DeliveryOrder $deliveryOrder): bool
    {
        return $user->hasPermission('confirm delivery orders');
    }

    public function print(User $user, DeliveryOrder $deliveryOrder): bool
    {
        return $user->hasPermission('print delivery orders');
    }
}
