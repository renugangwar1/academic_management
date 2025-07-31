<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AcademicSession, Student, Program, Institute, Course};
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Services\Grading\{CourseGrade, SemesterGpa};
use App\Models\ProgramResultGroup;
use Illuminate\Support\Collection;

/**
 * Examination‑related operations for **Regular** programmes only.
 * All Diploma‑specific logic has been removed.
 */
class ExaminationController extends Controller
{
    /* ───────────────── Dashboard landing (Regular only) ───────────────── */
// public function indexRegular(Request $request)
// {
//     $academicYear = $request->input('academic_year', now()->format('Y') . '-' . now()->addYear()->format('y'));

//     // Fetch groups the same way you did before
//     $groups = ProgramResultGroup::where('academic_year', $academicYear)
//                 ->with('program')
//                 ->orderBy('program_id')
//                 ->orderBy('semester')
//                 ->get();

//     return view('examination.regular.calculated-results', compact('groups', 'academicYear'));
// }
public function indexDiploma()
{
    return view('admin.examination.diploma.index');
}

// public function indexRegular()
// {
//     $academicSession = AcademicSession::where('type', 'regular')->latest()->first();

//     return view('admin.examination.regular.index', compact('academicSession'));
// }


public function indexRegular()
{
    $academicSession = AcademicSession::where('type', 'regular')->latest()->first();

    // ✅ Ensure the session ID is set
    if ($academicSession) {
        session(['exam_session_id' => $academicSession->id]);
    }

    return view('admin.examination.regular.index', compact('academicSession'));
}
// public function indexRegular()
// {
//     // Group external_results by program, semester, and academic_session_id
//     $groups = \App\Models\ExternalResult::select(
//             'program_id',
//             'semester',
//             'academic_session_id'
//         )
//         ->with(['program', 'academicSession']) // Eager load relationships
//         ->groupBy('program_id', 'semester', 'academic_session_id')
//         ->get();

//     // Load sessions for promotion modal
//     $sessions = \App\Models\AcademicSession::orderBy('year', 'desc')->get();

//     // return view('admin.examination.regular.all-results', compact('groups', 'sessions'));
//      return view('admin.examination.regular.index', compact('groups','sessions'));
// }


public function uploadMarksRegular($sessionId)
{
    $session = AcademicSession::findOrFail($sessionId); // ✅ Ensure full model is fetched

    $academicSessions = AcademicSession::where('type', 'regular')
        ->orderByDesc('year')
        ->get(['id', 'year', 'term', 'odd_even', 'type']);

    $programs = Program::where('structure', 'semester')->orderBy('name')->get();

    return view('admin.examination.regular.upload-marks', [
        'session'          => $session, // ✅ This is what your view needs
        'academicSessions' => $academicSessions,
        'programs'         => $programs,
        'previewData'      => session('previewData', []),
        'columns'          => session('columns', []),
        'markType'         => session('markType', ''),
        'programId'        => session('programId'),
        'semester'         => session('semester'),
    ]);
}


public function uploadMarksDiploma(AcademicSession $session)
{
    abort_if($session->type !== 'diploma', 404);

   $academicSessions = AcademicSession::where('type', $session->type)
                    ->orderByDesc('year')
                    ->get(['id', 'year', 'term', 'odd_even', 'type']);
    // Get only programs mapped to this session and filtered by structure
   $programs = Program::whereIn('id', function ($query) use ($session) {
    $query->select('program_id')
        ->from('academic_session_program')
        ->where('academic_session_id', $session->id)
        ->where('structure', 'yearly'); 
})->orderBy('name')->get();

    return view('admin.examination.diploma.upload-marks', compact('session', 'academicSessions', 'programs'));
}



public function generateAdmitCard(AcademicSession $session)
{
    $institutes = Institute::all();

    // Filter programs by structure according to session type
   $programs = Program::where('structure', 'semester')->orderBy('name')->get();



    $academicSessions = AcademicSession::orderByDesc('year')
        ->get(['id', 'year', 'term', 'odd_even', 'type']);

    $view = $session->type === 'diploma'
        ? 'admin.examination.diploma.admit-card'
        : 'admin.examination.regular.admit-card';

    return view($view, [
        'session'           => $session,
        'institutes'        => $institutes,
        'programs'          => $programs,
        'academicSessions'  => $academicSessions,
    ]);
}

public function downloadBulkAdmitCards(Request $request)
{
    $validated = $request->validate([
        'academic_session_id' => 'required|exists:academic_sessions,id',
        'institute_id'        => 'required|exists:institutes,id',
        'program_id'          => 'required|exists:programs,id',
        'semester'            => 'nullable|integer|min:1|max:10',
        'year'                => 'nullable|integer|min:1|max:6',
    ]);

    $session = AcademicSession::findOrFail($validated['academic_session_id']);

    // Get the selected program with pivot data from academic_session_program
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
            ['institute_id', $validated['institute_id']],
            ['academic_session_id', $session->id],
        ])
        ->orderBy('nchm_roll_number')
        ->get()
        ->filter(fn($student) => $student->getPassedAppearingCourses($level)->isNotEmpty())
        ->values();

    if ($students->isEmpty()) {
        return back()->with('error', 'No eligible students found — all failed mid-term or missing subjects.');
    }

    $pdf = Pdf::loadView('pdf.bulk_admitcards', compact(
        'students', 'session', 'program', 'structure', 'level'
    ));

    return $pdf->download('admitcards_' . now()->format('Ymd_His') . '.pdf');
}


