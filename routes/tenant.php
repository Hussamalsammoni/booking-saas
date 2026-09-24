<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetCodeController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ProfileAvatarController;
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

Route::middleware(array_merge($tenancyMiddleware, ['auth', 'owner']))
    ->resource('services', \App\Http\Controllers\ServiceController::class);
Route::middleware(array_merge($tenancyMiddleware, ['auth', 'owner']))
    ->resource('staff', \App\Http\Controllers\StaffController::class);
Route::middleware(array_merge($tenancyMiddleware, ['auth', 'owner']))->get('/settings/shop', [\App\Http\Controllers\ShopSettingsController::class, 'edit'])->name('shop-settings.edit');
Route::middleware(array_merge($tenancyMiddleware, ['auth', 'owner']))->post('/settings/shop', [\App\Http\Controllers\ShopSettingsController::class, 'update'])->name('shop-settings.update');
Route::middleware($tenancyMiddleware)->get('/book', [\App\Http\Controllers\BookingPageController::class, 'index'])->name('booking.index');
Route::middleware($tenancyMiddleware)->post('/book', [\App\Http\Controllers\BookingPageController::class, 'store'])->name('booking.store');
Route::middleware($tenancyMiddleware)
    ->get('/booking/success/{booking}', [\App\Http\Controllers\BookingPageController::class, 'success'])
    ->name('booking.success');
Route::middleware($tenancyMiddleware)->get('/availability', [\App\Http\Controllers\AvailabilityController::class, 'getSlots'])->name('availability.slots');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->get('/bookings', [\App\Http\Controllers\BookingManagementController::class, 'index'])->name('bookings.index');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->post('/bookings', [\App\Http\Controllers\BookingManagementController::class, 'store'])->name('bookings.store');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->patch('/bookings/{booking}/status', [\App\Http\Controllers\BookingManagementController::class, 'updateStatus'])->name('bookings.updateStatus');
Route::middleware(array_merge($tenancyMiddleware, ['auth', 'owner']))->get('/invoices', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('invoices.index');
Route::middleware(array_merge($tenancyMiddleware, ['auth', 'owner']))->patch('/invoices/{invoice}/pay', [\App\Http\Controllers\InvoiceController::class, 'markAsPaid'])->name('invoices.markAsPaid');
Route::middleware(array_merge($tenancyMiddleware, ['auth', 'owner']))->patch('/invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'update'])->name('invoices.update');
Route::middleware(array_merge($tenancyMiddleware, ['auth', 'owner']))->delete('/invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'destroy'])->name('invoices.destroy');
Route::middleware(array_merge($tenancyMiddleware, ['auth', 'owner']))->get('/invoices/{invoice}/download', [\App\Http\Controllers\InvoicePdfController::class, 'download'])->name('invoices.download');
Route::middleware(array_merge($tenancyMiddleware, ['auth', 'verified', 'owner']))->get('/analytics', [\App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics.index');
// --- Dashboard & Profile ---
Route::middleware(array_merge($tenancyMiddleware, ['auth', 'verified']))
    ->get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->name('dashboard');

Route::middleware(array_merge($tenancyMiddleware, ['auth']))->get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

// --- Profile avatar ---
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->get('/profile/avatar/{id}', [ProfileAvatarController::class, 'show'])->name('profile.avatar.show');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->post('/profile/avatar', [ProfileAvatarController::class, 'update'])->name('profile.avatar.update');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->delete('/profile/avatar', [ProfileAvatarController::class, 'destroy'])->name('profile.avatar.destroy');

// --- Guest Auth Routes ---

Route::middleware(array_merge($tenancyMiddleware, ['guest']))->get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::middleware(array_merge($tenancyMiddleware, ['guest']))->post('login', [AuthenticatedSessionController::class, 'store']);

// --- نسيت كلمة المرور (برمز تأكيد على الإيميل) ---
Route::middleware(array_merge($tenancyMiddleware, ['guest']))->get('forgot-password', [PasswordResetCodeController::class, 'create'])->name('password.request');
Route::middleware(array_merge($tenancyMiddleware, ['guest', 'throttle:5,1']))->post('forgot-password', [PasswordResetCodeController::class, 'store'])->name('password.email');
Route::middleware(array_merge($tenancyMiddleware, ['guest']))->get('forgot-password/verify', [PasswordResetCodeController::class, 'verifyForm'])->name('password.verify');
Route::middleware(array_merge($tenancyMiddleware, ['guest', 'throttle:10,1']))->post('forgot-password/verify', [PasswordResetCodeController::class, 'verify'])->name('password.verify.store');
Route::middleware(array_merge($tenancyMiddleware, ['guest', 'throttle:3,1']))->post('forgot-password/resend', [PasswordResetCodeController::class, 'resend'])->name('password.resend');
Route::middleware(array_merge($tenancyMiddleware, ['guest']))->get('reset-password', [PasswordResetCodeController::class, 'resetForm'])->name('password.reset');
Route::middleware(array_merge($tenancyMiddleware, ['guest']))->post('reset-password', [PasswordResetCodeController::class, 'reset'])->name('password.store');

// --- Authenticated Auth Routes ---
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
Route::middleware(array_merge($tenancyMiddleware, ['auth', 'signed', 'throttle:6,1']))->get('verify-email/{id}/{hash}', VerifyEmailController::class)->name('verification.verify');
Route::middleware(array_merge($tenancyMiddleware, ['auth', 'throttle:6,1']))->post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->name('verification.send');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->post('confirm-password', [ConfirmablePasswordController::class, 'store']);
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->put('password', [PasswordController::class, 'update'])->name('password.update');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
Route::middleware(array_merge($tenancyMiddleware, ['auth']))->post('/notifications/read', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.read');