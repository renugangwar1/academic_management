<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AcademicSession, Student, Program, Institute, Course};
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Services\Grading\{CourseGrade, SemesterGpa};
use App\Models\ProgramResultGroup;
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
public function indexRegular()
{
    return view('admin.examination.regular.index');
}


public function uploadMarksRegular(AcademicSession $session)
{
    abort_if($session->type !== 'regular', 404);

    $academicSessions = AcademicSession::where('type', 'regular')
        ->orderByDesc('year')->get();

    $programs = Program::whereIn('id', function ($q) use ($session) {
        $q->select('program_id')
          ->from('academic_session_program')
          ->where('academic_session_id', $session->id)
          ->where('structure', 'semester');
    })->orderBy('name')->get();

    return view('admin.examination.regular.upload-marks', [
        'session'          => $session,
        'academicSessions' => $academicSessions,
        'programs'         => $programs,
        'previewData'      => session('previewData', []),
        'columns'          => session('columns', []),
        'markType'         => session('markType', ''),
    ]);
}


public function uploadMarksDiploma(AcademicSession $session)
{
    abort_if($session->type !== 'diploma', 404);

    $academicSessions = AcademicSession::where('type', 'diploma')
                       ->orderByDesc('year')
                       ->get();

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

    $programs = Program::whereIn('id', function ($query) use ($session) {
        $query->select('program_id')
              ->from('academic_session_program')
              ->where('academic_session_id', $session->id);
    })->orderBy('name')->get();

    // 🔧 Add this to fix the error
    $academicSessions = AcademicSession::where('type', $session->type)
                            ->orderByDesc('year')->get(['id', 'year']);

    $view = $session->type === 'diploma'
        ? 'admin.examination.diploma.admit-card'
        : 'admin.examination.regular.admit-card';

    return view($view, [
        'session'           => $session,
        'institutes'        => $institutes,
        'programs'          => $programs,
        'academicSessions'  => $academicSessions, // ✅ this line fixes the Blade error
    ]);
}


public function downloadBulkAdmitCards(Request $request)
{
    $validated = $request->validate([
        'session_id'   => 'required|exists:academic_sessions,id',
        'institute_id' => 'required|exists:institutes,id',
        'program_id'   => 'required|exists:programs,id',
        'semester'     => 'nullable|integer|min:1|max:10',
        'year'         => 'nullable|integer|min:1|max:6',
    ]);

    $session = AcademicSession::findOrFail($validated['session_id']);
    $program = Program::findOrFail($validated['program_id']);
    $structure = $program->structure ?? 'semester';

    // ✅ Define these to avoid "undefined variable" in compact()
    $semester = $validated['semester'] ?? null;
    $year = $validated['year'] ?? null;

    $level = $structure === 'yearly' ? $year : $semester;

    if (!$level) {
        return back()->with('error', ucfirst($structure) . ' value is required.');
    }

    // Mapping check
    $isMapped = DB::table('academic_session_program')
        ->where('academic_session_id', $session->id)
        ->where('program_id', $program->id)
        ->exists();

    if (!$isMapped) {
        return back()->with('error', 'Programme not mapped to the selected session.');
    }

    // Fetch students
    $students = Student::with([
        'institute:id,name',
        'program:id,name,structure',
        'appearingCourses' => function ($q) use ($structure, $level) {
            $q->whereHas('programs', function ($q2) use ($structure, $level) {
                $q2->where("course_program.$structure", $level);
            });
            $q->select('courses.id', 'course_code', 'course_title');
        }
    ])
    ->where([
        ['program_id', $program->id],
        ['institute_id', $validated['institute_id']],
        ['academic_session_id', $session->id],
    ])
    ->orderBy('nchm_roll_number')
    ->get()
    ->filter(function ($student) use ($structure, $level) {
        return $student->getPassedAppearingCourses($level)->isNotEmpty();
    })->values();

    if ($students->isEmpty()) {
        return back()->with('error', 'No eligible students found — all failed mid-term or missing subjects.');
    }

    // ✅ This now works safely
    $pdf = Pdf::loadView('pdf.bulk_admitcards', compact(
        'students', 'session', 'program', 'structure', 'semester', 'year'
    ));

    return $pdf->download('admitcards_' . now()->format('Ymd_His') . '.pdf');
}



