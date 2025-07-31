<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // Show chat + message form on the "create" page only
    public function create()
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'institute') {
            return redirect()->route('login')->with('error', 'You must be logged in as an institute.');
        }

        $instituteId = $user->id;
        $adminId = 1;

        $messages = Message::where(function($query) use ($instituteId, $adminId) {
            $query->where('institute_id', $instituteId)
                  ->where('admin_id', $adminId);
        })->orWhere(function($query) use ($instituteId, $adminId) {
            $query->where('institute_id', $adminId)
                  ->where('admin_id', $instituteId);
        })->orderBy('created_at', 'asc')->get();

        return view('institute.messages.create', compact('messages'));
    }

    // Store a new message
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'institute') {
            return redirect()->route('login')->with('error', 'You must be logged in as an institute.');
        }

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        try {
            Message::create([
                'institute_id' => $user->id,
                'admin_id' => 1, // adjust as needed
                'message' => $validated['message'],
            ]);

            return redirect()->route('institute.message.create')->with('success', 'Message sent successfully.');
        } catch (\Exception $e) {
            \Log::error('Institute message store error: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->withErrors('An unexpected error occurred while sending your message. Please try again later.');
        }
 
 
   }

   public function markAsRead($id)
{
    $message = Message::findOrFail($id);
    $message->update(['is_read' => true]);

    return redirect()->route('admin.dashboard')->with('success', 'Message marked as read.');
}

}
