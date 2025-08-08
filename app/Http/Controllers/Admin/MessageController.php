<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Institute;

class MessageController extends Controller
{
    public function index()
    {
        Message::where('is_read', false)->update(['is_read' => true]);

        $institutes = Institute::with(['messages' => function($q) {
            $q->latest();
        }])->get();

        $allInstitutes = Institute::orderBy('name')->get();

        return view('admin.messages.index', compact('institutes', 'allInstitutes'));
    }

    public function chat($institute_id)
    {
        $institute = Institute::findOrFail($institute_id);

        $messages = Message::where('institute_id', $institute_id)
                        ->orderBy('created_at')
                        ->get();

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

    public function markAsRead($id)
    {
        $message = Message::findOrFail($id);
        $message->update(['is_read' => true]);

        return redirect()->route('admin.dashboard')->with('success', 'Message marked as read.');
    }

  public function send(Request $request)
{
    $request->validate([
        'institute_id' => 'required|exists:institutes,id',
        'message'      => 'required|string|max:2000',
    ]);

    Message::create([
        'institute_id' => $request->institute_id,
        'admin_id'     => auth()->id(), // ✅ set admin id
        'message'      => $request->message,
        'is_admin'     => true,
        'is_read'      => false,
    ]);

    return redirect()
        ->route('admin.messages.chat', $request->institute_id)
        ->with('success', 'Message sent successfully.');
}

}
