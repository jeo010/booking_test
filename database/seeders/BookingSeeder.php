<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get customers
        $customers = User::where('role', 'customer')->get();
        // Get tickets
        $tickets = Ticket::all();

        // Create 20 bookings
        Booking::factory()
            ->count(20)
            ->sequence(function ($sequence) use ($customers, $tickets) {
                return [
                    'user_id' => $customers->random()->id,
                    'ticket_id' => $tickets->random()->id,
                ];
            })
            ->create();
    }
}
