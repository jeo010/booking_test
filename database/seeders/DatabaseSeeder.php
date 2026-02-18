<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create 2 admins
        User::factory()->admin()->count(2)->create();

        // Create 3 organizers
        User::factory()->organizer()->count(3)->create();

        // Create 10 customers
        User::factory()->customer()->count(10)->create();

        // Call other seeders
        $this->call([
            EventSeeder::class,
            TicketSeeder::class,
            BookingSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}

