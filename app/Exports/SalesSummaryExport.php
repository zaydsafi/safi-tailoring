<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Monthly sales summary for the trailing 12 months: number of orders,
 * paid orders, delivered orders and collected revenue per month.
 *
 * Aggregation is done with portable date-range queries in PHP instead of
 * SQL date functions so it works on both SQLite (tests) and MySQL (production).
 */
class SalesSummaryExport implements FromArray, WithHeadings, WithTitle
{
    use Exportable;

    /**
     * @return list<array{int, int, int, float}>
     */
    public function array(): array
    {
        $rows = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::today()->subMonths($i);
            $from = $month->copy()->startOfMonth();
            $to = $month->copy()->endOfMonth();

            $base = Order::query()
                ->where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

            $paidRevenue = (clone $base)
                ->where('payment_status', 'paid')
                ->get(['total'])
                ->sum(fn ($order) => (float) $order->total);

            $paidOrders = (clone $base)->where('payment_status', 'paid')->count();
            $deliveredOrders = (clone $base)->where('status', 'delivered')->count();
            $totalOrders = (clone $base)->count();

            $rows[] = [
                $month->locale('en')->translatedFormat('M Y'),
                $totalOrders,
                $paidOrders,
                $deliveredOrders,
                round($paidRevenue, 2),
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['Month', 'Orders', 'Paid Orders', 'Delivered Orders', 'Revenue'];
    }

    public function title(): string
    {
        return 'Sales Summary';
    }
}
