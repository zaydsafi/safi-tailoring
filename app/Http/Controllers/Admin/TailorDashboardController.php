<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomOrderRequest;
use App\Models\User;

class TailorDashboardController extends Controller
{
    public function index()
    {
        $pendingRequests = CustomOrderRequest::with(['user'])
            ->whereIn('status', ['new', 'assigned', 'in_progress'])
            ->latest()
            ->take(10)
            ->get();

        $recentlyConverted = CustomOrderRequest::with(['convertedOrder'])
            ->where('status', 'converted')
            ->latest()
            ->take(10)
            ->get();

        // The dashboard reads each customer's has-measurements badge, so the
        // count is fetched with the customers in one query (no N+1).
        $customers = User::where('role', 'customer')
            ->whereNotNull('phone')
            ->withCount('measurementProfiles')
            ->latest()
            ->take(20)
            ->get();

        return view('admin.tailor.dashboard', compact('pendingRequests', 'recentlyConverted', 'customers'));
    }

    public function requests()
    {
        $requests = CustomOrderRequest::with(['user'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.tailor.requests', compact('requests'));
    }
}
