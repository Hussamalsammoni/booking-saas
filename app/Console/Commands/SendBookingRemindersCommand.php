<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Tenant;
use App\Jobs\SendBookingReminderJob;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendBookingRemindersCommand extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'فحص الحجوزات القادمة وإرسال التذكيرات للعملاء عبر الواتساب لجميع المحلات';

    public function handle()
    {
        $totalReminders = 0;

        Tenant::all()->each(function (Tenant $tenant) use (&$totalReminders) {
            $tenant->run(function () use (&$totalReminders, $tenant) {

                // البحث عن الحجوزات المؤكدة خلال الـ 24 ساعة القادمة بحسب start_time
                $upcomingBookings = Booking::where('status', 'confirmed')
                    ->where('reminder_sent', false)
                    ->whereBetween('start_time', [
                        Carbon::now(),
                        Carbon::now()->addHours(24)
                    ])
                    ->get();

                foreach ($upcomingBookings as $booking) {
                    SendBookingReminderJob::dispatch($booking);
                    $totalReminders++;
                }

                if ($upcomingBookings->isNotEmpty()) {
                    $this->info("تم معالجة " . $upcomingBookings->count() . " تذكير للمحل: {$tenant->id}");
                }
            });
        });

        if ($totalReminders === 0) {
            $this->info('لا توجد حجوزات قادمة بحاجة لإرسال تذكير حالياً في أي محل.');
        } else {
            $this->info("تم إرسال إجمالي {$totalReminders} تذكير بنجاح عبر جميع المحلات.");
        }
    }
}