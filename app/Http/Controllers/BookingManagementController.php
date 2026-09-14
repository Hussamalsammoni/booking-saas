<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BookingManagementController extends Controller
{
    public function index()
    {
        return Inertia::render('Bookings/Index', [
            'bookings' => Booking::with(['staff.user', 'service'])
                ->orderBy('start_time', 'desc')
                ->get(),
        ]);
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

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

        // 3. الإرسال عبر UltraMsg
        try {
            $instanceId = env('ULTRAMSG_INSTANCE_ID', 'instance191104');
            $token      = env('ULTRAMSG_TOKEN', 'gnksggqeb76hdhdo');

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