<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Notification;

class NewBookingNotification extends Notification
{
    public function __construct(public Booking $booking)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $b = $this->booking;

        $name    = $b->customer_name ?: 'زبون';
        $service = $b->service?->name;                       // لو عمود اسم الخدمة مختلف عدّلو
        $time    = $b->start_time?->format('Y/m/d - H:i');

        $message = "حجز جديد من {$name}";
        if ($service) {
            $message .= " — {$service}";
        }
        if ($time) {
            $message .= " ({$time})";
        }

        return [
            'booking_id' => $b->id,
            'title'      => 'حجز جديد',
            'message'    => $message,
            'url'        => '/bookings',
        ];
    }
}