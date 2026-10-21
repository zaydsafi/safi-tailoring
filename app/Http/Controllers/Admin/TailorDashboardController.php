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

        $customers = User::where('role', 'customer')
            ->whereNotNull('phone')
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