// public function downloadSingleAdmitCard(Request $request)
// {
//     $validated = $request->validate([
//         'academic_session_id' => 'required|exists:academic_sessions,id',
//         'nchm_roll_number'    => 'required|string',
//         'semester'            => 'nullable|integer|min:1|max:10',
//         'year'                => 'nullable|integer|min:1|max:6',
//     ]);

//     $session = AcademicSession::findOrFail($validated['academic_session_id']);
//    $programs = Program::where('structure', 'semester')->orderBy('name')->get();
//     $student = Student::with([
//             'institute:id,name',
//             'program:id,name',
//             'appearingCourses' => function ($q) use ($validated) {
//                 if ($validated['semester']) {
//                     $q->whereHas('programs', fn($q2) => $q2->where('course_program.semester', $validated['semester']));
//                 } elseif ($validated['year']) {
//                     $q->whereHas('programs', fn($q2) => $q2->where('course_program.year', $validated['year']));
//                 }
//                 $q->select('courses.id', 'course_code', 'course_title');
//             }
//         ])
//         ->where('nchm_roll_number', $validated['nchm_roll_number'])
//         ->where('academic_session_id', $session->id)
//         ->first();

//     if (!$student) {
//         return back()->with('error', 'Student not found for the given Roll Number and Academic Session.');
//     }

//     $program = $session->programs()->where('programs.id', $student->program_id)->first();
//     $structure = $program?->pivot->structure ?? 'semester';
//     $level = $structure === 'yearly' ? $validated['year'] : $validated['semester'];

//     if (!$level) {
//         return back()->with('error', ucfirst($structure) . ' value is required.');
//     }

//     if ($student->getPassedAppearingCourses($level)->isEmpty()) {
//         return back()->with('error', 'Mid-term not cleared for the selected ' . $structure . ' — Admit Card not available.');
//     }

//     $pdf = Pdf::loadView('pdf.admitcard', compact(
//         'student', 'session', 'program', 'structure', 'level'
//     ));

//     return $pdf->download('admitcard_' . $student->nchm_roll_number . '.pdf');
// }
public function downloadSingleAdmitCard(Request $request)
{
    $validated = $request->validate([
        'academic_session_id' => 'required|exists:academic_sessions,id',
        'nchm_roll_number'    => 'required|string',
        'semester'            => 'nullable|integer|min:1|max:10',
        'year'                => 'nullable|integer|min:1|max:6',
    ]);

    $session = AcademicSession::findOrFail($validated['academic_session_id']);
    $structure = 'semester'; // or 'yearly'
    $level = $structure === 'yearly' ? $validated['year'] : $validated['semester'];

    if (!$level) {
        return back()->with('error', ucfirst($structure) . ' value is required.');
    }

    // Step 1: Try fetching from students
    $student = Student::with(['program', 'institute'])
        ->where('nchm_roll_number', $validated['nchm_roll_number'])
        ->where(function ($query) use ($session) {
            $query->where('academic_session_id', $session->id)
                  ->orWhere('original_academic_session_id', $session->id);
        })
        ->first();

    // Step 2: If not found, try history
    if (!$student) {
        $history = \App\Models\StudentSessionHistory::with(['student.program', 'student.institute'])
            ->whereHas('student', fn($q) =>
                $q->where('nchm_roll_number', $validated['nchm_roll_number']))
            ->where('academic_session_id', $session->id)
            ->where('semester', $level)
            ->first();

        if (!$history) {
            return back()->with('error', 'No record found for given Roll Number and Academic Session.');
        }

        $student = $history->student;
        $program = $student->program;
        $structure = 'semester';
    } else {
        $program = $student->program;
    }

    // Ensure student is eligible for admit card
    if (method_exists($student, 'getPassedAppearingCourses') &&
        $student->getPassedAppearingCourses($level)->isEmpty()) {
        return back()->with('error', 'Mid-term not cleared for the selected ' . $structure . ' — Admit Card not available.');
    }

    $pdf = Pdf::loadView('pdf.admitcard', compact(
        'student', 'session', 'program', 'structure', 'level'
    ));

    return $pdf->download('admitcard_' . $student->nchm_roll_number . '.pdf');
}


    /* ─────────────── REGULAR RESULTS ─────────────── */
