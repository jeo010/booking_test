<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Get all events with pagination, search, and filtering.
     */
    public function index(Request $request)
    {
        $query = Event::with('user', 'tickets');

        // Search by title or location
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->input('start_date'), $request->input('end_date')]);
        }

        // Filter by location
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->input('location') . '%');
        }

        // Sort by date
        $query->orderBy('date', 'asc');

        // Paginate
        $per_page = $request->input('per_page', 15);
        $events = $query->paginate($per_page);

        return response()->json([
            'message' => 'Events retrieved successfully',
            'data' => $events->items(),
            'pagination' => [
                'total' => $events->total(),
                'per_page' => $events->perPage(),
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'from' => $events->firstItem(),
                'to' => $events->lastItem(),
            ],
        ], 200);
    }

    /**
     * Get a single event with its tickets.
     */
    public function show(Event $event)
    {
        return response()->json([
            'message' => 'Event retrieved successfully',
            'data' => $event->load('user', 'tickets'),
        ], 200);
    }

    /**
     * Create a new event (Admin & Organizer only).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'sometimes|string',
            'date' => 'required|date|after:today',
            'location' => 'required|string|max:255',
        ]);

        $validated['created_by'] = $request->user()->id;

        $event = Event::create($validated);

        return response()->json([
            'message' => 'Event created successfully',
            'data' => $event->load('user'),
        ], 201);
    }

    /**
     * Update an event (Admin can update all, Organizer can update their own).
     */
    public function update(Request $request, Event $event)
    {
        $user = $request->user();

        if ($user->role === 'organizer' && $event->created_by !== $user->id) {
            return response()->json(['message' => 'You do not have permission to update this event'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'date' => 'sometimes|date|after:today',
            'location' => 'sometimes|string|max:255',
        ]);

        $event->update($validated);

        return response()->json([
            'message' => 'Event updated successfully',
            'data' => $event,
        ], 200);
    }

    /**
     * Delete an event (Admin can delete all, Organizer can delete their own).
     */
    public function destroy(Request $request, Event $event)
    {
        $user = $request->user();

        if ($user->role === 'organizer' && $event->created_by !== $user->id) {
            return response()->json(['message' => 'You do not have permission to delete this event'], 403);
        }

        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully',
        ], 200);
    }
}
