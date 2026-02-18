<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get organizers
        $organizers = User::where('role', 'organizer')->get();

        // Create 5 events
        Event::factory()
            ->count(5)
            ->sequence(fn ($sequence) => [
                'created_by' => $organizers->random()->id,
            ])
            ->create();
    }
}
