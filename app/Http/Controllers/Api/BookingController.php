<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Ticket;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    use ApiResponse;

    public function store(Request $request, $ticketId)
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $booking = DB::transaction(function () use ($ticketId, $validated, $request) {
                // Lock the ticket row to prevent race conditions
                $ticket = Ticket::where('id', $ticketId)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Check availability inside transaction with locked ticket
                $bookedQuantity = Booking::where('ticket_id', $ticketId)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->sum('quantity');

                if ($bookedQuantity + $validated['quantity'] > $ticket->quantity) {
                    throw new \Exception('Not enough tickets available');
                }

                return Booking::create([
                    'user_id' => $request->user()->id,
                    'ticket_id' => $ticketId,
                    'quantity' => $validated['quantity'],
                    'status' => 'pending',
                ]);
            });

            return $this->success($booking, 'Booking created successfully', 201);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Not enough tickets available') {
                return $this->error('Not enough tickets available', null, 409);
            }
            return $this->error('Failed to create booking');
        }
    }

    public function index(Request $request)
    {
        try {
            $query = Booking::withRelations(['ticket.event']);

            // Only return current user's bookings unless admin explicitly requests all
            if (!$request->user()->isAdmin()) {
                $query->where('user_id', $request->user()->id);
            }

            // Filter by status if provided
            if ($request->has('status')) {
                $query->filterByStatus($request->status);
            }

            // Filter by date range if provided
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->filterByDateRange($request->start_date, $request->end_date);
            }

            // Order by latest by default
            $query->orderByLatest();

            $bookings = $query->get();

            return $this->success($bookings, 'Bookings retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve bookings');
        }
    }

    public function show($id)
    {
        try {
            $user = request()->user();
            if (!$user) {
                return $this->unauthorized('Unauthorized');
            }

            $bookingQuery = Booking::withRelations(['ticket.event', 'payment']);

            if (!$user->isAdmin()) {
                $bookingQuery->where('user_id', $user->id);
            }

            $booking = $bookingQuery->findOrFail($id);

            return $this->success($booking, 'Booking retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound('Booking not found');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve booking');
        }
    }

    public function adminIndex(Request $request)
    {
        try {
            $query = Booking::withRelations(['ticket.event'])->orderByLatest();

            if ($request->has('status')) {
                $query->filterByStatus($request->status);
            }

            $bookings = $query->get();

            return $this->success($bookings, 'All bookings retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve bookings');
        }
    }

    public function adminShow($id)
    {
        try {
            $booking = Booking::withRelations(['ticket.event', 'payment'])->findOrFail($id);
            return $this->success($booking, 'Booking retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound('Booking not found');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve booking');
        }
    }

    public function cancel($id)
    {
        try {
            $bookingQuery = Booking::where('id', $id);

            if (!request()->user()->isAdmin()) {
                $bookingQuery->where('user_id', request()->user()->id);
            }

            $booking = $bookingQuery->firstOrFail();

            if ($booking->status === 'cancelled') {
                return $this->error('Booking is already cancelled');
            }

            $booking->update(['status' => 'cancelled']);

            // If paid, trigger refund (simulate)
            if ($booking->payment && $booking->payment->status === 'success') {
                $booking->payment->update(['status' => 'refunded']);
            }

            return $this->success($booking, 'Booking cancelled successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to cancel booking');
        }
    }

    public function adminCancel($id)
    {
        try {
            $booking = Booking::findOrFail($id);

            if ($booking->status === 'cancelled') {
                return $this->error('Booking is already cancelled');
            }

            $booking->update(['status' => 'cancelled']);

            // If paid, trigger refund (simulate)
            if ($booking->payment && $booking->payment->status === 'success') {
                $booking->payment->update(['status' => 'refunded']);
            }

            return $this->success($booking, 'Booking cancelled successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to cancel booking');
        }
    }
}
