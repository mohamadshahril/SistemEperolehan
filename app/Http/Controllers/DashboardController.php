<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use App\Models\Status;
use App\Models\Vendor;
use App\Models\Tender;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $summary = [];

        if ($user->hasRole('Admin')) {
            $summary = [
                'total_purchase_requests' => PurchaseRequest::count(),
                'pending_purchase_requests' => PurchaseRequest::whereHas('statusRef', function($q) {
                    $q->where('name', 'Pending');
                })->count(),
                'total_vendors' => Vendor::count(),
                'total_tenders' => Tender::count(),
            ];
        } elseif ($user->hasRole('Manager')) {
            $summary = [
                'pending_approvals' => PurchaseRequest::whereHas('statusRef', function($q) {
                    $q->where('name', 'Pending');
                })->count(),
                'approved_requests' => PurchaseRequest::whereHas('statusRef', function($q) {
                    $q->where('name', 'Approved');
                })->count(),
                'total_vendors' => Vendor::count(),
            ];
        } elseif ($user->hasRole('Staff')) {
            $summary = [
                'my_purchase_requests' => PurchaseRequest::where('user_id', $user->id)->count(),
                'my_pending_requests' => PurchaseRequest::where('user_id', $user->id)
                    ->whereHas('statusRef', function($q) {
                        $q->where('name', 'Pending');
                    })->count(),
                'my_approved_requests' => PurchaseRequest::where('user_id', $user->id)
                    ->whereHas('statusRef', function($q) {
                        $q->where('name', 'Approved');
                    })->count(),
            ];
        } else {
            // Default summary for users with no specific role or other roles
            $summary = [
                'total_purchase_requests' => PurchaseRequest::count(),
            ];
        }

        return Inertia::render('Dashboard', [
            'summary' => $summary,
        ]);
    }
}
