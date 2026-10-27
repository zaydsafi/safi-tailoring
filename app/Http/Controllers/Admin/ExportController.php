<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ContactMessagesExport;
use App\Exports\CustomersExport;
use App\Exports\CustomOrderRequestsExport;
use App\Exports\EmailLogExport;
use App\Exports\OrdersExport;
use App\Exports\ProductsExport;
use App\Exports\SalesSummaryExport;
use App\Http\Controllers\Controller;
use App\Models\CustomOrderRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Excel / CSV exports of the shop's reports.
 *
 * Every action returns a streamed download; the file format follows the
 * `format` query parameter (xlsx by default, csv optionally).
 */
class ExportController extends Controller
{
    public function orders(Request $request): BinaryFileResponse
    {
        $data = $request->validate([
            'format' => ['nullable', 'in:xlsx,csv'],
            'status' => ['nullable', 'in:'.implode(',', array_keys(Order::STATUSES))],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        return Excel::download(
            new OrdersExport($data),
            'orders-'.now()->format('Ymd-His').'.'.$this->extension($data)
        );
    }

    public function products(Request $request): BinaryFileResponse
    {
        $format = $request->validate(['format' => ['nullable', 'in:xlsx,csv']]);

        return Excel::download(
            new ProductsExport,
            'products-'.now()->format('Ymd-His').'.'.$this->extension($format)
        );
    }

    public function customers(Request $request): BinaryFileResponse
    {
        $format = $request->validate(['format' => ['nullable', 'in:xlsx,csv']]);

        return Excel::download(
            new CustomersExport,
            'customers-'.now()->format('Ymd-His').'.'.$this->extension($format)
        );
    }

    public function salesSummary(Request $request): BinaryFileResponse
    {
        $format = $request->validate(['format' => ['nullable', 'in:xlsx,csv']]);

        return Excel::download(
            new SalesSummaryExport,
            'sales-summary-'.now()->format('Ymd-His').'.'.$this->extension($format)
        );
    }

    public function customOrders(Request $request): BinaryFileResponse
    {
        $data = $request->validate([
            'format' => ['nullable', 'in:xlsx,csv'],
            'status' => ['nullable', 'in:'.implode(',', array_keys(CustomOrderRequest::STATUSES))],
        ]);

        return Excel::download(
            new CustomOrderRequestsExport($data),
            'custom-order-requests-'.now()->format('Ymd-His').'.'.$this->extension($data)
        );
    }

    public function messages(Request $request): BinaryFileResponse
    {
        $format = $request->validate(['format' => ['nullable', 'in:xlsx,csv']]);

        return Excel::download(
            new ContactMessagesExport,
            'contact-messages-'.now()->format('Ymd-His').'.'.$this->extension($format)
        );
    }

    public function emailLog(Request $request): BinaryFileResponse
    {
        $format = $request->validate(['format' => ['nullable', 'in:xlsx,csv']]);

        return Excel::download(
            new EmailLogExport,
            'email-log-'.now()->format('Ymd-His').'.'.$this->extension($format)
        );
    }

    /**
     * @param  array{format?: string}  $validated
     */
    private function extension(array $validated): string
    {
        return ($validated['format'] ?? 'xlsx') === 'csv' ? 'csv' : 'xlsx';
    }
}
