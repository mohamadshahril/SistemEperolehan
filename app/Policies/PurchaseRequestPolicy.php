<?php

namespace App\Policies;

use App\Models\PurchaseRequest;
use App\Models\User;

class PurchaseRequestPolicy
{
    public function viewAny(User $user): bool
    {
        // Owner can view own via controller; approvers/managers via permission
        return $user->hasPermission('view approvals') || $user->hasRole('Admin') || true;
    }

    public function view(User $user, PurchaseRequest $purchaseRequest): bool
    {
        if ($user->id === $purchaseRequest->user_id) {
            return true;
        }
        return $user->hasPermission('view approvals');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create purchase requests');
    }

    public function update(User $user, PurchaseRequest $purchaseRequest): bool
    {
        if ($user->hasRole('Admin')) return true;

        // Staff can update own while Pending
        $isOwner = $user->id === $purchaseRequest->user_id;
        $isPending = ($purchaseRequest->status === 'Pending' || $purchaseRequest->status_id === (int)($purchaseRequest->status_id));
        return $isOwner && $isPending;
    }

    public function delete(User $user, PurchaseRequest $purchaseRequest): bool
    {
        // Treat delete as update/manage: only admin or owner while pending
        return $this->update($user, $purchaseRequest);
    }

    public function approve(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->hasPermission('approve purchase requests');
    }

    public function reject(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->hasPermission('reject purchase requests');
    }
}
