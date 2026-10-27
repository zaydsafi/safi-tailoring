<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Order-level export: one row per order, newest first.
 *
 * @implements FromQuery<Order>
 */
class OrdersExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * @param  array{status?: string, from?: string, to?: string}  $filters
     */
    public function __construct(private readonly array $filters = []) {}

    public function query(): Builder
    {
        return Order::query()
            ->when($this->filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($this->filters['from'] ?? null, fn (Builder $q, string $from) => $q->whereDate('created_at', '>=', $from))
            ->when($this->filters['to'] ?? null, fn (Builder $q, string $to) => $q->whereDate('created_at', '<=', $to))
            ->latest();
    }

    public function headings(): array
    {
        return [
            'Order #', 'Date', 'Customer', 'Phone', 'Email', 'Address', 'City',
            'Status', 'Payment Method', 'Payment Status', 'Subtotal', 'Delivery Fee', 'Total', 'Items',
        ];
    }

    /**
     * @param  Order  $order
     */
    public function map($order): array
    {
        return [
            $order->order_number,
            $order->created_at->format('Y-m-d H:i'),
            $order->customer_name,
            $order->customer_phone,
            $order->customer_email,
            $order->customer_address,
            $order->customer_city,
            Order::STATUSES[$order->status] ?? $order->status,
            ucfirst(str_replace('_', ' ', (string) $order->payment_method)),
            ucfirst((string) $order->payment_status),
            (float) $order->subtotal,
            (float) $order->delivery_fee,
            (float) $order->total,
            $order->items()->count(),
        ];
    }
}
