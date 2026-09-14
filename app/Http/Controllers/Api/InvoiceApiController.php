<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvoiceApiController extends Controller
{
    public function index(Request $request)
    {
         $this->authorize('viewAny', Invoice::class);
        $query = Invoice::with(['booking.customer', 'booking.staff.user', 'booking.service']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json(
            $query->orderBy('created_at', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
          $this->authorize('create', Invoice::class);
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id|unique:invoices,booking_id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:100',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        $invoice = Invoice::create([
            ...$validated,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'status' => 'unpaid',
        ]);

        return response()->json($invoice->load('booking'), 201);
    }

    public function show(Invoice $invoice)
    {
         $this->authorize('view', $invoice);

        return response()->json(
            $invoice->load(['booking.customer', 'booking.staff.user', 'booking.service'])
        );
    }

    public function markAsPaid(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'payment_method' => 'nullable|string|max:100',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        $invoice->update([
            ...$validated,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return response()->json($invoice->load('booking'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        $validated = $request->validate([
            'amount' => 'sometimes|required|numeric|min:0',
            'payment_method' => 'sometimes|nullable|string|max:100',
            'payment_reference' => 'sometimes|nullable|string|max:255',
            'status' => ['sometimes', Rule::in(['unpaid', 'paid', 'cancelled'])],
        ]);

        $invoice->update($validated);

        return response()->json($invoice->load('booking'));
    }

public function destroy(Invoice $invoice)
{
    $this->authorize('delete', $invoice);

    $invoice->delete();

    return response()->json(['message' => 'تم حذف الفاتورة بنجاح']);
}
}