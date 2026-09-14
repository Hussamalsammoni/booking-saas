<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'todayBookings' => Booking::whereDate('start_time', today())->count(),
                'totalServices' => Service::count(),
                'totalStaff' => Staff::count(),
            ],
            'upcomingBookings' => Booking::with(['customer', 'staff.user', 'service'])
                ->where('start_time', '>=', now())
                ->orderBy('start_time')
                ->limit(5)
                ->get(),
        ]);
    }
}