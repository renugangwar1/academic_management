<?php


namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use Illuminate\Support\Facades\Auth;

class ProgramController extends Controller
{
    /**
     * Display a list of programs mapped to the logged-in institute.
     */
   public function index()
{
    $user = Auth::user();

    if (!$user || $user->role !== 'institute') {
        return redirect()->route('login')->with('error', 'You must be logged in as an institute.');
    }

    $instituteId = $user->id;


    $programs = Program::with('courses')
        ->whereHas('institutes', function ($query) use ($instituteId) {
            $query->where('institutes.id', $instituteId);
        })
        ->orderBy('name')
        ->get();

    return view('institute.programs.index', compact('programs'));
}


    /**
     * View specific program details (optional method, similar to admin).
     */
    public function show($id)
    {
        $instituteId = Auth::guard('institute')->id();

        $program = Program::with(['courses'])
            ->whereHas('institutes', function ($query) use ($instituteId) {
                $query->where('institutes.id', $instituteId);
            })
            ->findOrFail($id);

        return view('institute.programs.show', compact('program'));
    }
}
