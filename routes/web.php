<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

// مسارات الدومين الرئيسي (Central Domain)
Route::middleware([
    'web',
])->group(function () {

    Route::get('/', function () {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    });

    // 'central': التسجيل بس على الدومين الرئيسي (مو من دومين أي محل)
    Route::middleware(['central', 'guest'])->group(function () {
        Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');

        // كل تسجيل بيعمل قاعدة بيانات جديدة، فنحدّ العدد: 5 محاولات بالساعة للـ IP الواحد
        Route::post('/register', [RegisteredUserController::class, 'store'])
            ->middleware('throttle:5,60');
    });

});