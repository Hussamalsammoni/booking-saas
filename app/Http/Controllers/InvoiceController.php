<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index()
    {
        return Inertia::render('Invoices/Index', [
            'invoices' => Invoice::with(['booking.customer', 'booking.service', 'booking.staff.user'])
                ->orderBy('created_at', 'desc')
                ->get(),
        ]);
    }

    public function markAsPaid(Request $request, Invoice $invoice)
{
    $request->validate([
        'payment_method' => 'required|in:cash,card,sham_cash',
        'payment_reference' => 'nullable|string|max:255',
    ]);

    $invoice->update([
        'status' => 'paid',
        'payment_method' => $request->payment_method,
        'payment_reference' => $request->payment_reference,
        'paid_at' => now(),
    ]);

    return back()->with('success', 'تم تأكيد استلام الدفعة');
}

public function update(Request $request, Invoice $invoice)
{
    $request->validate([
        'amount' => 'required|numeric|min:0',
        'status' => 'required|in:unpaid,paid',
        'payment_method' => 'required|in:cash,card,sham_cash',
        'payment_reference' => 'nullable|string|max:255',
    ]);

    $invoice->update([
        'amount' => $request->amount,
        'status' => $request->status,
        'payment_method' => $request->payment_method,
        'payment_reference' => $request->payment_reference,
        'paid_at' => $request->status === 'paid' ? ($invoice->paid_at ?? now()) : null,
    ]);

    return back()->with('success', 'تم تحديث الفاتورة بنجاح');
}

public function destroy(Invoice $invoice)
{
    $invoice->delete();

    return back()->with('success', 'تم إلغاء الفاتورة');
}
}