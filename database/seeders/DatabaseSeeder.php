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
        // Create 2 admin users
        User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'admin']);

        // Create 3 organizer users
        User::factory(3)->create(['role' => 'organizer']);

        // Create 10 customer users
        User::factory(10)->create(['role' => 'customer']);

        // Get organizers
        $organizers = User::where('role', 'organizer')->get();

        // Create 5 events, each by a random organizer
        $events = [];
        for ($i = 0; $i < 5; $i++) {
            $events[] = \App\Models\Event::factory()->create([
                'created_by' => $organizers->random()->id,
            ]);
        }

        // Create 15 tickets (3 per event)
        foreach ($events as $event) {
            \App\Models\Ticket::factory()->create(['event_id' => $event->id, 'type' => 'VIP']);
            \App\Models\Ticket::factory()->create(['event_id' => $event->id, 'type' => 'Standard']);
            \App\Models\Ticket::factory()->create(['event_id' => $event->id, 'type' => 'Economy']);
        }

        // Get customers and tickets
        $customers = User::where('role', 'customer')->get();
        $tickets = \App\Models\Ticket::all();

        // Create 20 bookings by random customers, status confirmed
        for ($i = 0; $i < 20; $i++) {
            $booking = \App\Models\Booking::factory()->create([
                'user_id' => $customers->random()->id,
                'ticket_id' => $tickets->random()->id,
                'status' => 'confirmed',
            ]);

            // Create payment for each booking
            \App\Models\Payment::factory()->create([
                'booking_id' => $booking->id,
                'status' => 'success',
            ]);
        }
    }
}
