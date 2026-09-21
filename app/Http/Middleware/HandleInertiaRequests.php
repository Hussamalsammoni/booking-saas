<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user' => $request->user(),
        ],
        'flash' => [
            'success' => fn () => $request->session()->get('success'),
        ],
        'shop' => function () {
            if (! function_exists('tenant') || ! tenant()) {
                return null;
            }

            $t = tenant();

            return [
                'shop_name'       => $t->shop_name,
                'description'     => $t->description,
                'primary_color'   => $t->primary_color ?? '#4f46e5',
                'secondary_color' => $t->secondary_color,
                'bg_color'        => $t->bg_color,
                'text_color'      => $t->text_color,
                'logo_url'        => $t->logo_path ? tenant_asset($t->logo_path) : null,
                'cover_url'       => $t->cover_path ? tenant_asset($t->cover_path) : null,
            ];
        },
    ];
}
}