public function downloadSingleAdmitCard(Request $request)
{
    $validated = $request->validate([
        'session_id'       => 'required|exists:academic_sessions,id',
        'nchm_roll_number' => 'required|string',
        'semester'         => 'nullable|integer|min:1|max:10',
        'year'             => 'nullable|integer|min:1|max:6',
    ]);

    $session = AcademicSession::findOrFail($validated['session_id']);

    $student = Student::with([
        'institute:id,name',
        'program:id,name,structure',
        'appearingCourses' => function ($q) use ($validated) {
            if (isset($validated['semester'])) {
                $q->whereHas('programs', function ($q2) use ($validated) {
                    $q2->where('course_program.semester', $validated['semester']);
                });
            } elseif (isset($validated['year'])) {
                $q->whereHas('programs', function ($q2) use ($validated) {
                    $q2->where('course_program.year', $validated['year']);
                });
            }
            $q->select('courses.id', 'course_code', 'course_title');
        }
    ])
    ->where('nchm_roll_number', $validated['nchm_roll_number'])
    ->where('academic_session_id', $session->id)
    ->first();

    if (!$student) {
        return back()->with('error', 'Student not found for the given Roll Number and Session.');
    }

    $program = $student->program;
    $structure = $program->structure ?? 'semester';
    $level = $structure === 'yearly' ? $validated['year'] : $validated['semester'];
  $semester = $validated['semester'] ?? null;
    $year = $validated['year'] ?? null;
    if (!$level) {
        return back()->with('error', ucfirst($structure) . ' value is required.');
    }

    if ($student->getPassedAppearingCourses($level)->isEmpty()) {
        return back()->with('error', 'Mid-term not cleared for the selected ' . $structure . ' — Admit Card not available.');
    }
  // ✅ This now works safely
  $pdf = Pdf::loadView('pdf.admitcard', compact(
    'student', 'session', 'program', 'structure', 'semester', 'year'
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
public function showResultPageRegular(AcademicSession $session, Request $request)
{
    abort_if($session->type !== 'regular', 404);

    $sessions = AcademicSession::where('type', 'regular')
                               ->orderByDesc('year')
                               ->get(['id','year']);

    $programs = Program::whereIn('id', function ($q) use ($session) {
        $q->select('program_id')
          ->from('academic_session_program')
          ->where('academic_session_id', $session->id)
          ->where('structure', 'semester');
    })->orderBy('name')->get();

    $programId = $request->input('program_id');
    $semester  = $request->input('semester');

    if ($programId && $semester) {
        $program = Program::findOrFail($programId);
        $matrix = app(ResultsController::class)->marksMatrix($programId, $semester);

        return view('admin.examination.regular.results', array_merge($matrix, [
            'session'   => $session,
            'sessions'  => $sessions,
            'programs'  => $programs,
            'program'   => $program,
            'programId' => $programId,
            'semester'  => $semester,
        ]));
    }

    return view('admin.examination.regular.results', compact(
        'session', 'sessions', 'programs'
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
  
    
public function showRegular(Request $request)
{
    $programId = $request->query('program_id');
    $semester  = $request->query('semester');

    $students = Student::query()
        ->where('program_id', $programId)
        ->where('semester', $semester)
        ->orderBy('nchm_roll_number')
        ->get();

    $programName = optional($students->first()?->program)->name ?? 'N/A';

    $academicSessions = AcademicSession::where('type', 'regular')
        ->orderByDesc('year')
        ->get(['id', 'year']);

    return view('admin.examination.regular.result-view', [
        'students'          => $students,
        'programName'       => $programName,
        'semester'          => $semester,
        'institutes'        => Institute::all(),
        'programs'          => Program::all(),
        'academicSessions'  => $academicSessions,
    ]);
}

}
