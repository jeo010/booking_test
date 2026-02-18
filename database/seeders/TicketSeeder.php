<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all events
        $events = Event::all();

        // Create 15 tickets distributed across events
        Ticket::factory()
            ->count(15)
            ->sequence(function ($sequence) use ($events) {
                return [
                    'event_id' => $events->random()->id,
                ];
            })
            ->create();
    }
}
