<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomOrderRequest;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Support\Notify;
use App\Support\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerOrderController extends Controller
{
    public function create(Request $request)
    {
        $customers = User::query()
            ->where('role', 'customer')
            ->whereNotNull('phone')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $selectedCustomer = null;

        if ($request->filled('customer_id')) {
            $selectedCustomer = User::query()
                ->where('role', 'customer')
                ->where('id', (int) $request->input('customer_id'))
                ->first();
        }

        return view('admin.custom-orders.create-from-customer', compact('customers', 'selectedCustomer'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:users,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_address' => ['required', 'string', 'max:500'],
            'customer_city' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.measurements' => ['nullable', 'array'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'custom_order_request_id' => ['nullable', 'exists:custom_order_requests,id'],
        ]);

        $customer = User::find($data['customer_id']);

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

        $order = DB::transaction(function () use ($customer, $data, $items, $subtotal, $deliveryFee) {
            $order = Order::create([
                'user_id' => $customer->id,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_address' => $data['customer_address'],
                'customer_city' => $data['customer_city'] ?? null,
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

            $order->statusHistories()->create(['status' => 'pending', 'note' => 'Order created by staff on behalf of customer']);

            return $order;
        });

        $requestDescription = $items->first()['product_name'];

        if (! empty($data['custom_order_request_id'])) {
            $customRequest = CustomOrderRequest::find($request->input('custom_order_request_id'));

            if ($customRequest) {
                $customRequest->markConverted($order);
            }
        }

        WhatsApp::notifyShop(
            'New custom order '.$order->order_number.' — '.money($order->total)
            .' ('.$customer->name.', '.$customer->phone.'). '
            .route('admin.orders.show', $order)
        );

        Notify::orderPlaced(
            $order,
            t('mail.order_placed_line', 'Thank you! Your order was placed. Total: :total.', ['total' => money($order->total)])
        );

        return redirect()->route('admin.orders.show', $order)
            ->with('success', t('admin.flash.order_created', 'Order created on behalf of customer.'));
    }
}
