<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingApiController extends Controller
{
   public function index(Request $request)
{
    $user = $request->user();
    $query = Booking::with(['customer', 'staff.user', 'service']);

    // فلترة حسب الصلاحية
    if ($user->role !== 'owner') {
        $staff = $user->staff;
        if ($staff && !$staff->can_view_all_bookings) {
            $query->where('staff_id', $staff->id);
        }
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('staff_id')) {
        $query->where('staff_id', $request->staff_id);
    }

    if ($request->boolean('upcoming')) {
        $query->where('start_time', '>=', now());
    }

    return response()->json(
        $query->orderBy('start_time')->get()
    );
}
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:users,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'staff_id' => 'required|exists:staff,id',
            'service_id' => 'required|exists:services,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'notes' => 'nullable|string',
        ]);

        // تحقق من عدم تعارض الحجز مع حجز آخر لنفس الموظف
        $conflict = Booking::where('staff_id', $validated['staff_id'])
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('start_time', '<=', $validated['start_time'])
                         ->where('end_time', '>=', $validated['end_time']);
                  });
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'هذا الموعد محجوز مسبقاً لنفس الموظف.',
            ], 422);
        }

        $booking = Booking::create([
            ...$validated,
            'status' => 'pending',
        ]);

        return response()->json($booking->load(['customer', 'staff.user', 'service']), 201);
    }

   public function show(Request $request, Booking $booking)
{
    $this->authorize('view', $booking);

    return response()->json($booking->load(['customer', 'staff.user', 'service', 'invoice']));
}


   public function updateStatus(Request $request, Booking $booking)
{
    $this->authorize('update', $booking);

    $validated = $request->validate([
        'status' => ['required', Rule::in(['pending', 'confirmed', 'cancelled', 'completed'])],
    ]);

    $booking->update($validated);

    return response()->json($booking->load(['customer', 'staff.user', 'service']));
}

public function destroy(Request $request, Booking $booking)
{
    $this->authorize('delete', $booking);

    $booking->delete();

    return response()->json(['message' => 'تم حذف الحجز بنجاح']);
}
}