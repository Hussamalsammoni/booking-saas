<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->subDays(30)->startOfDay();

        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        // ===== 1. الإيرادات =====
        $invoicesQuery = Invoice::whereBetween('created_at', [$from, $to])
            ->where('status', '!=', 'cancelled');

        $totalRevenue = (clone $invoicesQuery)->sum('amount');

       // نجيب الإيرادات الفعلية مجمعة حسب اليوم
$actualRevenue = (clone $invoicesQuery)
    ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
    ->groupBy('date')
    ->pluck('total', 'date');

// نملي كل يوم بالفترة (حتى لو صفر) عشان يطلع الرسم متصل وواضح
$revenueByDay = [];
$cursor = $from->copy()->startOfDay();

while ($cursor->lte($to)) {
    $dateKey = $cursor->toDateString();
    $revenueByDay[] = [
        'date' => $dateKey,
        'total' => (float) ($actualRevenue[$dateKey] ?? 0),
    ];
    $cursor->addDay();
}

        // ===== 2. أفضل الخدمات =====
        $topServices = Booking::whereBetween('bookings.created_at', [$from, $to])
            ->where('bookings.status', 'completed')
            ->join('services', 'services.id', '=', 'bookings.service_id')
            ->join('invoices', 'invoices.booking_id', '=', 'bookings.id')
            ->selectRaw('services.id, services.name, COUNT(bookings.id) as bookings_count, SUM(invoices.amount) as revenue')
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('bookings_count')
            ->limit(10)
            ->get();

        // ===== 3. أفضل الموظفين =====
        // ===== 3. أفضل الموظفين =====
    $topStaff = Booking::whereBetween('bookings.created_at', [$from, $to])
    ->where('bookings.status', 'completed')
    ->join('staff', 'staff.id', '=', 'bookings.staff_id')
    ->join('users', 'users.id', '=', 'staff.user_id')
    ->join('invoices', 'invoices.booking_id', '=', 'bookings.id')
    ->selectRaw('staff.id, users.name as name, staff.title, COUNT(bookings.id) as bookings_count, SUM(invoices.amount) as revenue')
    ->groupBy('staff.id', 'users.name', 'staff.title')
    ->orderByDesc('revenue')
    ->limit(10)
    ->get();

        // ===== 4. حالة الحجوزات =====
        $bookingStatusBreakdown = Booking::whereBetween('created_at', [$from, $to])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        return Inertia::render('Analytics/Index', [
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'totalRevenue' => $totalRevenue,
            'revenueByDay' => $revenueByDay,
            'topServices' => $topServices,
            'topStaff' => $topStaff,
            'bookingStatusBreakdown' => $bookingStatusBreakdown,
        ]);
    }
}