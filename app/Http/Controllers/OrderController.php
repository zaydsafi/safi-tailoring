<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\InvoicePdf;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = auth()->user()->orders()->withCount('items')->latest()->paginate(10);

        return view('account.orders', compact('orders'));
    }

    public function show(Order $order)
    {
        abort_unless($order->user_id === auth()->id() || auth()->user()->isAdmin(), 403);

        $order->load(['items', 'statusHistories']);

        return view('account.order-detail', compact('order'));
    }

    /**
     * Invoice / receipt PDF for one of the signed-in customer's own orders.
     */
    public function invoice(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);

        return InvoicePdf::for($order)
            ->download('invoice-'.$order->order_number.'.pdf');
    }

    public function trackForm()
    {
        return view('shop.track');
    }

    public function track(Request $request)
    {
        $data = $request->validate([
            'order_number' => ['required', 'string', 'max:30'],
            'customer_phone' => ['required', 'string', 'max:30'],
        ]);

        $order = Order::with(['items', 'statusHistories'])
            ->where('order_number', $data['order_number'])
            ->where('customer_phone', $data['customer_phone'])
            ->first();

        if (! $order) {
            return back()
                ->withInput()
                ->with('error', t('flash.order_not_found', 'No order found with that order number and phone. Please check both and try again.'));
        }

        return view('shop.track', compact('order'));
    }
}
