<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::now()->startOfDay();

        $tenants = Tenant::with('domains')->get()->map(function ($tenant) use ($today) {
            $endsAt = $tenant->subscription_ends_at;

            // عدد الأيام المتبقية (سالب = منتهي). null = اشتراك بدون تاريخ انتهاء
            $daysLeft = $endsAt
                ? (int) $today->diffInDays(Carbon::parse($endsAt)->startOfDay(), false)
                : null;

            if (! $tenant->is_active) {
                $status = 'disabled';
            } elseif ($daysLeft !== null && $daysLeft < 0) {
                $status = 'expired';
            } elseif ($daysLeft !== null && $daysLeft <= 7) {
                $status = 'expiring';
            } else {
                $status = 'active';
            }

            return [
                'id'                   => $tenant->id,
                'shop_name'            => $tenant->shop_name ?? $tenant->id,
                'domain'               => optional($tenant->domains->first())->domain,
                'is_active'            => $tenant->is_active,
                'created_at'           => $tenant->created_at->format('Y-m-d'),
                'subscription_ends_at' => $endsAt ? Carbon::parse($endsAt)->format('Y-m-d') : null,
                'days_left'            => $daysLeft,
                'status'               => $status,
                'bookings_count'       => $this->bookingsCount($tenant),
            ];
        })->values();

        return Inertia::render('Admin/Dashboard', [
            'tenants' => $tenants,
            'stats'   => [
                'total'          => $tenants->count(),
                'active'         => $tenants->where('status', 'active')->count(),
                'expiring'       => $tenants->where('status', 'expiring')->count(),
                'expired'        => $tenants->where('status', 'expired')->count(),
                'disabled'       => $tenants->where('status', 'disabled')->count(),
                'total_bookings' => $tenants->sum('bookings_count'),
            ],
        ]);
    }

    public function toggleActive(Tenant $tenant)
    {
        $tenant->update(['is_active' => ! $tenant->is_active]);

        return back();
    }

    /**
     * تمديد اشتراك محل: بيبدأ العد من تاريخ الانتهاء الحالي إذا لسا ما انتهى،
     * أو من اليوم إذا انتهى أو ما إلو تاريخ.
     */
    public function extendSubscription(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'days' => 'required|integer|min:1|max:730',
        ]);

        $current = $tenant->subscription_ends_at
            ? Carbon::parse($tenant->subscription_ends_at)
            : null;

        $base = ($current && $current->isFuture()) ? $current : Carbon::now();

        $tenant->update([
            'subscription_ends_at' => $base->copy()->addDays((int) $data['days'])->toDateTimeString(),
        ]);

        Cache::forget($this->cacheKey($tenant));

        return back()->with('success', 'تم تمديد اشتراك المحل بنجاح.');
    }

    /**
     * عدد حجوزات المحل (مخزّن 5 دقائق حتى ما نفتح كل قواعد البيانات بكل زيارة).
     */
    private function bookingsCount(Tenant $tenant): int
    {
        try {
            return (int) Cache::remember($this->cacheKey($tenant), 300, function () use ($tenant) {
                return $tenant->run(fn () => \App\Models\Booking::count());
            });
        } catch (\Throwable $e) {
            Log::warning("Could not count bookings for tenant {$tenant->id}: " . $e->getMessage());

            return 0;
        }
    }

    private function cacheKey(Tenant $tenant): string
    {
        return "admin:tenant:{$tenant->id}:bookings_count";
    }
}