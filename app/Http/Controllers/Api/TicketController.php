<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Ticket;
use App\Traits\ApiResponse;
use Illuminate\Validation\ValidationException;

class TicketController extends Controller
{
    use ApiResponse;

    public function index($eventId)
    {
        try {
            $tickets = Ticket::where('event_id', $eventId)->get();
            return $this->success($tickets, 'Tickets retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve tickets');
        }
    }

    public function store(Request $request, $eventId)
    {
        try {
            $event = Event::findOrFail($eventId);

            if ($event->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
                return $this->forbidden('Only event organizer can add tickets');
            }

            $validated = $request->validate([
                'type' => 'required|in:VIP,Standard,Economy',
                'price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:1',
            ]);

            $ticket = Ticket::create([
                'event_id' => $eventId,
                'type' => $validated['type'],
                'price' => $validated['price'],
                'quantity' => $validated['quantity'],
            ]);

            return $this->success($ticket, 'Ticket created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create ticket');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $ticket = Ticket::findOrFail($id);
            $event = $ticket->event;

            if ($event->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
                return $this->forbidden('Only event organizer can update tickets');
            }

            $validated = $request->validate([
                'type' => 'sometimes|required|in:VIP,Standard,Economy',
                'price' => 'sometimes|required|numeric|min:0',
                'quantity' => 'sometimes|required|integer|min:1',
            ]);

            $ticket->update($validated);

            return $this->success($ticket, 'Ticket updated successfully');
        } catch (ValidationException $e) {
            return $this->error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->error('Failed to update ticket');
        }
    }

    public function destroy($id)
    {
        try {
            $ticket = Ticket::findOrFail($id);
            $event = $ticket->event;

            if ($event->created_by !== request()->user()->id && !request()->user()->isAdmin()) {
                return $this->forbidden('Only event organizer can delete tickets');
            }

            $ticket->delete();

            return $this->success(null, 'Ticket deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete ticket');
        }
    }
}
