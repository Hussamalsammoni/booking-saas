<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ShopLookupController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// مسارات الدومين الرئيسي (Central Domain)
Route::middleware([
    'web',
])->group(function () {

    $centralDomain = parse_url(config('app.url'), PHP_URL_HOST);

    /*
    |--------------------------------------------------------------------------
    | الصفحة الرئيسية
    |--------------------------------------------------------------------------
    */

    Route::get('/', function () use ($centralDomain) {

        $port = request()->getPort();
        $portPart = in_array($port, [80, 443], true)
            ? ''
            : ':' . $port;

        $registerUrl =
            request()->getScheme()
            . '://'
            . $centralDomain
            . $portPart
            . '/register';

        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'registerUrl' => $registerUrl,

            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | التسجيل - Central Domain فقط
    |--------------------------------------------------------------------------
    |
    | إنشاء المحل يجب أن يحصل على الدومين الرئيسي،
    | وليس على دومين أي Tenant.
    |
    */

    Route::domain($centralDomain)
        ->middleware(['central', 'guest'])
        ->group(function () {

            Route::get('/register', [RegisteredUserController::class, 'create'])
                ->name('register');

            Route::post('/register', [RegisteredUserController::class, 'store'])
                ->middleware('throttle:5,60');
        });

    /*
    |--------------------------------------------------------------------------
    | البحث عن المحل
    |--------------------------------------------------------------------------
    */

    Route::get('/shop-lookup', ShopLookupController::class)
        ->middleware('throttle:20,1')
        ->name('shop.lookup');
});