<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Create a mock payment for a booking.
     */
    public function store(Request $request, Booking $booking)
    {
        $user = $request->user();

        if ($user->role === 'customer' && $booking->user_id !== $user->id) {
            return response()->json(['message' => 'You do not have permission to process payment for this booking'], 403);
        }

        if ($user->role === 'organizer') {
            return response()->json(['message' => 'You do not have permission to process payments'], 403);
        }

        // Check if booking is already paid
        $existingPayment = Payment::where('booking_id', $booking->id)
            ->where('status', 'success')
            ->first();

        if ($existingPayment) {
            return response()->json(['message' => 'Payment already processed for this booking'], 422);
        }

        $validated = $request->validate([
            'payment_method' => 'sometimes|in:card,bank_transfer,wallet',
        ]);

        // Mock payment processing (90% success rate)
        $isSuccess = rand(1, 100) <= 90;
        $status = $isSuccess ? 'success' : 'failed';

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $booking->ticket->price * $booking->quantity,
            'status' => $status,
        ]);

        if ($isSuccess) {
            $booking->update(['status' => 'confirmed']);
        }

        return response()->json([
            'message' => $isSuccess ? 'Payment processed successfully' : 'Payment failed',
            'data' => $payment->load('booking'),
        ], $isSuccess ? 201 : 422);
    }

    /**
     * Get payment details.
     */
    public function show(Request $request, Payment $payment)
    {
        $user = $request->user();

        if ($user->role === 'customer' && $payment->booking->user_id !== $user->id) {
            return response()->json(['message' => 'You do not have permission to view this payment'], 403);
        }

        if ($user->role === 'organizer' && $payment->booking->ticket->event->created_by !== $user->id) {
            return response()->json(['message' => 'You do not have permission to view this payment'], 403);
        }

        return response()->json([
            'message' => 'Payment retrieved successfully',
            'data' => $payment->load('booking'),
        ], 200);
    }
}
