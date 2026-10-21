<?php

namespace App\Http\Controllers;

use App\Models\CustomOrderRequest;
use Illuminate\Http\Request;

class CustomOrderRequestController extends Controller
{
    public function show()
    {
        return view('shop.custom-order-request');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_address' => ['required', 'string', 'max:500'],
            'customer_city' => ['nullable', 'string', 'max:100'],
            'request_description' => ['required', 'string', 'max:2000'],
            'preferred_fabric' => ['nullable', 'string', 'max:255'],
            'measurements' => ['nullable', 'array'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $measurements = null;

        if (! empty($data['measurements'])) {
            $measurements = collect($data['measurements'])->filter(fn ($v) => $v !== null && $v !== '')
                ->map(fn ($v) => (float) $v)->all();
        }

        // Attach to the logged-in customer when present.
        $userId = auth()->id();

        $requestRecord = CustomOrderRequest::create(array_merge($data, [
            'user_id' => $userId,
            'measurements' => $measurements,
        ]));

        return redirect()->route('custom-order.request.thankyou', $requestRecord)
            ->with('success', t('flash.custom_order_submitted', 'Your custom order request has been submitted. Our team will follow up with you.'));
    }

    public function thankyou(CustomOrderRequest $requestRecord)
    {
        return view('shop.custom-order-thankyou', compact('requestRecord'));
    }
}
