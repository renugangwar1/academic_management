<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicSession;
use App\Models\Program;
use App\Models\Student;
use PDF;

class AdmitCardController extends Controller
{
    public function index()
    {
       $user = auth('institute')->user();
// Correct auth guard

        $academicSessions = AcademicSession::orderByDesc('year')
            ->get(['id', 'year', 'term', 'odd_even', 'type']);

        $programs = Program::where('structure', 'semester')
            ->orderBy('name')
            ->get();

        $firstProgram = $programs->first();
        $level = $firstProgram?->level ?? null;

        $session = AcademicSession::latest()->first();

        return view('institute.admitcards.index', compact(
            'academicSessions',
            'programs',
            'session',
            'level'
        ));
    }

    public function bulkDownload(Request $request)
    {
      $user = auth('institute')->user();
 // Correct institute auth

        $validated = $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'program_id' => 'required|exists:programs,id',
            'semester' => 'nullable|integer|min:1|max:10',
            'year' => 'nullable|integer|min:1|max:6',
        ]);

        $session = AcademicSession::findOrFail($validated['academic_session_id']);

        // Fetch program with structure from pivot
        $program = $session->programs()->where('programs.id', $validated['program_id'])->first();
        if (!$program) {
            return back()->with('error', 'Program not found for the selected Academic Session.');
        }

        $structure = $program->pivot->structure ?? 'semester';
        $level = $structure === 'yearly' ? $validated['year'] : $validated['semester'];

        if (!$level) {
            return back()->with('error', ucfirst($structure) . ' value is required.');
        }

        $students = Student::with([
                'institute:id,name',
                'program:id,name',
                'appearingCourses' => function ($q) use ($structure, $level) {
                    $q->whereHas('programs', function ($q2) use ($structure, $level) {
                        $q2->where("course_program.$structure", $level);
                    })->select('courses.id', 'course_code', 'course_title');
                }
            ])
            ->where([
                ['program_id', $program->id],
              ['institute_id', $user->id],

                ['academic_session_id', $session->id],
            ])
            ->orderBy('nchm_roll_number')
            ->get()
            ->filter(fn($student) => $student->getPassedAppearingCourses($level)->isNotEmpty())
            ->values();

        if ($students->isEmpty()) {
            return back()->with('error', 'No eligible students found — all failed mid-term or missing subjects.');
        }

        $pdf = PDF::loadView('pdf.bulk_admitcards', compact(
            'students', 'session', 'program', 'structure', 'level'
        ));

        return $pdf->download('admitcards_' . now()->format('Ymd_His') . '.pdf');
    }

    public function singleDownload(Request $request)
    {
       $user = auth('institute')->user();
 // Correct auth

        $validated = $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'nchm_roll_number' => 'required|string',
            'semester' => 'nullable|integer|min:1|max:10',
            'year' => 'nullable|integer|min:1|max:6',
        ]);

        $session = AcademicSession::findOrFail($validated['academic_session_id']);
        $structure = $validated['year'] ? 'yearly' : 'semester';
        $level = $structure === 'yearly' ? $validated['year'] : $validated['semester'];

        if (!$level) {
            return back()->with('error', ucfirst($structure) . ' value is required.');
        }

        // Step 1: Try main student table
        $student = Student::with(['program', 'institute', 'appearingCourses' => function ($q) use ($structure, $level) {
                $q->whereHas('programs', fn($q2) => $q2->where("course_program.$structure", $level))
                    ->select('courses.id', 'course_code', 'course_title');
            }])
            ->where('nchm_roll_number', $validated['nchm_roll_number'])
            ->where(function ($q) use ($session) {
                $q->where('academic_session_id', $session->id)
                  ->orWhere('original_academic_session_id', $session->id);
            })
            ->where('institute_id', $institute->id)
            ->first();

        // Step 2: Try from session history
        if (!$student) {
            $history = \App\Models\StudentSessionHistory::with(['student.program', 'student.institute'])
                ->whereHas('student', fn($q) =>
                    $q->where('nchm_roll_number', $validated['nchm_roll_number'])
                      ->where('institute_id', $institute->id))
                ->where('academic_session_id', $session->id)
                ->where($structure, $level)
                ->first();

            if (!$history) {
                return back()->with('error', 'No record found for given Roll Number and Academic Session.');
            }

            $student = $history->student;
            $program = $student->program;
        } else {
            $program = $student->program;
        }

        // Eligibility check
        if (method_exists($student, 'getPassedAppearingCourses') &&
            $student->getPassedAppearingCourses($level)->isEmpty()) {
            return back()->with('error', 'Mid-term not cleared for the selected ' . $structure . ' — Admit Card not available.');
        }

        $pdf = PDF::loadView('pdf.admitcard', compact(
            'student', 'session', 'program', 'structure', 'level'
        ));

        return $pdf->download('admitcard_' . $student->nchm_roll_number . '.pdf');
    }
}
