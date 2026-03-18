<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        try {
            $query = Event::withRelations(['creator']);

            if ($request->has('search')) {
                $query->searchByTitle($request->search);
            }

            if ($request->has('date')) {
                $query->filterByDate($request->date);
            }

            if ($request->has('location')) {
                $query->where('location', 'LIKE', '%' . $request->location . '%');
            }

            $query->orderByLatest();

            $cacheKey = 'events_list_' . md5(serialize($request->all()));
            $events = Cache::remember($cacheKey, 600, function () use ($query) {
                return $query->paginate(10);
            });

            return $this->success($events, 'Events retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve events');
        }
    }

    public function show($id)
    {
        try {
            $event = Event::with('tickets')->findOrFail($id);
            return $this->success($event, 'Event retrieved successfully');
        } catch (\Exception $e) {
            return $this->notFound('Event not found');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'date' => 'required|date_format:Y-m-d H:i:s',
                'location' => 'required|string|max:255',
            ]);

            $event = Event::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'date' => $validated['date'],
                'location' => $validated['location'],
                'created_by' => $request->user()->id,
            ]);

            Cache::flush();

            return $this->success($event, 'Event created successfully', 201);
        } catch (ValidationException $e) {
            return $this->error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->error('Failed to create event');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'date' => 'sometimes|required|date_format:Y-m-d H:i:s',
                'location' => 'sometimes|required|string|max:255',
            ]);

            $event = Event::findOrFail($id);

            if ($event->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
                return $this->forbidden('You can only update your own events');
            }

            $event->update($validated);

            Cache::flush();

            return $this->success($event, 'Event updated successfully');
        } catch (ValidationException $e) {
            return $this->error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->error('Failed to update event');
        }
    }

    public function destroy($id)
    {
        try {
            $event = Event::findOrFail($id);

            if ($event->created_by !== request()->user()->id && !request()->user()->isAdmin()) {
                return $this->forbidden('You can only delete your own events');
            }

            $event->delete();

            Cache::flush();

            return $this->success(null, 'Event deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete event');
        }
    }
}
