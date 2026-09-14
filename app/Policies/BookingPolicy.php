<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * هل يقدر يشوف قائمة الحجوزات (منطق الفلترة الفعلي بالـ Controller)
     */
    public function viewAny(User $user): bool
    {
        return true; // الكل يقدر يفتح الصفحة، الفلترة صايرة بالـ Controller
    }

    /**
     * هل يقدر يشوف حجز معيّن بالتفصيل
     */
    public function view(User $user, Booking $booking): bool
    {
        if ($user->role === 'owner') {
            return true;
        }

        $staff = $user->staff;

        if (!$staff) {
            return false;
        }

        if ($staff->can_view_all_bookings) {
            return true;
        }

        return $booking->staff_id === $staff->id;
    }

    public function create(User $user): bool
    {
        return true; // owner وstaff الاثنين يقدروا يعملوا حجز جديد
    }

    public function update(User $user, Booking $booking): bool
    {
        return $this->view($user, $booking);
    }

    public function delete(User $user, Booking $booking): bool
    {
        return $user->role === 'owner'; // بس صاحب المحل يحذف حجز
    }
}