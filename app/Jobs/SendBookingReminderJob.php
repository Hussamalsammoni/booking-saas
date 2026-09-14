<?php

namespace App\Jobs;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendBookingReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function handle(): void
    {
        if ($this->booking->status !== 'confirmed') {
            return;
        }

        // جلب بيانات العميل والخدمة وتنسيق وقت start_time
        $customerPhone = $this->booking->customer->phone ?? $this->booking->customer_phone ?? '';
        $customerName  = $this->booking->customer->name ?? $this->booking->customer_name ?? 'عميلنا العزيز';
        $serviceName   = $this->booking->service->name ?? 'خدمة';
        $bookingTime   = Carbon::parse($this->booking->start_time)->format('Y-m-d H:i');

        if (empty($customerPhone)) {
            Log::warning("Skipped booking #{$this->booking->id}: No phone number found.");
            return;
        }

        $formattedPhone = $this->formatPhoneNumber($customerPhone);
        $message = "مرحباً {$customerName}، نذكرك بموعدك القادم لخدمة ({$serviceName}) بتاريخ {$bookingTime}. نتطلع لرؤيتك!";

        $instanceId = config('services.ultramsg.instance_id');
        $token      = config('services.ultramsg.token');

        try {
            $response = Http::asForm()->post("https://api.ultramsg.com/{$instanceId}/messages/chat", [
                'token' => $token,
                'to'    => $formattedPhone,
                'body'  => $message,
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp reminder sent successfully to {$formattedPhone}");
                $this->booking->update(['reminder_sent' => true]);
            } else {
                Log::error("UltraMsg API Error: " . $response->body());
            }

        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp reminder: " . $e->getMessage());
        }
    }

    private function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '09')) {
            return '+963' . substr($phone, 1);
        }

        if (str_starts_with($phone, '9') && strlen($phone) === 9) {
            return '+963' . $phone;
        }

        return '+' . $phone;
    }
}