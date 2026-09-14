<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AvailabilityController extends Controller
{
    public function getSlots(Request $request)
    {
        $request->validate([
            'staff_id'   => 'required|exists:staff,id',
            'service_id' => 'required|exists:services,id',
            'date'       => 'required|date',
        ]);

        $staff   = Staff::findOrFail($request->staff_id);
        $service = Service::findOrFail($request->service_id);
        $date    = Carbon::parse($request->date);

        // تحويل working_hours في حال كانت نصية
        $workingHoursData = is_string($staff->working_hours) 
            ? json_decode($staff->working_hours, true) 
            : $staff->working_hours;

        $dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $dayKey   = $dayNames[$date->dayOfWeek];

        $workingHours = $workingHoursData[$dayKey] ?? null;

        // تعيين قيم افتراضية في حال عدم ضبط ساعات العمل
        $startStr = $workingHours['start'] ?? '09:00';
        $endStr   = $workingHours['end'] ?? '21:00';
        $enabled  = $workingHours['enabled'] ?? true;

        if (!$enabled) {
            return response()->json(['slots' => []]);
        }

        $duration  = $service->duration_minutes ?? 30;
        $slotStart = Carbon::parse($date->format('Y-m-d') . ' ' . $startStr);
        $dayEnd    = Carbon::parse($date->format('Y-m-d') . ' ' . $endStr);

        // جلب الحجوزات اليومية
        $existingBookings = Booking::where('staff_id', $staff->id)
            ->where('status', '!=', 'cancelled')
            ->whereDate('start_time', $date->format('Y-m-d'))
            ->get();

        $slots = [];

        while ($slotStart->copy()->addMinutes($duration)->lte($dayEnd)) {
            $slotEnd = $slotStart->copy()->addMinutes($duration);

            $hasConflict = $existingBookings->contains(function ($booking) use ($slotStart, $slotEnd) {
                $bStart = Carbon::parse($booking->start_time);
                $bEnd   = Carbon::parse($booking->end_time);
                return $slotStart < $bEnd && $slotEnd > $bStart;
            });

            if (!$hasConflict) {
                $slots[] = $slotStart->format('H:i');
            }

            $slotStart->addMinutes(30); // توليد خانة كل 30 دقيقة
        }

        return response()->json(['slots' => $slots]);
    }
}