<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomOrderRequest;
use App\Models\Order;
use App\Models\Setting;
use App\Support\Notify;
use App\Support\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomOrderRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = CustomOrderRequest::with('user')
            ->when($request->filled('status'), fn ($q) => $q->status($request->query('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->query('q');
                $q->where(fn ($w) => $w
                    ->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('request_description', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.custom-orders.index', compact('requests'));
    }

    public function show(CustomOrderRequest $requestRecord)
    {
        $requestRecord->load(['user', 'convertedOrder']);

        return view('admin.custom-orders.show', compact('requestRecord'));
    }

    public function updateStatus(Request $request, CustomOrderRequest $requestRecord)
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(CustomOrderRequest::STATUSES))],
            'assigned_to' => ['nullable', 'string', 'max:100'],
        ]);

        $requestRecord->update([
            'status' => $data['status'],
            'assigned_to' => $data['assigned_to'] ?? null,
        ]);

        Notify::customOrderStatusChange(
            $requestRecord,
            $data['status'],
            $data['assigned_to'] ?? null
        );

        return back()->with('success', t('admin.flash.custom_order_status_updated', 'Custom order request status updated.'));
    }

    public function convertToOrder(Request $request, CustomOrderRequest $requestRecord)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.measurements' => ['nullable', 'array'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $customer = $requestRecord->user ?? null;

        $customerName = $request->input('customer_name', $requestRecord->customer_name);
        $customerPhone = $request->input('customer_phone', $requestRecord->customer_phone);
        $customerEmail = $request->input('customer_email', $requestRecord->customer_email);
        $customerAddress = $request->input('customer_address', $requestRecord->customer_address);
        $customerCity = $request->input('customer_city', $requestRecord->customer_city);

        $items = collect($data['items'])->map(function ($item) {
            $measurements = null;

            if (! empty($item['measurements'])) {
                $measurements = collect($item['measurements'])->filter(fn ($v) => $v !== null && $v !== '')
                    ->map(fn ($v) => (float) $v)->all();
            }

            return [
                'product_name' => $item['description'],
                'type' => 'custom',
                'size' => null,
                'measurements' => $measurements,
                'price' => (float) $item['price'],
                'quantity' => (int) $item['quantity'],
                'line_total' => (float) $item['price'] * (int) $item['quantity'],
            ];
        });

        $subtotal = $items->sum('line_total');
        $deliveryFee = (float) Setting::get('delivery_fee', '0');

        $order = DB::transaction(function () use ($customer, $data, $items, $subtotal, $deliveryFee, $customerName, $customerPhone, $customerEmail, $customerAddress, $customerCity) {
            $order = Order::create([
                'user_id' => $customer?->id,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'customer_email' => $customerEmail ?: null,
                'customer_address' => $customerAddress,
                'customer_city' => $customerCity ?: null,
                'notes' => $data['notes'] ?? null,
                'payment_method' => 'cash_on_delivery',
                'payment_status' => 'pending',
                'status' => 'pending',
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $subtotal + $deliveryFee,
            ]);

            foreach ($items as $item) {
                $order->items()->create($item);
            }

            $order->statusHistories()->create(['status' => 'pending', 'note' => 'Order converted from a customer custom order request']);

            return $order;
        });

        $requestRecord->markConverted($order);

        WhatsApp::notifyShop(
            'New custom order '.$order->order_number.' — '.money($order->total)
            .' ('.$order->customer_name.', '.$order->customer_phone.'). '
            .route('admin.orders.show', $order)
        );

        Notify::orderPlaced(
            $order,
            t('mail.order_placed_line', 'Thank you! Your order was placed. Total: :total.', ['total' => money($order->total)])
        );

        return redirect()->route('admin.orders.show', $order)
            ->with('success', t('admin.flash.order_created_from_request', 'Order created from the custom order request.'));
    }
}
