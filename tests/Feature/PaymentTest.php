<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_process_payment_for_pending_booking(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $ticket = Ticket::factory()->create(['price' => 50.00, 'quantity' => 10]);
        $booking = Booking::factory()->create(['user_id' => $customer->id, 'ticket_id' => $ticket->id, 'status' => 'pending', 'quantity' => 2]);

        $this->mock(PaymentService::class, function ($mock) use ($booking) {
            $mock->shouldReceive('processPayment')
                ->once()
                ->with(
                    \Mockery::on(fn ($value) => $value instanceof Booking && $value->id === $booking->id)
                )
                ->andReturn(['status' => 'success', 'amount' => 100.00]);
        });

        $this->actingAs($customer, 'sanctum');

        $response = $this->postJson("/api/v1/bookings/{$booking->id}/payment");

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'message' => 'Payment processed']);
    }

    public function test_payment_fails_if_booking_not_pending(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $ticket = Ticket::factory()->create();
        $booking = Booking::factory()->create(['user_id' => $customer->id, 'ticket_id' => $ticket->id, 'status' => 'confirmed']);

        $this->actingAs($customer, 'sanctum');

        $response = $this->postJson("/api/v1/bookings/{$booking->id}/payment");

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Booking is not in pending status']);
    }

    public function test_user_can_view_own_payment_and_cannot_view_others(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $other = User::factory()->create(['role' => 'customer']);

        $booking = Booking::factory()->create(['user_id' => $customer->id]);
        $payment = Payment::factory()->create(['booking_id' => $booking->id]);

        $this->actingAs($customer, 'sanctum');
        $response = $this->getJson("/api/v1/payments/{$payment->id}");
        $response->assertStatus(200);

        $this->actingAs($other, 'sanctum');
        $denied = $this->getJson("/api/v1/payments/{$payment->id}");
        $denied->assertStatus(403);
    }
}
