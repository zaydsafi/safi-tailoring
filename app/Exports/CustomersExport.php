<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Registered customers with their order statistics.
 *
 * @implements FromQuery<User>
 */
class CustomersExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function query(): Builder
    {
        return User::query()
            ->where('role', 'customer')
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->with('orders')
            ->orderByDesc('id');
    }

    public function headings(): array
    {
        return [
            'Name', 'Email', 'Phone', 'City', 'Address',
            'Orders', 'Orders Total', 'Joined', 'Blocked',
        ];
    }

    /**
     * @param  User  $customer
     */
    public function map($customer): array
    {
        return [
            $customer->name,
            $customer->email,
            $customer->phone,
            $customer->city,
            $customer->address,
            (int) $customer->orders_count,
            (float) ($customer->orders_sum_total ?? 0),
            $customer->created_at->format('Y-m-d'),
            $customer->isBlocked() ? ($customer->blocked_at ? $customer->blocked_at->format('Y-m-d') : 'yes') : 'no',
        ];
    }
}
