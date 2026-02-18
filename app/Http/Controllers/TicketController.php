<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Event;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Create a new ticket for an event (Organizer only).
     */
    public function store(Request $request, Event $event)
    {
        $user = $request->user();

        // Check if organizer owns the event
        if ($user->role === 'organizer' && $event->created_by !== $user->id) {
            return response()->json(['message' => 'You do not have permission to create tickets for this event'], 403);
        }

        $validated = $request->validate([
            'type' => 'required|in:vip,standard',
            'price' => 'required|integer|min:0',
            'quantity' => 'required|integer|min:1',
        ]);

        $validated['event_id'] = $event->id;
        $ticket = Ticket::create($validated);

        return response()->json([
            'message' => 'Ticket created successfully',
            'data' => $ticket,
        ], 201);
    }

    /**
     * Update a ticket (Organizer can update if they own event, Admin can update all).
     */
    public function update(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        $event = $ticket->event;

        if ($user->role === 'organizer' && $event->created_by !== $user->id) {
            return response()->json(['message' => 'You do not have permission to update this ticket'], 403);
        }

        $validated = $request->validate([
            'type' => 'sometimes|in:vip,standard',
            'price' => 'sometimes|integer|min:0',
            'quantity' => 'sometimes|integer|min:0',
        ]);

        $ticket->update($validated);

        return response()->json([
            'message' => 'Ticket updated successfully',
            'data' => $ticket,
        ], 200);
    }

    /**
     * Delete a ticket (Organizer can delete if they own event, Admin can delete all).
     */
    public function destroy(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        $event = $ticket->event;

        if ($user->role === 'organizer' && $event->created_by !== $user->id) {
            return response()->json(['message' => 'You do not have permission to delete this ticket'], 403);
        }

        $ticket->delete();

        return response()->json([
            'message' => 'Ticket deleted successfully',
        ], 200);
    }
}
