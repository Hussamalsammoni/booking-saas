<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with('domains')->get()->map(function ($tenant) {
            return [
                'id' => $tenant->id,
                'shop_name' => $tenant->shop_name ?? $tenant->id,
                'domain' => optional($tenant->domains->first())->domain,
                'is_active' => $tenant->is_active,
                'created_at' => $tenant->created_at->format('Y-m-d'),
            ];
        });

        return Inertia::render('Admin/Dashboard', [
            'tenants' => $tenants,
        ]);
    }

    public function toggleActive(Tenant $tenant)
    {
        $tenant->update(['is_active' => !$tenant->is_active]);

        return back();
    }
}