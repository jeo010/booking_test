<?php

namespace App\Http\Middleware;

use App\Models\Booking;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventDoubleBooking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check on POST requests to booking endpoints
        if ($request->isMethod('post') && $request->is('api/tickets/*/bookings')) {
            $user = $request->user();
            $ticket = $request->route('ticket');

            // Check if user already has an active booking for this ticket
            $existingBooking = Booking::where('user_id', $user->id)
                ->where('ticket_id', $ticket->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->first();

            if ($existingBooking) {
                return response()->json([
                    'message' => 'You have already booked this ticket. Booking ID: ' . $existingBooking->id,
                    'existing_booking' => [
                        'id' => $existingBooking->id,
                        'status' => $existingBooking->status,
                        'quantity' => $existingBooking->quantity,
                    ],
                ], 409); // 409 Conflict
            }
        }

        return $next($request);
    }
}
