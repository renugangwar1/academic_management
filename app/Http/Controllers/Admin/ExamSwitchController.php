<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicSession;

class ExamSwitchController extends Controller
{
    /**
     * Persist the chosen academic‑session *per programme‑type*.
     *
     * Rules:
     * 1.  If the user explicitly posts a `session_id` that belongs to the given `$type`, we store it.
     * 2.  Otherwise, we *keep* the existing stored session – **no automatic overwrite** –
     *     unless none exists yet.
     * 3.  If we still don’t have a session for this type, fall back to the *oldest* active one.
     */
   public function switch(string $type, Request $request)
{
    abort_if(!in_array($type, ['regular', 'diploma']), 404);

    // Only store the session if explicitly provided in request
    if ($request->filled('session_id')) {
        $chosen = AcademicSession::where('id', $request->input('session_id'))
                   ->where('type', $type)
                   ->firstOrFail();

        session([
            'exam_type'       => $type,
            'exam_session_id' => $chosen->id,
        ]);
    }

    // DO NOT do anything if session_id is not provided
    // and do NOT auto-select any fallback session

    return $type === 'regular'
        ? redirect()->route('admin.regular.exams.index')
        : redirect()->route('admin.diploma.exams.index');
}


    /**
     * Card dashboard showing the two programme types.
     * Always displays the *oldest* active session for each type (just informational).
     */
    public function dashboard()
    {
        $regularSession = AcademicSession::where('active', 1)
                          ->where('type', 'regular')
                          ->oldest('id')
                          ->first();

        $diplomaSession = AcademicSession::where('active', 1)
                          ->where('type', 'diploma')
                          ->oldest('id')
                          ->first();

        return view('admin.examination.dashboard', compact('regularSession', 'diplomaSession'));
    }
    

    
}