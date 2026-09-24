<?php

namespace App\Http\Controllers;

use App\Support\MapCoords;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShopSettingsController extends Controller
{
    private function defaultWorkingHours(): array
    {
        $days = ['السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'];

        return collect($days)->map(fn ($day) => [
            'day'     => $day,
            'enabled' => $day !== 'الجمعة',
            'start'   => '09:00',
            'end'     => '18:00',
        ])->values()->all();
    }

    public function edit()
    {
        $t = tenant();

        return Inertia::render('ShopSettings/Edit', [
            'shop' => [
                'shop_name'       => $t->shop_name,
                'description'     => $t->description,
                'phone'           => $t->phone,
                'primary_color'   => $t->primary_color ?? '#ff0569',
                'secondary_color' => $t->secondary_color ?? '#1E1E24',
                'bg_color'        => $t->bg_color ?? '#F9F8F6',
                'text_color'      => $t->text_color ?? '#2D2D2D',
                'logo_url'        => $t->logo_path ? tenant_asset($t->logo_path) : null,
                'cover_url'       => $t->cover_path ? tenant_asset($t->cover_path) : null,
                'features'        => $t->features ?? [],
                'gallery'         => collect($t->gallery_paths ?? [])->map(fn ($path) => [
                    'path' => $path,
                    'url'  => tenant_asset($path),
                ])->values(),

                'working_hours'   => $t->working_hours ?? $this->defaultWorkingHours(),
                'map_address'     => $t->map_address,
                'map_url'         => $t->map_url,
                'social_links'    => $t->social_links ?? ['facebook' => '', 'instagram' => '', 'tiktok' => '', 'snapchat' => ''],
                'testimonials'    => $t->testimonials ?? [],
            ],
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'shop_name'          => 'required|string|max:255',
            'description'        => 'nullable|string|max:500',
            'phone'              => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\s\-]{8,20}$/'],
            'primary_color'      => 'required|string|max:7',
            'secondary_color'    => 'nullable|string|max:7',
            'bg_color'           => 'nullable|string|max:7',
            'text_color'         => 'nullable|string|max:7',
            'logo'               => 'nullable|image|max:2048',
            'cover'              => 'nullable|image|max:4096',

            'features'           => 'nullable|array|max:8',
            'features.*.title'   => 'required_with:features|string|max:60',
            'features.*.icon'    => 'nullable|string|max:10',

            'gallery_keep'       => 'nullable|array',
            'gallery_keep.*'     => 'string',
            'gallery_new'        => 'nullable|array|max:12',
            'gallery_new.*'      => 'image|max:4096',

            // ساعات العمل
            'working_hours'            => 'nullable|array|size:7',
            'working_hours.*.day'      => 'required|string',
            'working_hours.*.enabled'  => 'boolean',
            'working_hours.*.start'    => 'required_if:working_hours.*.enabled,true|string',
            'working_hours.*.end'      => 'required_if:working_hours.*.enabled,true|string',

            // الموقع
            'map_address'        => 'nullable|string|max:255',
            'map_url'            => 'nullable|url|max:500',

            // سوشال ميديا
            'social_links.facebook'  => 'nullable|url|max:255',
            'social_links.instagram' => 'nullable|url|max:255',
            'social_links.tiktok'    => 'nullable|url|max:255',
            'social_links.snapchat'  => 'nullable|url|max:255',

            // التقييمات
            'testimonials'            => 'nullable|array|max:6',
            'testimonials.*.name'     => 'required_with:testimonials|string|max:60',
            'testimonials.*.text'     => 'required_with:testimonials|string|max:300',
            'testimonials.*.rating'   => 'required_with:testimonials|integer|min:1|max:5',
        ], [
            'phone.regex' => 'رقم الهاتف غير صالح، اكتبه بأرقام فقط مثل 0912345678.',
        ]);

        $t = tenant();
        $t->shop_name       = $request->shop_name;
        $t->description     = $request->description;
        $t->phone           = $request->phone ? trim($request->phone) : null;
        $t->primary_color   = $request->primary_color;
        $t->secondary_color = $request->secondary_color;
        $t->bg_color        = $request->bg_color;
        $t->text_color      = $request->text_color;
        $t->features        = $request->features ?? [];

        $t->working_hours   = $request->working_hours ?? $this->defaultWorkingHours();
        $t->map_address     = $request->map_address;

        // جديد: نحسب إحداثيات الدبوس من رابط الخرائط (لما يتغير الرابط أو ما في إحداثيات محفوظة)
        if ($request->map_url !== $t->map_url || !$t->map_lat) {
            [$t->map_lat, $t->map_lng] = MapCoords::fromUrl($request->map_url) ?? [null, null];
        }

        $t->map_url         = $request->map_url;
        $t->social_links    = $request->social_links ?? [];
        $t->testimonials    = $request->testimonials ?? [];

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

        $keep = $request->input('gallery_keep', []);
        $existing = collect($t->gallery_paths ?? []);

        $existing->reject(fn ($path) => in_array($path, $keep, true))
            ->each(function ($path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            });

        $newPaths = collect($request->file('gallery_new', []))->map(
            fn ($file) => $file->storeAs(
                "shop-branding/{$t->id}/gallery",
                'photo_' . time() . '_' . uniqid() . '.' . $file->extension(),
                'public'
            )
        );

        $t->gallery_paths = $existing->intersect($keep)->values()
            ->merge($newPaths)
            ->take(12)
            ->values()
            ->all();

        $t->save();

        return back()->with('success', 'تم تحديث إعدادات المحل بنجاح');
    }
}