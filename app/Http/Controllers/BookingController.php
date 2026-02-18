<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Ticket;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Get all bookings (filtered by role).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $bookings = Booking::with('user', 'ticket.event')->paginate(15);
        } elseif ($user->role === 'customer') {
            $bookings = Booking::where('user_id', $user->id)->with('ticket.event')->paginate(15);
        } elseif ($user->role === 'organizer') {
            $bookings = Booking::whereHas('ticket.event', function ($query) use ($user) {
                $query->where('created_by', $user->id);
            })->with('user', 'ticket.event')->paginate(15);
        }

        return response()->json([
            'message' => 'Bookings retrieved successfully',
            'data' => $bookings->items(),
            'pagination' => [
                'total' => $bookings->total(),
                'per_page' => $bookings->perPage(),
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
            ],
        ], 200);
    }

    /**
     * Get a single booking.
     */
    public function show(Request $request, Booking $booking)
    {
        $user = $request->user();

        if ($user->role === 'customer' && $booking->user_id !== $user->id) {
            return response()->json(['message' => 'You do not have permission to view this booking'], 403);
        }

        if ($user->role === 'organizer' && $booking->ticket->event->created_by !== $user->id) {
            return response()->json(['message' => 'You do not have permission to view this booking'], 403);
        }

        return response()->json([
            'message' => 'Booking retrieved successfully',
            'data' => $booking->load('user', 'ticket.event'),
        ], 200);
    }

    /**
     * Create a new booking (Customers only).
     */
    public function store(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if ($ticket->quantity < $validated['quantity']) {
            return response()->json([
                'message' => 'Not enough tickets available',
                'available' => $ticket->quantity,
            ], 422);
        }

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'ticket_id' => $ticket->id,
            'quantity' => $validated['quantity'],
            'status' => 'pending',
        ]);

        // Update ticket quantity
        $ticket->decrement('quantity', $validated['quantity']);

        return response()->json([
            'message' => 'Booking created successfully',
            'data' => $booking->load('ticket.event'),
        ], 201);
    }

    /**
     * Update booking status (Admin, Organizer, or Customer).
     */
    public function update(Request $request, Booking $booking)
    {
        $user = $request->user();

        if ($user->role === 'customer' && $booking->user_id !== $user->id) {
            return response()->json(['message' => 'You do not have permission to update this booking'], 403);
        }

        if ($user->role === 'organizer' && $booking->ticket->event->created_by !== $user->id) {
            return response()->json(['message' => 'You do not have permission to update this booking'], 403);
        }

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,confirmed,cancelled',
        ]);

        $booking->update($validated);

        return response()->json([
            'message' => 'Booking updated successfully',
            'data' => $booking,
        ], 200);
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Request $request, Booking $booking)
    {
        $user = $request->user();

        if ($user->role === 'customer' && $booking->user_id !== $user->id) {
            return response()->json(['message' => 'You do not have permission to cancel this booking'], 403);
        }

        if ($user->role === 'organizer') {
            return response()->json(['message' => 'You do not have permission to cancel bookings'], 403);
        }

        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Booking is already cancelled'], 422);
        }

        // Restore tickets
        $ticket = $booking->ticket;
        $ticket->increment('quantity', $booking->quantity);

        // Update status
        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'data' => $booking,
        ], 200);
    }

    /**
     * Delete a booking (Admin only).
     */
    public function destroy(Request $request, Booking $booking)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'You do not have permission to delete bookings'], 403);
        }

        // Restore tickets if booking isn't already cancelled
        if ($booking->status !== 'cancelled') {
            $ticket = $booking->ticket;
            $ticket->increment('quantity', $booking->quantity);
        }

        $booking->delete();

        return response()->json([
            'message' => 'Booking deleted successfully',
        ], 200);
    }
}
