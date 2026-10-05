<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\WhatsApp;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::withCount('items')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->query('q');
                $q->where(fn ($w) => $w->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statuses = Order::STATUSES;

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'statusHistories']);
        $statuses = Order::STATUSES;

        return view('admin.orders.show', compact('order', 'statuses'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys(Order::STATUSES))],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($order->status !== $data['status']) {
            $order->update(['status' => $data['status']]);
            $order->statusHistories()->create([
                'status' => $data['status'],
                'note' => $data['note'] ?? null,
            ]);

            $message = 'Safi Tailoring: your order ' . $order->order_number . ' is now '
                . (Order::STATUSES[$data['status']] ?? $data['status']) . '.';

            if (filled($data['note'] ?? null)) {
                $message .= ' Note: ' . $data['note'];
            }

            $message .= ' Track: ' . route('orders.track');

            WhatsApp::notifyCustomer($order->customer_phone, $message);
        }

        return back()->with('success', t('admin.flash.order_status_updated', 'Order status updated.'));
    }

    public function updatePayment(Request $request, Order $order)
    {
        $data = $request->validate([
            'payment_status' => ['required', 'in:pending,paid,refunded'],
        ]);

        $order->update(['payment_status' => $data['payment_status']]);

        return back()->with('success', t('admin.flash.payment_status_updated', 'Payment status updated.'));
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return back()->with('success', t('admin.flash.order_deleted', 'Order deleted.'));
    }
}
