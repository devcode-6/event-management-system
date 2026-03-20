<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_events_with_filters(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        Event::factory()->create(['title' => 'Laravel Conference', 'location' => 'Austin', 'date' => now()->addDays(10), 'created_by' => $organizer->id]);
        Event::factory()->create(['title' => 'Vue Summit', 'location' => 'Remote', 'date' => now()->addDays(20), 'created_by' => $organizer->id]);

        $response = $this->getJson('/api/v1/events?search=Laravel&location=Austin');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.data.0.title', 'Laravel Conference');
    }

    public function test_show_event_returns_event_data(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::factory()->create(['created_by' => $organizer->id]);

        $response = $this->getJson("/api/v1/events/{$event->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $event->id);
    }

    public function test_organizer_can_create_update_and_delete_event(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);

        $this->actingAs($organizer, 'sanctum');

        $createResponse = $this->postJson('/api/v1/events', [
            'title' => 'Test Event',
            'description' => 'An event for testing',
            'date' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'location' => 'Test City',
        ]);

        $createResponse->assertStatus(201);
        $eventId = $createResponse->json('data.id');

        $updateResponse = $this->patchJson("/api/v1/events/{$eventId}", [
            'title' => 'Updated Event',
        ]);

        $updateResponse->assertStatus(200);
        $updateResponse->assertJsonPath('data.title', 'Updated Event');

        $deleteResponse = $this->deleteJson("/api/v1/events/{$eventId}");
        $deleteResponse->assertStatus(200);
    }

    public function test_user_cannot_manage_other_users_events(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $otherOrganizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::factory()->create(['created_by' => $otherOrganizer->id]);

        $this->actingAs($organizer, 'sanctum');

        $response = $this->patchJson("/api/v1/events/{$event->id}", ['title' => 'Bad Update']);
        $response->assertStatus(403);

        $response = $this->deleteJson("/api/v1/events/{$event->id}");
        $response->assertStatus(403);
    }
}
