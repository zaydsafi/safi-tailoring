<?php

namespace App\Exports;

use App\Models\CustomOrderRequest;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * @implements FromQuery<CustomOrderRequest>
 */
class CustomOrderRequestsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * @param  array{status?: string}  $filters
     */
    public function __construct(private readonly array $filters = []) {}

    public function query(): Builder
    {
        return CustomOrderRequest::query()
            ->with(['user', 'convertedOrder'])
            ->when($this->filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->latest();
    }

    public function headings(): array
    {
        return [
            'ID', 'Date', 'Customer', 'Account Email', 'Phone', 'City', 'Address',
            'Request', 'Preferred Fabric', 'Status', 'Converted Order #',
        ];
    }

    /**
     * @param  CustomOrderRequest  $requestRecord
     */
    public function map($requestRecord): array
    {
        return [
            $requestRecord->id,
            $requestRecord->created_at->format('Y-m-d H:i'),
            $requestRecord->customer_name,
            $requestRecord->user?->email,
            $requestRecord->customer_phone,
            $requestRecord->customer_city,
            $requestRecord->customer_address,
            $requestRecord->request_description,
            $requestRecord->preferred_fabric,
            CustomOrderRequest::STATUSES[$requestRecord->status] ?? $requestRecord->status,
            $requestRecord->convertedOrder?->order_number,
        ];
    }
}
