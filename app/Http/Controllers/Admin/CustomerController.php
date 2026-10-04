<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class CustomerController extends Controller
{
    public function show(User $customer)
    {
        abort_unless($customer->role === 'customer', 404);

        $customer->load(['orders' => fn ($q) => $q->latest()->take(10), 'measurementProfiles']);

        return view('admin.customers.show', compact('customer'));
    }
}
