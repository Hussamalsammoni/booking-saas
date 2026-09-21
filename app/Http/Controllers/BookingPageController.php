<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class BookingPageController extends Controller
{
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'staff_id'   => 'required|exists:staff,id',
            'service_id' => 'required|exists:services,id',
            'date'       => 'required|date_format:Y-m-d',
        ]);

        $service  = Service::findOrFail($request->service_id);
        $duration = $service->duration_minutes;

        $workStart = Carbon::parse($request->date . ' 09:00:00');
        $workEnd   = Carbon::parse($request->date . ' 18:00:00');

        $existingBookings = Booking::where('staff_id', $request->staff_id)
            ->where('status', '!=', 'cancelled')
            ->whereDate('start_time', $request->date)
            ->get();

        $slots = [];
        $currentSlot = $workStart->clone();

        while ($currentSlot->clone()->addMinutes($duration)->lte($workEnd)) {
            $slotStart = $currentSlot->clone();
            $slotEnd   = $currentSlot->clone()->addMinutes($duration);

            $isOverlap = $existingBookings->contains(function ($booking) use ($slotStart, $slotEnd) {
                $bStart = Carbon::parse($booking->start_time);
                $bEnd   = Carbon::parse($booking->end_time);
                return $slotStart->lt($bEnd) && $slotEnd->gt($bStart);
            });

            if (!$isOverlap && $slotStart->gt(Carbon::now())) {
                $slots[] = [
                    'time'      => $slotStart->format('H:i'),
                    'full_date' => $slotStart->format('Y-m-d H:i:s'),
                    'formatted' => $slotStart->format('h:i A'),
                ];
            }

            $currentSlot->addMinutes(30);
        }

        return response()->json($slots);
    }

    public function index()
    {
        $t = tenant();

        return Inertia::render('Booking/Show', [
            'services' => Service::where('is_active', true)->get(),
            'staff'    => Staff::with('user', 'services')->where('is_active', true)->get(),
            'shop'     => [
                'name'            => $t->shop_name,
                'description'     => $t->description,
                'primary_color'   => $t->primary_color ?? '#ff0569',
                'secondary_color' => $t->secondary_color ?? '#1E1E24',
                'bg_color'        => $t->bg_color ?? '#F9F8F6',
                'text_color'      => $t->text_color ?? '#2D2D2D',
                'logo'            => $t->logo_path ? tenant_asset($t->logo_path) : null,
                'cover'           => $t->cover_path ? tenant_asset($t->cover_path) : null,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'required|string|max:255',
            'service_id'     => 'required|exists:services,id',
            'staff_id'       => 'required|exists:staff,id',
            'start_time'     => 'required|date|after:now',
        ]);

        $service = Service::findOrFail($request->service_id);
        $staff   = Staff::with('user')->findOrFail($request->staff_id);

        $bufferMinutes = 5;
        $startTime = Carbon::parse($request->start_time);
        $endTime   = $startTime->clone()->addMinutes($service->duration_minutes);

        $bufferedStart = $startTime->clone()->subMinutes($bufferMinutes);
        $bufferedEnd   = $endTime->clone()->addMinutes($bufferMinutes);

        $conflict = Booking::where('staff_id', $request->staff_id)
            ->where('status', '!=', 'cancelled')
            ->where('start_time', '<', $bufferedEnd)
            ->where('end_time', '>', $bufferedStart)
            ->exists();

        if ($conflict) {
            return back()->withErrors(['start_time' => 'هذا الموعد محجوز مسبقاً، يرجى اختيار وقت آخر.']);
        }

        $booking = Booking::create([
            'customer_name'  => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'staff_id'       => $request->staff_id,
            'service_id'     => $request->service_id,
            'start_time'     => $startTime->toDateTimeString(),
            'end_time'       => $endTime->toDateTimeString(),
            'status'         => 'pending',
        ]);

        // إرسال إشعار الواتساب المبدئي (قيد الانتظار)
        $this->sendWhatsAppNotification($booking, $service, $staff);

        // التوجيه لصفحة النجاح عبر رابط موقّع (ما حدا بيقدر يخمّن أرقام الحجوزات)
        return redirect()->to(
            URL::signedRoute('booking.success', ['booking' => $booking->id])
        );
    }

    public function success(Booking $booking)
    {
        $booking->load(['service', 'staff.user']);

        // رقم واتساب المحل بصيغة دولية. إذا مو محدد منرجع null والزر بيختفي من الصفحة
        $businessPhone = preg_replace('/\D/', '', (string) tenant('phone'));
        if (str_starts_with($businessPhone, '09')) {
            $businessPhone = '963' . substr($businessPhone, 1);
        }

        return Inertia::render('Booking/Success', [
            'booking' => [
                'id'             => $booking->id,
                'customer_name'  => $booking->customer_name,
                'service_name'   => $booking->service->name ?? 'خدمة',
                'staff_name'     => $booking->staff->user->name ?? 'الموظف',
                'date'           => Carbon::parse($booking->start_time)->format('Y-m-d'),
                'time'           => Carbon::parse($booking->start_time)->format('h:i A'),
                'business_phone' => $businessPhone ?: null,
            ]
        ]);
    }

    private function sendWhatsAppNotification($booking, $service, $staff)
    {
        $phone = preg_replace('/[^0-9]/', '', $booking->customer_phone);

        if (str_starts_with($phone, '09')) {
            $phone = '963' . substr($phone, 1);
        }

        $startCarbon = Carbon::parse($booking->start_time);

        // النص يوضح أن الطلب قيد المراجعة والانتظار
        $message  = "مرحباً {$booking->customer_name} 👋\n\n";
        $message .= "تلقينا طلب حجزك بنجاح وهو الآن (قيد المراجعة) ⏳\n\n";
        $message .= "تفاصيل الطلب:\n";
        $message .= "🔹 الخدمة: {$service->name}\n";
        $message .= "🔹 الموظف: " . ($staff->user->name ?? 'الموظف') . "\n";
        $message .= "📅 التاريخ: " . $startCarbon->format('Y-m-d') . "\n";
        $message .= "⏰ الوقت: " . $startCarbon->format('H:i') . "\n\n";
        $message .= "سيصلك إشعار فور تأكيد الحجز من لوحة التحكم. شكراً لاختيارك لنا! ❤️";

        try {
            $response = Http::asForm()->post('https://api.ultramsg.com/' . env('ULTRAMSG_INSTANCE_ID') . '/messages/chat', [
                'token' => env('ULTRAMSG_TOKEN'),
                'to'    => $phone,
                'body'  => $message,
            ]);

            Log::info('UltraMsg Response: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('UltraMsg Notification Error: ' . $e->getMessage());
        }
    }
}