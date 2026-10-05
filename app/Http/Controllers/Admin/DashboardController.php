<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $todayOrders = Order::whereDate('created_at', today())->count();
        $pendingOrders = Order::status('pending')->count();
        $totalProducts = Product::count();
        $totalCustomers = User::where('role', 'customer')->count();
        $revenue = Order::where('payment_status', 'paid')->where('status', '!=', 'cancelled')->sum('total');
        $unreadMessages = ContactMessage::where('is_read', false)->count();

        $recentOrders = Order::withCount('items')->latest()->take(8)->get();
        $lowStock = Product::where('track_stock', true)->where('stock', '<=', 5)->with('category')->orderBy('stock')->take(8)->get();

        $chart = $this->chartData();

        return view('admin.dashboard', compact(
            'todayOrders', 'pendingOrders', 'totalProducts', 'totalCustomers',
            'revenue', 'unreadMessages', 'recentOrders', 'lowStock', 'chart'
        ));
    }

    /**
     * Last 14 days revenue/order series and current status breakdown for charts.
     */
    private function chartData(): array
    {
        $start = today()->subDays(13);

        $daily = Order::where('created_at', '>=', $start)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('DATE(created_at) as day, SUM(CASE WHEN payment_status = ? THEN total ELSE 0 END) as revenue, COUNT(*) as orders', ['paid'])
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $labels = [];
        $revenue = [];
        $orders = [];

        foreach (range(0, 13) as $i) {
            $date = $start->copy()->addDays($i);
            $key = $date->toDateString();
            $labels[] = $date->locale(app()->getLocale())->translatedFormat('M j');
            $revenue[] = round((float) ($daily[$key]->revenue ?? 0), 2);
            $orders[] = (int) ($daily[$key]->orders ?? 0);
        }

        $counts = Order::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        $statusLabels = [];
        $statusCounts = [];

        foreach (Order::STATUSES as $status => $label) {
            $statusLabels[] = t('orders.status_' . $status, $label);
            $statusCounts[] = (int) ($counts[$status] ?? 0);
        }

        return compact('labels', 'revenue', 'orders', 'statusLabels', 'statusCounts');
    }
}
