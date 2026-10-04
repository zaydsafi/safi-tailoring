<?php

namespace App\Http\Controllers;

use App\Models\MeasurementProfile;
use App\Models\Order;
use App\Models\Setting;
use App\Support\Cart;
use App\Support\HesabPay;
use App\Support\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function __construct(private Cart $cart)
    {
    }

    public function show()
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', t('flash.cart_empty', 'Your cart is empty.'));
        }

        $cartItems = $this->cart->content();

        if ($cartItems->isEmpty()) {
            $this->cart->clear();
            return redirect()->route('cart.index')->with('error', t('flash.cart_empty', 'Your cart is empty.'));
        }

        $subtotal = $this->cart->subtotal();
        $deliveryFee = $this->deliveryFee($subtotal);
        $measurementFields = MeasurementProfile::fields();
        $savedProfiles = auth()->check() ? auth()->user()->measurementProfiles()->latest()->get() : collect();
        $hesabpayEnabled = HesabPay::enabled();

        return view('shop.checkout', compact('cartItems', 'subtotal', 'deliveryFee', 'measurementFields', 'savedProfiles', 'hesabpayEnabled'));
    }

    public function store(Request $request)
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', t('flash.cart_empty', 'Your cart is empty.'));
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_address' => ['required', 'string', 'max:500'],
            'customer_city' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', Rule::in(array_filter([
                'cash_on_delivery',
                'bank_transfer',
                HesabPay::enabled() ? 'hesabpay' : null,
            ]))],
            'measurements' => ['nullable', 'array'],
            'save_measurements' => ['nullable', 'boolean'],
            'profile_label' => ['nullable', 'string', 'max:100'],
        ]);

        $cartItems = $this->cart->content();

        if ($cartItems->isEmpty()) {
            $this->cart->clear();
            return redirect()->route('cart.index')->with('error', t('flash.cart_items_unavailable', 'Your cart items are no longer available.'));
        }

        // Validate required measurements for custom items.
        $customItems = $cartItems->where('product.type', 'custom');
        if ($customItems->isNotEmpty()) {
            $request->validate([
                'measurements' => ['required', 'array'],
            ], ['measurements.required' => t('flash.measurements_required', 'Please provide measurements for your made-to-measure items.')]);

            foreach ($customItems as $item) {
                $m = $data['measurements'][$item->key] ?? null;
                $hasAny = collect($m ?? [])->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                if (!$hasAny) {
                    return back()
                        ->withInput()
                        ->with('error', t('flash.measurement_needed_for', 'Please enter at least one measurement for') . ' "' . $item->product->name . '".');
                }
            }
        }

        // Validate stock for ready items once more.
        foreach ($cartItems->where('product.type', 'ready') as $item) {
            if (!$item->product->inStock($item->qty)) {
                return back()->with('error', '"' . $item->product->name . '" ' . t('flash.not_enough_stock', 'does not have enough stock') . ' (' . $item->product->stock . ' ' . t('flash.available', 'available') . ').');
            }
        }

        $subtotal = $cartItems->sum('line_total');
        $deliveryFee = $this->deliveryFee($subtotal);

        $order = DB::transaction(function () use ($data, $cartItems, $subtotal, $deliveryFee) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_address' => $data['customer_address'],
                'customer_city' => $data['customer_city'] ?? null,
                'notes' => $data['notes'] ?? null,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'status' => 'pending',
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $subtotal + $deliveryFee,
            ]);

            foreach ($cartItems as $item) {
                $measurements = null;
                if ($item->product->type === 'custom') {
                    $raw = $data['measurements'][$item->key] ?? [];
                    $measurements = collect($raw)->filter(fn ($v) => $v !== null && $v !== '')->map(fn ($v) => (float) $v)->all();
                }

                $order->items()->create([
                    'product_id' => $item->product->id,
                    'product_name' => $item->product->name,
                    'type' => $item->product->type,
                    'size' => $item->size,
                    'measurements' => $measurements,
                    'price' => $item->price,
                    'quantity' => $item->qty,
                    'line_total' => $item->line_total,
                ]);

                $item->product->decrementStock($item->qty);
            }

            $order->statusHistories()->create(['status' => 'pending', 'note' => 'Order placed']);

            return $order;
        });

        // Optionally save a measurement profile for logged-in customers.
        if (auth()->check() && ($data['save_measurements'] ?? false) && $customItems->isNotEmpty()) {
            $firstItem = $customItems->first();
            $raw = $data['measurements'][$firstItem->key] ?? [];
            $clean = collect($raw)->filter(fn ($v) => $v !== null && $v !== '')->map(fn ($v) => (float) $v)->all();

            if (!empty($clean)) {
                auth()->user()->measurementProfiles()->create(array_merge($clean, [
                    'label' => $data['profile_label'] ?? 'My Measurements',
                ]));
            }
        }

        $this->cart->clear();

        WhatsApp::notifyShop(
            'New order ' . $order->order_number . ' — ' . money($order->total)
            . ' (' . str_replace('_', ' ', $order->payment_method) . '). '
            . route('admin.orders.show', $order)
        );

        WhatsApp::notifyCustomer(
            $order->customer_phone,
            'Safi Tailoring: thank you! Your order ' . $order->order_number . ' was placed. Total: '
            . money($order->total) . '. Track your order: ' . route('orders.track')
        );

        if ($order->payment_method === 'hesabpay') {
            $paymentUrl = HesabPay::createSession($order);

            if ($paymentUrl) {
                session(['order_confirmed' => $order->order_number]);

                return redirect()->away($paymentUrl);
            }

            return redirect()->route('orders.success', $order)
                ->with('success', t('flash.order_placed', 'Order placed successfully!'))
                ->with('error', t('flash.hesabpay_unavailable', 'We could not open the HesabPay checkout automatically. Your order is saved — use "Pay with HesabPay" to try again, or contact us on WhatsApp.'))
                ->with('order_confirmed', $order->order_number);
        }

        return redirect()->route('orders.success', $order)
            ->with('success', t('flash.order_placed', 'Order placed successfully!'))
            ->with('order_confirmed', $order->order_number);
    }

    public function success(Order $order)
    {
        $allowed = session('order_confirmed') === $order->order_number
            || (auth()->check() && (auth()->id() === $order->user_id || auth()->user()->isAdmin()));

        abort_unless($allowed, 403);

        $order->load('items');

        return view('shop.order-success', compact('order'));
    }

    private function deliveryFee(float $subtotal): float
    {
        $fee = (float) Setting::get('delivery_fee', '0');
        $freeOver = (float) Setting::get('free_delivery_over', '0');

        if ($freeOver > 0 && $subtotal >= $freeOver) {
            return 0.0;
        }

        return $fee;
    }
}
