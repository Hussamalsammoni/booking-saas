<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 1. تشغيل أمر إرسال التذكيرات قبل الموعد بساعة (يفحص كل دقيقة)
        $schedule->command('booking:send-reminders')->everyMinute();

        // 2. تشغيل أمر إرسال رسائل الشكر والتقييم بعد الموعد (يفحص كل نصف ساعة)
        $schedule->command('booking:send-followup')->everyThirtyMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}