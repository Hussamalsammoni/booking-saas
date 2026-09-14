<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendPostBookingFollowup extends Command
{
    protected $signature = 'booking:send-followup';
    protected $description = 'إرسال رسالة شكر وتقييم بعد انتهاء الموعد بساعة لجميع المستأجرين';

    public function handle()
    {
        Tenant::all()->runForEach(function () {
            $now = Carbon::now();
            // البحث عن الحجوزات التي انتهت قبل 30 إلى 90 دقيقة
            $startWindow = $now->copy()->subMinutes(90);
            $endWindow   = $now->copy()->subMinutes(30);

            $bookings = Booking::where('status', 'confirmed')
                ->where('followup_sent', false) // ضمان عدم تكرار الإرسال
                ->where('end_time', '>=', $startWindow)
                ->where('end_time', '<=', $endWindow)
                ->get();

            foreach ($bookings as $booking) {
                $this->sendFollowup($booking);
            }
        });

        $this->info("تم معالجة المتابعات والتقييمات بنجاح.");
    }

    private function sendFollowup($booking)
    {
        $phone = preg_replace('/[^0-9]/', '', $booking->customer_phone);
        if (str_starts_with($phone, '09')) {
            $phone = '963' . substr($phone, 1);
        }

        $message  = "شكراً لزيارتك لنا اليوم! ❤️\n\n";
        $message .= "مرحباً {$booking->customer_name} 👋\n";
        $message .= "نتمنى أن تكون قد حصلت على خدمة ممتازة، يسعدنا جداً سماع رأيك أو تقييمك لتجربتك معنا ✨";

        $instanceId = env('ULTRAMSG_INSTANCE_ID', 'instance191104');
        $token      = env('ULTRAMSG_TOKEN', 'gnksggqeb76hdhdo');

        try {
            $response = Http::asForm()->post("https://api.ultramsg.com/{$instanceId}/messages/chat", [
                'token' => $token,
                'to'    => $phone,
                'body'  => $message,
            ]);

            if ($response->successful()) {
                // تحديث العمود كي لا تتكرر الرسالة في الفحص القادم
                $booking->update(['followup_sent' => true]);
                Log::info("Followup sent successfully to {$phone}");
            }
        } catch (\Exception $e) {
            Log::error('Followup Sending Error: ' . $e->getMessage());
        }
    }
}