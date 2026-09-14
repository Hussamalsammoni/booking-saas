<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendBookingReminders extends Command
{
    /**
     * اسم الأمر الذي سيتم تشغيله
     */
    protected $signature = 'booking:send-reminders';

    /**
     * وصف الأمر
     */
    protected $description = 'إرسال تذكيرات الواتساب للحجوزات المؤكدة قبل الموعد بساعة لجميع المستأجرين';

    public function handle()
    {
        // الدوران على كافة المستأجرين لتطبيق الاستعلام في قواعد بياناتهم
        Tenant::all()->runForEach(function () {
            $now = Carbon::now();
            $startWindow = $now->copy()->addMinutes(55);
            $endWindow   = $now->copy()->addMinutes(65);

            // جلب الحجوزات المؤكدة (confirmed) فقط والتي لم يُرسل لها تذكير بعد
            $bookings = Booking::with(['service', 'staff.user'])
                ->where('status', 'confirmed') // حصراً للحجوزات المؤكدة من لوحة التحكم
                ->where('reminder_sent', false)
                ->whereBetween('start_time', [$startWindow, $endWindow])
                ->get();

            foreach ($bookings as $booking) {
                $this->sendReminder($booking);
            }
        });

        $this->info("تم معالجة التذكيرات للحجوزات المؤكدة لجميع المستأجرين بنجاح.");
    }

    private function sendReminder($booking)
    {
        // تنظيف وتجهيز رقم الهاتف
        $phone = preg_replace('/[^0-9]/', '', $booking->customer_phone);
        if (str_starts_with($phone, '09')) {
            $phone = '963' . substr($phone, 1);
        }

        $serviceName = $booking->service->name ?? 'الخدمة';
        $staffName   = $booking->staff->user->name ?? 'الموظف';
        $timeFormatted = Carbon::parse($booking->start_time)->format('H:i');

        // نص رسالة التذكير
        $message  = "تذكير بموعدك ⏰\n\n";
        $message .= "مرحباً {$booking->customer_name} 👋\n";
        $message .= "نود تذكيرك بأن موعدك بعد ساعة من الآن!\n\n";
        $message .= "🔹 الخدمة: {$serviceName}\n";
        $message .= "🔹 الموظف: {$staffName}\n";
        $message .= "⏰ الوقت: {$timeFormatted}\n\n";
        $message .= "بانتظارك في الوقت المحدد! ❤️";

        try {
           $response = Http::asForm()->post('https://api.ultramsg.com/' . env('ULTRAMSG_INSTANCE_ID') . '/messages/chat', [
    'token' => env('ULTRAMSG_TOKEN'),
    'to'    => $phone,
    'body'  => $message,
]);

            if ($response->successful()) {
                // تحديث قاعدة البيانات كي لا يتكرر التذكير
                $booking->update(['reminder_sent' => true]);
                Log::info("Reminder sent successfully to {$phone}");
            }
        } catch (\Exception $e) {
            Log::error('Reminder Sending Error: ' . $e->getMessage());
        }
    }
}