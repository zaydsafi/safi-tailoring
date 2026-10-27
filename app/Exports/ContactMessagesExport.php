<?php

namespace App\Exports;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * @implements FromQuery<ContactMessage>
 */
class ContactMessagesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function query(): Builder
    {
        return ContactMessage::query()->latest();
    }

    public function headings(): array
    {
        return ['Date', 'Name', 'Email', 'Phone', 'Read', 'Message'];
    }

    /**
     * @param  ContactMessage  $message
     */
    public function map($message): array
    {
        return [
            $message->created_at->format('Y-m-d H:i'),
            $message->name,
            $message->email,
            $message->phone,
            $message->is_read ? 'yes' : 'no',
            $message->message,
        ];
    }
}
