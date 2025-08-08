<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CalendarEvent; 

class CalendarController extends Controller
{
    public function index()
    {
        return view('admin.calendar.index');
    }

  
  public function fetchEvents()
{
    // Only return events for the next 30 days
    $events = CalendarEvent::whereBetween('start', [now(), now()->addDays(30)])
        ->get()
        ->map(function ($event) {
            return [
                'id'    => $event->id,
                'title' => $event->title,
                'start' => \Carbon\Carbon::parse($event->start)->toIso8601String(),
                'end'   => $event->end ? \Carbon\Carbon::parse($event->end)->toIso8601String() : null,
            ];
        });

    return response()->json($events);
}


    public function storeEvent(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end'   => 'nullable|date|after_or_equal:start',
        ]);
   $startDate = \Carbon\Carbon::parse($request->start);
    $endDate = $request->end 
        ? \Carbon\Carbon::parse($request->end) 
        : $startDate->copy()->addDays(30);
        $event = CalendarEvent::create([
            'title' => $request->title,
            'start' => $request->start,
            'end'   => $request->end ?? $request->start, // fallback to same day
        ]);

        return response()->json($event);
    }
}

