<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_tickets_for_event(): void
    {
        $event = Event::factory()->create();
        Ticket::factory()->count(2)->create(['event_id' => $event->id]);

        $response = $this->getJson("/api/v1/events/{$event->id}/tickets");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(2, 'data');
    }

    public function test_organizer_can_create_update_and_delete_ticket(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::factory()->create(['created_by' => $organizer->id]);

        $this->actingAs($organizer, 'sanctum');

        $createResponse = $this->postJson("/api/v1/events/{$event->id}/tickets", [
            'type' => 'VIP',
            'price' => 100,
            'quantity' => 10,
        ]);

        $createResponse->assertStatus(201);
        $ticketId = $createResponse->json('data.id');

        $updateResponse = $this->putJson("/api/v1/tickets/{$ticketId}", ['price' => 150]);
        $updateResponse->assertStatus(200);
        $updateResponse->assertJsonPath('data.price', '150.00');

        $deleteResponse = $this->deleteJson("/api/v1/tickets/{$ticketId}");
        $deleteResponse->assertStatus(200);
    }

    public function test_customer_cannot_manage_tickets(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $event = Event::factory()->create(['created_by' => User::factory()->create(['role' => 'organizer'])->id]);

        $this->actingAs($customer, 'sanctum');

        $response = $this->postJson("/api/v1/events/{$event->id}/tickets", [
            'type' => 'VIP',
            'price' => 10,
            'quantity' => 1,
        ]);

        $response->assertStatus(403);
    }
}
