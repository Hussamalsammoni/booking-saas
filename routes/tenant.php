<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureTenantIsActive;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

$tenancyMiddleware = [
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    EnsureTenantIsActive::class,
];

Route::middleware(array_merge($tenancyMiddleware, ['auth']))
    ->resource('services', \App\Http\Controllers\ServiceController::class);
Route::middleware(array_merge($tenancyMiddleware, ['auth']))
    ->resource('staff', \App\Http\Controllers\StaffController::class);
Route::middleware($tenancyMiddleware)->get('/book', [\App\Http\Controllers\BookingPageController::class, 'index'])->name('booking.index');
Route::middleware($tenancyMiddleware)->post('/book', [\App\Http\Controllers\BookingPageController::class, 'store'])->name('booking.store');
Route::middleware($tenancyMiddleware)
    ->get('/booking/success/{booking}', [\App\Http\Controllers\BookingPageController::class, 'success'])
    ->name('booking.success');
Route::middleware($tenancyMiddleware)->get('/availability', [\App\Http\Controllers\AvailabilityController::class, 'getSlots'])->name('availability.slots');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->get('/bookings', [\App\Http\Controllers\BookingManagementController::class, 'index'])->name('bookings.index');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->patch('/bookings/{booking}/status', [\App\Http\Controllers\BookingManagementController::class, 'updateStatus'])->name('bookings.updateStatus');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->get('/invoices', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('invoices.index');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->patch('/invoices/{invoice}/pay', [\App\Http\Controllers\InvoiceController::class, 'markAsPaid'])->name('invoices.markAsPaid');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->patch('/invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'update'])->name('invoices.update');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->delete('/invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'destroy'])->name('invoices.destroy');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->get('/invoices/{invoice}/download', [\App\Http\Controllers\InvoicePdfController::class, 'download'])->name('invoices.download');
Route::middleware(array_merge($tenancyMiddleware, ['auth', 'verified']))->get('/analytics', [\App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics.index');
// --- Dashboard & Profile ---
Route::middleware(array_merge($tenancyMiddleware, ['auth', 'verified']))
    ->get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->name('dashboard');

Route::middleware(array_merge($tenancyMiddleware, ['auth']))->get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

// --- Guest Auth Routes ---

Route::middleware(array_merge($tenancyMiddleware, ['guest']))->get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::middleware(array_merge($tenancyMiddleware, ['guest']))->post('login', [AuthenticatedSessionController::class, 'store']);
Route::middleware(array_merge($tenancyMiddleware, ['guest']))->get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
Route::middleware(array_merge($tenancyMiddleware, ['guest']))->post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
Route::middleware(array_merge($tenancyMiddleware, ['guest']))->get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
Route::middleware(array_merge($tenancyMiddleware, ['guest']))->post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');

// --- Authenticated Auth Routes ---
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
Route::middleware(array_merge($tenancyMiddleware, ['auth', 'signed', 'throttle:6,1']))->get('verify-email/{id}/{hash}', VerifyEmailController::class)->name('verification.verify');
Route::middleware(array_merge($tenancyMiddleware, ['auth', 'throttle:6,1']))->post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->name('verification.send');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->post('confirm-password', [ConfirmablePasswordController::class, 'store']);
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->put('password', [PasswordController::class, 'update'])->name('password.update');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');