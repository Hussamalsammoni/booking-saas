<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Staff;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();

        $todayBookings = Booking::whereDate('start_time', $today)->count();

        $completedBookings = Booking::where('status', 'completed')->count();

        $cancelledBookings = Booking::where('status', 'cancelled')->count();

        $confirmedBookings = Booking::where('status', 'confirmed')->count();

        $pendingBookings = Booking::where('status', 'pending')->count();

        $revenue = Booking::where('status', 'completed')
            ->with('service')
            ->get()
            ->sum(function ($booking) {
                return (float) ($booking->service?->price ?? 0);
            });

        $upcomingBookings = Booking::with([
                'customer',
                'staff.user',
                'service',
            ])
            ->where('start_time', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'todayBookings' => $todayBookings,
                'totalServices' => Service::count(),
                'totalStaff' => Staff::count(),

                'completedBookings' => $completedBookings,
                'cancelledBookings' => $cancelledBookings,
                'confirmedBookings' => $confirmedBookings,
                'pendingBookings' => $pendingBookings,

                'revenue' => $revenue,
            ],

            'upcomingBookings' => $upcomingBookings,
        ]);
    }
}