<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get confirmed bookings
        $bookings = Booking::where('status', 'confirmed')->get();

        // Create payments for bookings (at most one payment per booking)
        $bookings->each(function ($booking) {
            Payment::factory()->create([
                'booking_id' => $booking->id,
            ]);
        });
    }
}
