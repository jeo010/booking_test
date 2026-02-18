<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (all authenticated users)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'user']);

    // Events - Public view (with pagination, search, filtering)
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event}', [EventController::class, 'show']);

    // Event management (Admin & Organizer only)
    Route::middleware('role:admin,organizer')->group(function () {
        Route::post('/events', [EventController::class, 'store']);
        Route::put('/events/{event}', [EventController::class, 'update']);
        Route::delete('/events/{event}', [EventController::class, 'destroy']);
    });

    // Tickets - nested under events
    Route::middleware('role:admin,organizer')->group(function () {
        Route::post('/events/{event}/tickets', [TicketController::class, 'store']);
    });

    // Ticket management (Update/Delete)
    Route::middleware('role:admin,organizer')->group(function () {
        Route::put('/tickets/{ticket}', [TicketController::class, 'update']);
        Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy']);
    });

    // Bookings - Create booking for a ticket
    Route::middleware('role:customer,prevent.double.booking')->group(function () {
        Route::post('/tickets/{ticket}/bookings', [BookingController::class, 'store']);
    });

    // Bookings - List and retrieve
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);

    // Booking management - Update status
    Route::middleware('role:admin,organizer,customer')->group(function () {
        Route::put('/bookings/{booking}', [BookingController::class, 'update']);
    });

    // Booking cancellation
    Route::middleware('role:admin,customer')->group(function () {
        Route::put('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
        Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);
    });

    // Payments - Mock payment processing
    Route::middleware('role:admin,customer')->group(function () {
        Route::post('/bookings/{booking}/payment', [PaymentController::class, 'store']);
    });

    // Get payment details
    Route::get('/payments/{payment}', [PaymentController::class, 'show']);
});