// public function showResultPageRegular(AcademicSession $session)
// {
//     abort_if($session->type !== 'regular', 404);

//     $programs = Program::whereIn('id', function ($q) use ($session) {
//         $q->select('program_id')
//           ->from('academic_session_program')
//           ->where('academic_session_id', $session->id);
//     })->orderBy('name')->get();

//     return view('admin.examination.regular.results', compact('session', 'programs'));
// }

/* ─────────────── REGULAR → “Process Results” landing ─────────────── */



// // public function showResultPageRegular(Request $request)
// // {
// //     $sessionId = session('exam_session_id');

// //     if (!$sessionId) {
// //         return redirect()->back()->with('error', 'No academic session selected.');
// //     }

// //     $session = AcademicSession::find($sessionId);

// //     if (!$session || $session->type !== 'regular') {
// //         abort(404);
// //     }

// //     $academicSessions = AcademicSession::where('type', 'regular')
// //         ->orderByDesc('year')
// //         ->get(['id', 'year', 'term', 'odd_even', 'type']);

// //     $programs = Program::whereIn('id', function ($q) use ($session) {
// //         $q->select('program_id')
// //             ->from('academic_session_program')
// //             ->where('academic_session_id', $session->id)
// //             ->where('structure', 'semester');
// //     })->orderBy('name')->get();

// //     $programId = $request->input('program_id');
// //     $semester  = $request->input('semester');

// //     if ($programId && $semester) {
// //         $program = Program::findOrFail($programId);
// //         $matrix  = app(ResultsController::class)->marksMatrix($programId, $semester);

// //         return view('admin.examination.regular.results', array_merge($matrix, [
// //             'session'          => $session,
// //             'academicSessions' => $academicSessions,
// //             'programs'         => $programs,
// //             'program'          => $program,
// //             'programId'        => $programId,
// //             'semester'         => $semester,
// //         ]));
// //     }

// //    return view('admin.examination.regular.results', compact(
// //     'session', 'academicSessions', 'programs'
// // ));

// }

public function showResultPageRegular(Request $request)
{
    $sessionId = session('exam_session_id');

    if (!$sessionId) {
        return redirect()->back()->with('error', 'No academic session selected.');
    }

    $session = AcademicSession::find($sessionId);

    if (!$session || $session->type !== 'regular') {
        abort(404);
    }

    $academicSessions = AcademicSession::where('type', 'regular')
        ->orderByDesc('year')
        ->get(['id', 'year', 'term', 'odd_even', 'type']);

    $programs = Program::whereIn('id', function ($q) use ($session) {
        $q->select('program_id')
            ->from('academic_session_program')
            ->where('academic_session_id', $session->id)
            ->where('structure', 'semester');
    })->orderBy('name')->get();

    $programId = $request->input('program_id');
    $semester  = $request->input('semester');
    $selectedSessionId = $request->input('academic_session_id');

    if ($programId && $semester && $selectedSessionId) {
        $students = Student::with([
                'program',
                'marks' => function ($query) use ($selectedSessionId, $semester) {
                    $query->where('session_id', $selectedSessionId)
                        ->where('semester', $semester);
                }
            ])
            ->where('academic_session_id', $selectedSessionId)
            ->where('program_id', $programId)
            ->orderBy('name')
            ->get();

        $courseIds = $students->flatMap(fn($student) => $student->marks->pluck('course_id'))
                              ->unique()
                              ->values();

        $courses = Course::whereIn('id', $courseIds)->get();

        return view('admin.examination.regular.results', compact(
            'session',
            'academicSessions',
            'programs',
            'programId',
            'semester',
            'selectedSessionId',
            'students',
            'courses'
        ));
    }

    return view('admin.examination.regular.results', compact(
        'session',
        'academicSessions',
        'programs'
    ));
}




    /* ───────────────────────────────────────────────────
       Shared AJAX helper
    ─────────────────────────────────────────────────── */
    public function fetchCourses(Request $request)
    {
        $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
        ]);

        $academicSession = AcademicSession::find($request->academic_session_id);

        return response()->json([
            'courses'  => Course::all(),       // tweak if you need filtering
            'semester' => $academicSession->semester,
            'year'     => $academicSession->year,
        ]);
    }




    /* ───────────────── PRIVATE HELPERS ───────────────── */

    /** Render upload‑marks page for given type */
    private function renderUploadMarks(AcademicSession $session, string $type)
    {
        $academicSessions = AcademicSession::orderByDesc('year')->get();
        $courses  = collect();
        $programs = Program::all();

        return view("admin.examination.$type.upload-marks",
            compact('session', 'academicSessions', 'courses', 'programs'));
    }

    /** Render admit‑card page for given type */
    private function renderAdmitCard(AcademicSession $session, string $type)
    {
        $institutes = Institute::all();
        $programs   = Program::all();

        return view("admin.examination.$type.admit-card",
            compact('session', 'institutes', 'programs'));
    }

