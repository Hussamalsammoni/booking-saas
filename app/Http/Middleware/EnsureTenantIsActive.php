<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class EnsureTenantIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if ($tenant) {
            // 1. الفحص المباشر لخاصية التعطيل اليدوي
            if (isset($tenant->is_active) && !$tenant->is_active) {
                abort(503, 'هذا المحل معطّل حالياً. تواصل مع الدعم الفني.');
            }

            // 2. فحص انتهاء مدة الاشتراك
            if (!empty($tenant->subscription_ends_at)) {
                if (Carbon::now()->greaterThan(Carbon::parse($tenant->subscription_ends_at))) {
                    abort(503, 'انتهت فترة اشتراك هذا المحل. يرجى التواصل مع الإدارة للتجديد.');
                }
            }
        }

        return $next($request);
    }
}