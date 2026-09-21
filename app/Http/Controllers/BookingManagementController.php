<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Staff;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BookingManagementController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = Booking::with(['staff.user', 'service'])
            ->orderBy('start_time', 'desc');

        // إذا كان المستخدم موظف (مو owner)، يشوف حجوزاته فقط
        // إلا إذا كان عنده صلاحية can_view_all_bookings مفعّلة
        if ($user->role === 'staff') {
            $staff = $user->staff;

            if (! $staff || ! $staff->can_view_all_bookings) {
                $query->where('staff_id', $staff?->id ?? 0);
            }
        }

        return Inertia::render('Bookings/Index', [
            'bookings' => $query->get(),
            'services' => Service::where('is_active', true)->get(['id', 'name', 'duration_minutes', 'price']),
            'staffList' => Staff::with('user')->where('is_active', true)->get(['id', 'user_id', 'title', 'can_view_all_bookings']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'required|string|max:255',
            'service_id'     => 'required|exists:services,id',
            'staff_id'       => 'required|exists:staff,id',
            'start_time'     => 'required|date',
            'status'         => 'required|in:pending,confirmed,completed',
        ]);

        $user = auth()->user();
        $staffId = $request->staff_id;

        // إذا كان المستخدم موظف بدون صلاحية عرض الكل، يقدر يحجز لنفسه فقط
        if ($user->role === 'staff') {
            $staffSelf = $user->staff;

            if (! $staffSelf) {
                abort(403, 'لا يوجد حساب موظف مرتبط بهذا المستخدم.');
            }

            if (! $staffSelf->can_view_all_bookings) {
                $staffId = $staffSelf->id;
            }
        }

        $service = Service::findOrFail($request->service_id);

        $startTime = Carbon::parse($request->start_time);
        $endTime   = $startTime->clone()->addMinutes($service->duration_minutes);

        $conflict = Booking::where('staff_id', $staffId)
            ->where('status', '!=', 'cancelled')
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        if ($conflict) {
            return back()
                ->withErrors(['start_time' => 'هذا الموعد متعارض مع حجز آخر لنفس الموظف.'])
                ->withInput();
        }

        $booking = Booking::create([
            'customer_name'  => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'staff_id'       => $staffId,
            'service_id'     => $request->service_id,
            'start_time'     => $startTime->toDateTimeString(),
            'end_time'       => $endTime->toDateTimeString(),
            'status'         => $request->status,
        ]);

        // إنشاء فاتورة تلقائياً إذا كانت الحالة مكتملة مباشرة
        if ($request->status === 'completed' && ! $booking->invoice) {
            Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'booking_id'     => $booking->id,
                'amount'         => $service->price,
                'status'         => 'unpaid',
            ]);
        }

        return back()->with('success', 'تم إضافة الحجز اليدوي بنجاح.');
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        $user = auth()->user();

        // إذا كان المستخدم موظف بدون صلاحية عرض الكل، يقدر بس يعدل حجوزاته هو
        if ($user->role === 'staff') {
            $staff = $user->staff;

            if (! $staff || (! $staff->can_view_all_bookings && $booking->staff_id !== $staff->id)) {
                abort(403, 'لا يمكنك تعديل حجز لا يخصك.');
            }
        }

        $booking->update(['status' => $request->status]);

        // إرسال إشعار الواتساب عند تغيير الحالة (تأكيد أو إلغاء)
        if (in_array($request->status, ['confirmed', 'cancelled'])) {
            $this->sendStatusNotification($booking, $request->status);
        }

        // إنشاء فاتورة تلقائياً عند إتمام الحجز (إذا لم توجد مسبقاً)
        if ($request->status === 'completed' && !$booking->invoice) {
            \App\Models\Invoice::create([
                'invoice_number' => \App\Models\Invoice::generateInvoiceNumber(),
                'booking_id'     => $booking->id,
                'amount'         => $booking->service->price,
                'status'         => 'unpaid',
            ]);
        }

        return back()->with('success', 'تم تحديث حالة الحجز وإرسال الإشعار للعميل.');
    }

    private function sendStatusNotification(Booking $booking, string $status)
    {
        // تحميل العلاقات للتأكد من وجود البيانات
        $booking->load(['service', 'staff.user']);

        // 1. تنظيف الرقم وتجهيزه
        $phone = preg_replace('/[^0-9]/', '', $booking->customer_phone);
        if (str_starts_with($phone, '09')) {
            $phone = '963' . substr($phone, 1);
        }

        $serviceName   = $booking->service->name ?? 'الخدمة';
        $staffName     = $booking->staff->user->name ?? 'الموظف';
        $dateFormatted = Carbon::parse($booking->start_time)->format('Y-m-d');
        $timeFormatted = Carbon::parse($booking->start_time)->format('H:i');

        // 2. صياغة النص بناءً على الحالة
        if ($status === 'confirmed') {
            $message  = "تم تأكيد حجزك بنجاح! 🎉\n\n";
            $message .= "مرحباً {$booking->customer_name} 👋\n";
            $message .= "يسعدنا إبلاغك بأنه تم تأكيد موعدك بنجاح ✨\n\n";
            $message .= "تفاصيل الموعد المؤكد:\n";
            $message .= "🔹 الخدمة: {$serviceName}\n";
            $message .= "🔹 الموظف: {$staffName}\n";
            $message .= "📅 التاريخ: {$dateFormatted}\n";
            $message .= "⏰ الوقت: {$timeFormatted}\n\n";
            $message .= "بانتظارك في الوقت المحدد! ❤️";
        } else {
            $message  = "تحديث بشأن حجزك ⚠️\n\n";
            $message .= "مرحباً {$booking->customer_name}، نعتذر منك، تم إلغاء حجزك لليوم ({$dateFormatted}) الساعة ({$timeFormatted}).\n";
            $message .= "يمكنك التواصل معنا لإعادة الجدولة أو اختيار موعد آخر.";
        }

        // 3. الإرسال عبر UltraMsg (المفاتيح لازم تكون بملف .env فقط، بدون قيم افتراضية بالكود)
        $instanceId = env('ULTRAMSG_INSTANCE_ID');
        $token      = env('ULTRAMSG_TOKEN');

        if (! $instanceId || ! $token) {
            Log::warning('UltraMsg keys are missing in .env, WhatsApp message not sent.');
            return;
        }

        try {
            $response = Http::asForm()->post("https://api.ultramsg.com/{$instanceId}/messages/chat", [
                'token' => $token,
                'to'    => $phone,
                'body'  => $message,
            ]);

            Log::info('Status Update UltraMsg Response: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Status Update WhatsApp Error: ' . $e->getMessage());
        }
    }
}