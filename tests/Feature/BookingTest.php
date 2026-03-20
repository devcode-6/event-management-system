<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_booking_and_prevents_double_booking(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $ticket = Ticket::factory()->create(['quantity' => 5]);

        $this->actingAs($customer, 'sanctum');

        $firstResponse = $this->postJson("/api/v1/tickets/{$ticket->id}/bookings", ['quantity' => 2]);
        $firstResponse->assertStatus(201);

        // Attempt to book same ticket again should be blocked by middleware
        $secondResponse = $this->postJson("/api/v1/tickets/{$ticket->id}/bookings", ['quantity' => 1]);
        $secondResponse->assertStatus(409);
        $secondResponse->assertJson(['message' => 'You already have an active booking for this ticket']);
    }

    public function test_booking_fails_when_not_enough_tickets_available(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $ticket = Ticket::factory()->create(['quantity' => 2]);

        $this->actingAs($customer, 'sanctum');

        $response = $this->postJson("/api/v1/tickets/{$ticket->id}/bookings", ['quantity' => 5]);

        $response->assertStatus(409);
        $response->assertJson(['message' => 'Not enough tickets available']);
    }

    public function test_user_can_list_and_view_their_bookings(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $other = User::factory()->create(['role' => 'customer']);

        Booking::factory()->create(['user_id' => $customer->id]);
        Booking::factory()->create(['user_id' => $other->id]);

        $this->actingAs($customer, 'sanctum');

        $response = $this->getJson('/api/v1/bookings');
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertCount(1, $response->json('data')); // should only return own booking
    }

    public function test_user_can_view_single_booking_and_cannot_view_others(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $other = User::factory()->create(['role' => 'customer']);

        $booking = Booking::factory()->create(['user_id' => $customer->id]);
        $otherBooking = Booking::factory()->create(['user_id' => $other->id]);

        $this->actingAs($customer, 'sanctum');

        $response = $this->getJson("/api/v1/bookings/{$booking->id}");
        $response->assertStatus(200);

        $denied = $this->getJson("/api/v1/bookings/{$otherBooking->id}");
        $denied->assertStatus(404);
    }

    public function test_customer_can_cancel_booking_and_refund_payment(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = Booking::factory()->create(['user_id' => $customer->id, 'status' => 'pending']);
        Payment::factory()->create(['booking_id' => $booking->id, 'status' => 'success']);

        $this->actingAs($customer, 'sanctum');

        $response = $this->putJson("/api/v1/bookings/{$booking->id}/cancel");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
        $this->assertDatabaseHas('payments', ['booking_id' => $booking->id, 'status' => 'refunded']);
    }

    public function test_admin_can_cancel_any_booking(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $booking = Booking::factory()->create(['status' => 'pending']);

        $this->actingAs($admin, 'sanctum');

        $response = $this->putJson("/api/v1/admin/bookings/{$booking->id}/cancel");
        $response->assertStatus(200);

        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
    }
}
