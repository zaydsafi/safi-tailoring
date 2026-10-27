<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Builds the invoice / receipt PDF for an order.
 *
 * The document is always rendered in English with explicit formatting
 * (dates, numbers) because DomPDF has no reliable fonts for the site's
 * RTL locales; the data itself is language-neutral.
 */
class InvoicePdf
{
    public static function for(Order $order): \Barryvdh\DomPDF\PDF
    {
        $order->loadMissing(['items', 'statusHistories']);

        return Pdf::loadView('pdf.invoice', [
            'order' => $order,
            'shopName' => Setting::get('shop_name', config('app.name')),
            'shopPhone' => Setting::get('contact_phone', ''),
            'shopEmail' => Setting::get('contact_email', ''),
            'shopAddress' => Setting::get('shop_address', ''),
            'currency' => Setting::get('currency_symbol', 'AFN'),
            'footerNote' => Setting::get('invoice_footer_note', 'Thank you for your business!'),
        ])->setPaper('a4');
    }
}
