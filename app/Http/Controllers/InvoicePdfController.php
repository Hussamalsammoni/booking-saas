<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Mpdf\Mpdf;

class InvoicePdfController extends Controller
{
    public function download(Invoice $invoice)
    {
        $invoice->load([
            'booking.customer',
            'booking.service',
            'booking.staff.user',
        ]);

        // التهيئة الحديثة المتوافقة مع الإصدار 8.x و PHP 8.2
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'amiri',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);

        $html = view('invoices.pdf', [
            'invoice'  => $invoice,
            'shopName' => tenant('shop_name') ?? tenant('id'),
        ])->render();

        $mpdf->WriteHTML($html);

        return response()->streamDownload(
            fn () => print($mpdf->Output('', 'S')),
            $invoice->invoice_number . '.pdf'
        );
    }
}