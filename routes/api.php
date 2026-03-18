<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TicketController;

Route::prefix('v1')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    // Public routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    // Authenticated routes
    Route::prefix('auth')->middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Events - public read
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{id}', [EventController::class, 'show']);
    Route::get('/events/{event_id}/tickets', [TicketController::class, 'index']);

    // Events - organizer/admin write
    Route::middleware(['auth:sanctum', 'role:admin,organizer'])->group(function () {
        Route::post('/events', [EventController::class, 'store']);
        Route::patch('/events/{id}', [EventController::class, 'update']);
        Route::delete('/events/{id}', [EventController::class, 'destroy']);
    });

    // Tickets
    Route::middleware(['auth:sanctum', 'role:admin,organizer'])->group(function () {
        Route::post('/events/{event_id}/tickets', [TicketController::class, 'store']);
        Route::put('/tickets/{id}', [TicketController::class, 'update']);
        Route::delete('/tickets/{id}', [TicketController::class, 'destroy']);
    });

    // Bookings - customer
    Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
        Route::post('/tickets/{ticket}/bookings', [BookingController::class, 'store'])
            ->middleware('prevent.double.booking');
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::get('/bookings/{id}', [BookingController::class, 'show']);
        Route::put('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
    });

    // Bookings - admin management
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/admin/bookings', [BookingController::class, 'adminIndex']);
        Route::get('/admin/bookings/{id}', [BookingController::class, 'adminShow']);
        Route::put('/admin/bookings/{id}/cancel', [BookingController::class, 'adminCancel']);
    });

    // Payments
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/bookings/{bookingId}/payment', [PaymentController::class, 'store']);
        Route::get('/payments/{id}', [PaymentController::class, 'show']);
    });
});