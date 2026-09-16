<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ShopSettingsController extends Controller
{
    public function edit()
    {
        $t = tenant();

        return Inertia::render('ShopSettings/Edit', [
            'shop' => [
                'shop_name' => $t->shop_name,
                'description' => $t->description,
                'primary_color' => $t->primary_color ?? '#4f46e5',
                'logo_url' => $t->logo_path ? tenant_asset($t->logo_path) : null,
'cover_url' => $t->cover_path ? tenant_asset($t->cover_path) : null,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'shop_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'primary_color' => 'required|string|max:7',
            'logo' => 'nullable|image|max:2048',
            'cover' => 'nullable|image|max:4096',
        ]);

        $t = tenant();
        $t->shop_name = $request->shop_name;
        $t->description = $request->description;
        $t->primary_color = $request->primary_color;

        if ($request->hasFile('logo')) {
            $t->logo_path = $request->file('logo')->storeAs(
                "shop-branding/{$t->id}",
                'logo_' . time() . '.' . $request->file('logo')->extension(),
                'public'
            );
        }
        if ($request->hasFile('cover')) {
            $t->cover_path = $request->file('cover')->storeAs(
                "shop-branding/{$t->id}",
                'cover_' . time() . '.' . $request->file('cover')->extension(),
                'public'
            );
        }

        $t->save();

        return back()->with('success', 'تم تحديث إعدادات المحل بنجاح');
    }
}