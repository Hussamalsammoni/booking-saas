<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    public function index()
    {
        return response()->json([
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