<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Institute;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index()
    {
        // Mark all messages as read
        Message::where('is_read', false)->update(['is_read' => true]);

        // Get all institutes with their latest message
        $institutes = Institute::with(['messages' => function($q) {
            $q->latest();
        }])->get();

        return view('admin.messages.index', compact('institutes'));
    }

    public function chat($institute_id)
    {
        $institute = Institute::findOrFail($institute_id);

        $messages = Message::where('institute_id', $institute_id)
                        ->orderBy('created_at')
                        ->get();

        // Mark unread messages from this institute as read
        Message::where('institute_id', $institute_id)
               ->where('is_read', false)
               ->update(['is_read' => true]);

        return view('admin.messages.chat', compact('institute', 'messages'));
    }

public function reply(Request $request, $id)
{
    $request->validate([
        'message' => 'required|string|max:1000',
    ]);

    $user = auth()->user();

    // Optional: Check if the user is an admin using role
    if ($user->role !== 'admin') {
        return redirect()->back()->with('error', 'Unauthorized access.');
    }

    Message::create([
        'institute_id' => $id,
        'admin_id'     => $user->id,
        'message'      => $request->message,
        'is_admin'     => true,
        'is_read'      => false,
    ]);

    return redirect()->back()->with('success', 'Message sent!');
}


}