// public function showRegular(Request $request)
// {
//     $academicSessionId = $request->input('academic_session_id');
//     $programId         = $request->input('program_id');
//     $semester          = $request->input('semester');
// $institutes = \App\Models\Institute::all();
//     // Load dropdown data
//     $academicSessions = \App\Models\AcademicSession::all();
//     $programs         = \App\Models\Program::all();
// $programName = \App\Models\Program::find($programId)?->name;
//     // No filters yet – just render the page
//     if (!$academicSessionId || !$programId || !$semester) {
//         return view('admin.examination.results.index', compact(
//             'academicSessions', 'programs'
//         ));
//     }

//     // Fetch students with their marks
//     $students = \App\Models\Student::with([
//             'program',
//             'marks' => function ($query) use ($academicSessionId, $semester) {
//                 $query->where('session_id', $academicSessionId)
//                       ->where('semester', $semester)
//                       ->with('course');
//             }
//         ])
//         ->where('academic_session_id', $academicSessionId)
//         ->where('program_id', $programId)
//         ->orderBy('name')
//         ->get();

//     // Extract all distinct course IDs
//     $courseIds = $students->flatMap(fn($student) => $student->marks->pluck('course_id'))
//                           ->unique()
//                           ->values();

//     // Load those course models
//     $courses = \App\Models\Course::whereIn('id', $courseIds)->get();

//    return view('admin.examination.regular.result-view', compact(
//     'students',
//     'courses',
//     'academicSessions',
//     'programs',
//     'academicSessionId',
//     'programId',
//     'semester',
//     'programName',
//      'institutes' 
// ));
// }


public function showRegular(Request $request)
{
    $academicSessionId = $request->input('academic_session_id');
    $programId         = $request->input('program_id');
    $semester          = $request->input('semester');

    $institutes        = \App\Models\Institute::all();
    $academicSessions  = \App\Models\AcademicSession::orderByDesc('year')->get();
    $programs          = \App\Models\Program::all();
    $programName       = \App\Models\Program::find($programId)?->name;

    // Return early if missing inputs
    if (!$academicSessionId || !$programId || !$semester) {
        return view('admin.examination.regular.result-view', compact(
            'academicSessions', 'programs', 'institutes',
            'academicSessionId', 'programId', 'semester'
        ));
    }

    // Fetch students with filtered marks
    $students = \App\Models\Student::with([
            'program',
            'marks' => function ($query) use ($academicSessionId, $semester) {
                $query->where('session_id', $academicSessionId)
                      ->where('semester', $semester)
                      ->with('course');
            }
        ])
        ->where('academic_session_id', $academicSessionId)
        ->where('program_id', $programId)
        ->orderBy('name')
        ->get();

    // Distinct course IDs used in marks
    $courseIds = $students->flatMap(fn($student) => $student->marks->pluck('course_id'))
                          ->unique()
                          ->values();

    $courses = \App\Models\Course::whereIn('id', $courseIds)->get();

    return view('admin.examination.regular.result-view', compact(
        'students',
        'courses',
        'academicSessions',
        'programs',
        'academicSessionId',
        'programId',
        'semester',
        'programName',
        'institutes'
    ));
}



}
