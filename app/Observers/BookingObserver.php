<?php

namespace App\Observers;

use App\Models\Booking;
use App\Models\User;
use App\Notifications\NewBookingNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BookingObserver
{
    /**
     * بيشتغل تلقائياً كل ما انعمل حجز جديد، من أي مصدر:
     * صفحة /book العامة، لوحة التحكم، أو الـ API.
     */
    public function created(Booking $booking): void
    {
        $this->sendDashboardNotifications($booking);
        $this->sendOwnerWhatsApp($booking);
    }

    /**
     * إشعار الجرس بلوحة التحكم (لصاحب المحل + الموظف المحجوز عنده).
     */
    private function sendDashboardNotifications(Booking $booking): void
    {
        // أي خطأ بالإشعار (مثلاً الجدول مو موجود) ما لازم يخرّب حجز الزبون
        try {
            $recipients = User::where('role', 'owner')->get();

            $staffUser = $booking->staff?->user;
            if ($staffUser && !$recipients->contains('id', $staffUser->id)) {
                $recipients->push($staffUser);
            }

            foreach ($recipients as $user) {
                $user->notify(new NewBookingNotification($booking));
            }
        } catch (\Throwable $e) {
            Log::error('Booking notification failed: ' . $e->getMessage());
        }
    }

    /**
     * رسالة واتساب لصاحب المحل على الرقم المحفوظ بإعدادات المحل.
     */
    private function sendOwnerWhatsApp(Booking $booking): void
    {
        try {
            // إذا صاحب المحل هو نفسو أضاف الحجز يدوياً من لوحة التحكم، ما في داعي نبعتلو
            if (request()->routeIs('bookings.store')) {
                return;
            }

            $ownerPhone = preg_replace('/\D/', '', (string) tenant('phone'));
            if (str_starts_with($ownerPhone, '09')) {
                $ownerPhone = '963' . substr($ownerPhone, 1);
            }

            $instanceId = env('ULTRAMSG_INSTANCE_ID');
            $token      = env('ULTRAMSG_TOKEN');

            // بدون رقم للمحل أو بدون مفاتيح UltraMsg، منتخطى الإرسال بصمت
            if (! $ownerPhone || ! $instanceId || ! $token) {
                return;
            }

            $booking->loadMissing(['service', 'staff.user']);

            $start = Carbon::parse($booking->start_time);

            $message  = "🔔 حجز جديد بانتظار التأكيد\n\n";
            $message .= "👤 الزبون: {$booking->customer_name}\n";
            $message .= "📞 الهاتف: {$booking->customer_phone}\n";
            $message .= "🔹 الخدمة: " . ($booking->service->name ?? '-') . "\n";
            $message .= "🔹 الموظف: " . ($booking->staff->user->name ?? '-') . "\n";
            $message .= "📅 التاريخ: " . $start->format('Y-m-d') . "\n";
            $message .= "⏰ الوقت: " . $start->format('H:i') . "\n\n";
            $message .= "ادخل للوحة التحكم لتأكيد الحجز أو إلغائه.";

            Http::asForm()
                ->timeout(5)
                ->post("https://api.ultramsg.com/{$instanceId}/messages/chat", [
                    'token' => $token,
                    'to'    => $ownerPhone,
                    'body'  => $message,
                ]);
        } catch (\Throwable $e) {
            Log::error('Owner WhatsApp notification failed: ' . $e->getMessage());
        }
    }
}