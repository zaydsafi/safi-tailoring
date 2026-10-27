<?php

namespace App\Exports;

use App\Models\EmailDelivery;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * @implements FromQuery<EmailDelivery>
 */
class EmailLogExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function query(): Builder
    {
        return EmailDelivery::query()->latest();
    }

    public function headings(): array
    {
        return ['Date', 'Recipient', 'Subject', 'Mailable', 'Status', 'Error'];
    }

    /**
     * @param  EmailDelivery  $delivery
     */
    public function map($delivery): array
    {
        return [
            $delivery->created_at->format('Y-m-d H:i'),
            $delivery->recipient,
            $delivery->subject,
            $delivery->mailable_class,
            ucfirst((string) $delivery->status),
            $delivery->error,
        ];
    }
}
