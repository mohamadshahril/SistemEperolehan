<?php

namespace App\Policies;

use App\Models\Tender;
use App\Models\User;

class TenderPolicy extends BaseResourcePolicy
{
    public function award(User $user, Tender $tender): bool
    {
        return $user->hasPermission('award tenders');
    }
}
