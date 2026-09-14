<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardApiController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;
use App\Http\Controllers\Api\ServiceApiController;
use App\Http\Controllers\Api\StaffApiController;
use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Api\InvoiceApiController;
use App\Http\Middleware\EnsureTenantIsActive;


Route::middleware([InitializeTenancyByRequestData::class, EnsureTenantIsActive::class])->group(function () {

    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/dashboard', [DashboardApiController::class, 'index']);
        Route::apiResource('services', ServiceApiController::class);
        Route::apiResource('staff', StaffApiController::class);
        Route::get('bookings', [BookingApiController::class, 'index']);
        Route::post('bookings', [BookingApiController::class, 'store']);
        Route::get('bookings/{booking}', [BookingApiController::class, 'show']);
        Route::patch('bookings/{booking}/status', [BookingApiController::class, 'updateStatus']);
        Route::delete('bookings/{booking}', [BookingApiController::class, 'destroy']);
        Route::get('invoices', [InvoiceApiController::class, 'index']);
        Route::post('invoices', [InvoiceApiController::class, 'store']);
        Route::get('invoices/{invoice}', [InvoiceApiController::class, 'show']);
        Route::patch('invoices/{invoice}/pay', [InvoiceApiController::class, 'markAsPaid']);
        Route::patch('invoices/{invoice}', [InvoiceApiController::class, 'update']);
        Route::delete('invoices/{invoice}', [InvoiceApiController::class, 'destroy']);
        });

});