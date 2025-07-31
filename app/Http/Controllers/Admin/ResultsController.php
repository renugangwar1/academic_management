<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicSession;   
use App\Models\Program;
use App\Models\Student;
use App\Models\Mark;
use App\Models\InternalResult;
use App\Models\ExternalResult;
use App\Models\ResultsSummary;
use App\Exports\MatrixMarksExport;
use App\Services\Grading\{CourseGrade, SemesterGpa};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; 
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExternalResultsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AggregatedResult;
use Illuminate\Support\Facades\Log;
use App\Models\Course;
use App\Models\Institute;


class ResultsController extends Controller
{
    /* ------------------------------------------------------------------ */
    /*  Resource placeholders (index, create, store …)                    */
    /* ------------------------------------------------------------------ */

    public function index()    {}
    public function create()   {}
    public function store(Request $request) {}
    public function show(string $id) {}
    public function edit(string $id) {}
    public function update(Request $request, string $id) {}
    public function destroy(string $id) {}

    /* ------------------------------------------------------------------ */
    /*  Custom methods                                                    */
    /* ------------------------------------------------------------------ */

    /** Show the “Process Results” page for one academic session */
    public function showResultPage($sessionId)
    {
       $session   = AcademicSession::findOrFail($sessionId);
    $programs  = Program::with('courses')->get();
    $sessions  = AcademicSession::where('type', 'regular')
                    ->orderByDesc('year')
                    ->get(['id', 'year']);
  $institutes = Institute::all();
    return view('admin.examination.regular.results', [
        'session'    => $session,
        'sessions'   => $sessions,
        'programs'   => $programs,
         'institutes' => $institutes,
        'programId'  => null,   // default empty
        'semester'   => null,   // default empty
        'program'    => null,
        'students'   => null,
        'courses'    => null,
        'index'      => null,
    ]);
    }






public function marksMatrix($programId, $semester)
{
    $students = Student::where('program_id', $programId)
        ->where('status', 1) // Only active students
        ->orderBy('name')
        ->get();

    $program = Program::findOrFail($programId);

$courses = $program->courses()
    ->wherePivot('semester', $semester)
    ->orderBy('course_code')  // or 'code' if your column is named that
    ->get();

    // Eager load marks for these students
    $studentIds = $students->pluck('id');
    $marks = Mark::whereIn('student_id', $studentIds)
        ->where('semester', $semester)
        ->with('course')
        ->get()
        ->groupBy('student_id');

    $matrix = [];

    foreach ($students as $student) {
        $studentMarks = $marks[$student->id] ?? collect();

        $courseMarks = [];

        foreach ($courses as $course) {
            $mark = $studentMarks->firstWhere('course_id', $course->id);

            $courseMarks[] = [
                'course_id'   => $course->id,
                'code'        => $course->code,
                'title'       => $course->title,
                'internal'    => $mark->internal ?? null,
                'external'    => $mark->external ?? null,
                'attendance'  => $mark->attendance ?? null,
                'total'       => $mark->total ?? null,
            ];
        }

        $matrix[] = [
            'student' => $student,
            'marks'   => $courseMarks,
        ];
    }

    return [
        'students' => $students,
        'courses'  => $courses,
        'matrix'   => $matrix,
    ];
}


public function compileFinalResultsRegular(Request $request, $sessionId)
{
    Log::info("▶️ Entered compileFinalResultsRegular", [
        'request'   => $request->all(),
        'sessionId' => $sessionId
    ]);

    $validated = $request->validate([
        'program_id' => 'required|exists:programs,id',
        'semester'   => 'required|integer|min:1|max:10',
        'action'     => 'required|in:show,compile',
    ]);

    [$programId, $semester] = [$validated['program_id'], $validated['semester']];
    Log::info("✅ Validation passed", compact('programId', 'semester'));

    if ($validated['action'] === 'show') {
        Log::info("👁 Showing results page, not compiling.");
        $viewData = [
            'session'   => AcademicSession::findOrFail($sessionId),
            'sessions'  => AcademicSession::where('type', 'regular')->orderByDesc('year')->get(['id', 'year']),
            'programs'  => Program::all(),
            'program'   => Program::findOrFail($programId),
            'programId' => $programId,
            'semester'  => $semester,
        ] + $this->marksMatrix($programId, $semester);

        return view('admin.examination.regular.results', $viewData);
    }

    $studentIds = Student::where('program_id', $programId)
        ->where('semester', $semester)
        ->pluck('id');

    Log::info("👨‍🎓 Found students", ['count' => $studentIds->count(), 'ids' => $studentIds]);

    if ($studentIds->isEmpty()) {
        return back()->withErrors(['compile_error' => '❌ No students found for this program and semester.']);
    }

    $marks = Mark::whereIn('student_id', $studentIds)
        ->where('semester', $semester)
        ->where('session_id', $sessionId)
        ->get(['student_id', 'course_id', 'internal', 'external', 'attendance']);

    Log::info("📝 Found marks", ['count' => $marks->count()]);

    $markIndex = [];
    foreach ($marks as $m) {
        $markIndex[$m->student_id][$m->course_id] = $m;
    }

    $courses = Program::findOrFail($programId)
        ->courses()
        ->wherePivot('semester', $semester)
        ->with('component') // must have courseComponent or component relationship
        ->get(['courses.id', 'courses.credit_value', 'courses.has_external']);

    Log::info("📚 Courses found", ['count' => $courses->count(), 'ids' => $courses->pluck('id')]);

    if ($courses->isEmpty()) {
        return back()->withErrors(['compile_error' => '❌ No courses assigned to this semester for the program.']);
    }

    $defaultedMarks = [];

    foreach ($studentIds as $sid) {
        foreach ($courses as $course) {
            $m = $markIndex[$sid][$course->id] ?? null;

            if (!$m) {
                $m = new \stdClass();
                $m->student_id = $sid;
                $m->course_id = $course->id;
                $m->internal = 0;
                $m->external = 0;
                $m->attendance = 0;
                $markIndex[$sid][$course->id] = $m;
                $defaultedMarks[] = "Student {$sid} - Course {$course->id} created with default 0s";
            } else {
                foreach (['internal', 'external', 'attendance'] as $key) {
                    if (is_null($m->$key)) {
                        $m->$key = 0;
                        $defaultedMarks[] = "Student {$sid} - Course {$course->id}: {$key} set to 0";
                    }
                }
            }
        }
    }

    if (!empty($defaultedMarks)) {
        Log::warning("⚠️ Defaulted missing/null marks", $defaultedMarks);
        session()->flash('warning', '⚠️ Some missing marks were treated as 0.');
    }

    $errors = [];
    Log::info("🚀 Beginning DB transaction...");

    try {
        DB::transaction(function () use ($studentIds, $courses, $markIndex, $programId, $semester, $sessionId, &$errors) {
            foreach ($studentIds as $sid) {
                $courseGrades = [];

                foreach ($courses as $course) {
                    try {
                        $m = $markIndex[$sid][$course->id] ?? null;
                        if (!$m) continue;

                        $component = $course->component;
                        $attendance = (!is_null($component?->attendance_max)) ? (int) $m->attendance : 0;
                        $externalPassing = $component->external_min ?? 30;

                        $cg = new CourseGrade(
                            (float) $course->credit_value,
                            (int) $m->internal,
                            (int) $m->external,
                            $attendance,
                            $externalPassing
                        );

                        $courseGrades[] = $cg;

                        $externalMax = $component?->external_max;
                        $percentage = (is_numeric($m->external) && is_numeric($externalMax) && $externalMax > 0)
                            ? round(($m->external / $externalMax) * 100, 2)
                            : null;

                        $existing = ExternalResult::where([
                            'student_id' => $sid,
                            'program_id' => $programId,
                            'course_id'  => $course->id,
                            'semester'   => $semester,
                        ])->first();

                        ExternalResult::updateOrCreate(
                            [
                                'student_id' => $sid,
                                'program_id' => $programId,
                                'course_id'  => $course->id,
                                'semester'   => $semester,
                            ],
                            [
                                 'academic_session_id' => $sessionId, 
                                'internal'        => $cg->internal,
                                'external'        => $cg->external,
                                'attendance'      => $cg->attendance,
                                'total'           => $cg->total,
                                'credit'          => $cg->credit,
                                'grade_point'     => $cg->gradePoint,
                                'grade_letter'    => $cg->gradeLetter,
                                'result_status'   => ($cg->external < $externalPassing) ? 'FAIL' : $cg->status,
                                'exam_attempt'    => $existing?->exam_attempt ?? 1,
                                'obtained_marks'  => $m->external,
                                'total_marks'     => $externalMax,
                                'percentage'      => $percentage,
                            ]
                        );
                    } catch (\Exception $e) {
                        $errors[] = "❌ Error for Student ID {$sid}, Course ID {$course->id}: " . $e->getMessage();
                        Log::error("❌ ExternalResult Error [Student: $sid, Course: {$course->id}]: " . $e->getMessage());
                        continue;
                    }
                }

                if (empty($courseGrades)) {
                    Log::warning("⚠️ No course grades generated for Student ID {$sid}");
                    continue;
                }

                try {
                    $sgpa = SemesterGpa::sgpa($courseGrades);
                    $semCredits = array_sum(array_column($courseGrades, 'credit'));

                    $previousSgpas = ResultsSummary::where('student_id', $sid)
                        ->where('semester', '<', $semester)
                        ->pluck('sgpa')->filter()->toArray();

                    $cgpa = SemesterGpa::cgpa([...$previousSgpas, $sgpa]);

                    $prevSummary = ResultsSummary::where('student_id', $sid)
                        ->where('semester', '<', $semester)
                        ->orderByDesc('semester')
                        ->first();

                    ResultsSummary::updateOrCreate(
                        ['student_id' => $sid, 'semester' => $semester],
                        [
                            'program_id'         => $programId,
                            'sgpa'               => $sgpa,
                            'cgpa'               => $cgpa,
                            'cumulative_credits' => ($prevSummary->cumulative_credits ?? 0) + $semCredits,
                        ]
                    );

                    ExternalResult::where('student_id', $sid)
                        ->where('semester', $semester)
                        ->each(function ($result) use ($sgpa, $cgpa) {
                            $result->grade_letter ??= ExternalResult::calculateGradeLetter($result->total);
                            $result->sgpa = $sgpa;
                            $result->cgpa = $cgpa;
                            $result->save();
                        });

                    Log::info("✅ Result compiled for student: $sid");
                } catch (\Exception $e) {
                    $errors[] = "❌ SGPA/CGPA update failed for Student ID {$sid}: " . $e->getMessage();
                    Log::error("❌ SGPA/CGPA Error [Student: $sid]: " . $e->getMessage());
                    continue;
                }
            }
        });

        if (!empty($errors)) {
            Log::warning("⚠️ Partial compile with errors", $errors);
            return back()->withErrors(['compile_errors' => implode("\n", $errors)]);
        }

        Log::info("🎉 Final results compiled successfully");
        return back()->with('success', '✅ Final results compiled and saved successfully.');

    } catch (\Exception $e) {
        Log::error('❌ Transaction failed: ' . $e->getMessage());
        return back()->withErrors(['db_error' => '❌ Something went wrong during result compilation.']);
    }
}



public function compileAggregatedResult(Request $request)
{
    $request->validate([
        'student_id' => 'required|exists:students,id',
        'program_id' => 'required|exists:programs,id',
        'semester'   => 'required|integer|min:1|max:10',
    ]);

    $studentId = $request->student_id;
    $programId = $request->program_id;
    $semester  = $request->semester;

    // Get the student's marks for the semester
    $marks = Mark::with('course.component') // assumes relationships exist
        ->where('student_id', $studentId)
        ->where('semester', $semester)
        ->get();

    $internalMarks = [];
    $externalMarks = [];
    $attendanceMarks = [];
    $totalMarks = [];

    $reappearInternal = [];
    $reappearExternal = [];

    $courseGrades = [];

    foreach ($marks as $mark) {
        $course = $mark->course;
        $component = $course->component;
        $code = $course->course_code;

        $internal = $mark->internal ?? 0;
        $external = $mark->external ?? 0;
        $attendance = $mark->attendance ?? 0;

        // Get component passing marks (fallback to default values)
        $internalMin = $component->internal_min ?? 20;
$externalMin = $component?->external_min ?? 30;
        if ($internal < $internalMin) $reappearInternal[] = $code;
        if ($external < $externalMin) $reappearExternal[] = $code;

        $internalMarks[$code] = $internal;
        $externalMarks[$code] = $external;
        $attendanceMarks[$code] = $attendance;

        // Total calculation based on course component (total_from)
        $total = 0;
        if ($component && $component->total_from) {
            foreach (explode('+', strtolower($component->total_from)) as $part) {
                $total += match (trim($part)) {
                    'internal'   => $internal,
                    'external'   => $external,
                    'attendance' => $attendance,
                    default      => 0,
                };
            }
        } else {
            $total = $internal + $external + $attendance;
        }

        $totalMarks[$code] = $total;

        // Credit from course (default 4)
        $credit = $course->credit_value ?? 4;

        $courseGrade = new \App\Services\Grading\CourseGrade($credit, $internal, $external, $attendance);
        $courseGrades[] = $courseGrade;
    }

    // SGPA from course grades
    $sgpa = \App\Services\Grading\SemesterGpa::sgpa($courseGrades);

    // CGPA from past AggregatedResults
    $previousSgpas = AggregatedResult::where('student_id', $studentId)
        ->where('semester', '<', $semester)
        ->pluck('sgpa')
        ->filter()
        ->toArray();

    $cgpa = \App\Services\Grading\SemesterGpa::cgpa([...$previousSgpas, $sgpa]);

    AggregatedResult::updateOrCreate(
        [
            'student_id' => $studentId,
            'program_id' => $programId,
            'semester'   => $semester
        ],
        [
            'internal_marks'          => collect($internalMarks)->map(fn($v, $k) => "$k:$v")->implode(','),
            'internal_reappear'       => implode(',', $reappearInternal),
            'external_marks'          => collect($externalMarks)->map(fn($v, $k) => "$k:$v")->implode(','),
            'external_reappear'       => implode(',', $reappearExternal),
            'attendance_marks'        => collect($attendanceMarks)->map(fn($v, $k) => "$k:$v")->implode(','),
            'total_marks'             => collect($totalMarks)->map(fn($v, $k) => "$k:$v")->implode(','),
            'total_reappear_subjects' => count($reappearInternal) + count($reappearExternal),
           'reappear_subjects' => implode(',', array_merge($reappearInternal, $reappearExternal)),
            'remarks'                 => null,
            'sgpa'                    => round($sgpa, 2),
            'cgpa'                    => round($cgpa, 2),
            'cumulative_credits'      => array_sum(array_column($courseGrades, 'credit')),
            'exam_attempt'            => 1,
            'compiled_at'             => now(),
        ]
    );

    return back()->with('success', 'Aggregated result compiled successfully.');
}


public function aggregateAll(Request $request)
{
    $request->validate([
        'program_id' => 'required|exists:programs,id',
        'semester'   => 'required|integer|min:1|max:10',
    ]);

    $programId = $request->program_id;
    $semester  = $request->semester;

    $students = Student::where('program_id', $programId)
                ->where('semester', $semester)
                ->get(['id']);

    foreach ($students as $student) {
        // Manually dispatch the aggregation for each student
        $this->compileAggregatedResult(new Request([
            'student_id' => $student->id,
            'program_id' => $programId,
            'semester'   => $semester,
        ]));
    }

    return back()->with('success', 'Aggregated results compiled for all students in the program.');
}

///////////////////////
// final result download in the calculated result page
//////////////////////
public function downloadExcel(Request $request)
{
    $request->validate([
        'academic_session_id' => 'required|exists:academic_sessions,id',
        'program_id' => 'required|exists:programs,id',
        'semester'   => 'required|integer|min:1|max:10',
    ]);

    $sessionId = $request->input('academic_session_id'); // ✅ Add this
    $programId = $request->input('program_id');
    $semester  = $request->input('semester');

    return Excel::download(
        new ExternalResultsExport($sessionId, $programId, $semester), // ✅ Now 3 args
        "external_results_program_{$programId}_sem_{$semester}.xlsx"
    );
}


// public function showCalculatedResults(Request $request)
// {
//     // Optional filters from request — customize as needed
//     $filterType = $request->input('type');         // e.g., 'regular', 'diploma', etc.
//     $filterActive = $request->input('active');     // 1 or 0
//     $filterOddEven = $request->input('odd_even');  // 'odd' or 'even'

//     // Build base query for academic sessions with filters
//     $sessionQuery = AcademicSession::query();

//     if ($filterType) {
//         $sessionQuery->where('type', $filterType);
//     }

//     if (!is_null($filterActive)) {
//         $sessionQuery->where('active', $filterActive);
//     }

//     if ($filterOddEven) {
//         $sessionQuery->where('odd_even', $filterOddEven);
//     }

//     // Get all sessions with filters, ordered for dropdown
//     $sessions = $sessionQuery->orderByDesc('year')
//         ->orderBy('term')
//         ->get()
//         ->map(function ($session) {
//             $session->display = $session->year . ' - ' . $session->term
//                 . ($session->type ? ' (' . $session->type . ')' : '')
//                 . ($session->odd_even ? ' [' . ucfirst($session->odd_even) . ']' : '');
//             return $session;
//         });

//     // Get latest session from filtered sessions, or fallback if none
//     $currentSession = $sessions->first() ?? AcademicSession::orderByDesc('id')->first();

//     // Fetch ExternalResult groups by joining students (to get academic_session_id)
//     $groups = ExternalResult::select(
//             'external_results.program_id',
//             'external_results.semester',
//             'students.academic_session_id'
//         )
//         ->join('students', 'external_results.student_id', '=', 'students.id')
//         // Join academic_sessions to filter groups by academic sessions matching filters
//         ->join('academic_sessions', 'students.academic_session_id', '=', 'academic_sessions.id')
//         // Apply the same filters to academic_sessions here
//         ->when($filterType, fn($q) => $q->where('academic_sessions.type', $filterType))
//         ->when(!is_null($filterActive), fn($q) => $q->where('academic_sessions.active', $filterActive))
//         ->when($filterOddEven, fn($q) => $q->where('academic_sessions.odd_even', $filterOddEven))
//         ->with('program')
//         ->groupBy('external_results.program_id', 'external_results.semester', 'students.academic_session_id')
//         ->orderBy('students.academic_session_id', 'desc')
//         ->orderBy('external_results.program_id')
//         ->orderBy('external_results.semester')
//         ->get();

//     // Get academic sessions for groups (for display in blade)
//     $academicSessions = AcademicSession::whereIn('id', $groups->pluck('academic_session_id')->unique())
//         ->get()
//         ->keyBy('id');

//     return view('admin.examination.regular.calculated_results', [
//         'groups'           => $groups,
//         'academicSessions' => $academicSessions,
//         'academicYear'     => 'Filtered Sessions',
//         'sessions'         => $sessions,
//         'currentSession'   => $currentSession,
//         // Pass filters back to view to keep filter state (optional)
//         'filterType'       => $filterType,
//         'filterActive'     => $filterActive,
//         'filterOddEven'    => $filterOddEven,
//     ]);
// }


public function showCalculatedResults(Request $request)
{
    $filterType = $request->input('type');
    $filterActive = $request->input('active');
    $filterOddEven = $request->input('odd_even');

    // Get sessions for dropdown filter
    $sessionQuery = AcademicSession::query();

    if ($filterType) {
        $sessionQuery->where('type', $filterType);
    }

    if (!is_null($filterActive)) {
        $sessionQuery->where('active', $filterActive);
    }

    if ($filterOddEven) {
        $sessionQuery->where('odd_even', $filterOddEven);
    }

    $sessions = $sessionQuery->orderByDesc('year')
        ->orderBy('term')
        ->get()
        ->map(function ($session) {
            $session->display = $session->year . ' - ' . $session->term
                . ($session->type ? ' (' . $session->type . ')' : '')
                . ($session->odd_even ? ' [' . ucfirst($session->odd_even) . ']' : '');
            return $session;
        });

    $currentSession = $sessions->first() ?? AcademicSession::orderByDesc('id')->first();

    // Step 1: Raw group data
  $groups = ExternalResult::selectRaw('
        external_results.program_id,
        external_results.semester as current_semester,
        external_results.academic_session_id
    ')
    ->join('academic_sessions', 'external_results.academic_session_id', '=', 'academic_sessions.id')
    ->when($filterType, fn($q) => $q->where('academic_sessions.type', $filterType))
    ->when(!is_null($filterActive), fn($q) => $q->where('academic_sessions.active', $filterActive))
    ->when($filterOddEven, fn($q) => $q->where('academic_sessions.odd_even', $filterOddEven))
    ->groupBy('external_results.program_id', 'external_results.semester', 'external_results.academic_session_id')
    ->orderByDesc('external_results.academic_session_id')
    ->orderBy('external_results.program_id')
    ->get();



    // Step 2: Attach programs and academic sessions manually
    $programs = \App\Models\Program::whereIn('id', $groups->pluck('program_id'))->get()->keyBy('id');
$academicSessions = \App\Models\AcademicSession::whereIn('id', $groups->pluck('academic_session_id'))->get()->keyBy('id');

$groups->transform(function ($item) use ($programs, $academicSessions) {
    $item->program = $programs[$item->program_id] ?? null;
    $item->academicSession = $academicSessions[$item->academic_session_id] ?? null;
    return $item;
});


    return view('admin.examination.regular.calculated_results', [
        'groups'           => $groups,
        'academicSessions' => $academicSessions,
        'academicYear'     => 'Filtered Sessions',
        'sessions'         => $sessions,
        'currentSession'   => $currentSession,
        'filterType'       => $filterType,
        'filterActive'     => $filterActive,
        'filterOddEven'    => $filterOddEven,
    ]);
}


public function downloadExternalResults(int $academic_session_id, int $program_id, int $semester)
{
    $fileName = "external_results_P{$program_id}_S{$semester}.xlsx";

    return Excel::download(
        new ExternalResultsExport($academic_session_id, $program_id, $semester), // ✅ 3 arguments
        $fileName
    );
}



public function publish(Request $request)
{
    $request->validate([
        'program_id' => 'required|exists:programs,id',
        'semester'   => 'required|integer|min:1|max:10',
    ]);

    $programId = $request->input('program_id');
    $semester  = $request->input('semester');

    // Logic to publish the result
    // For example, set a `published` flag in a summary/result table

    return back()->with('success', 'Results published successfully.');
}


// -----------------------------------
// final result calculation
// -----------------------------------
public function calculateExternalResults(Request $request, AcademicSession $session)
{
    $data = $request->validate([
        'program_id' => 'required|exists:programs,id',
        'semester'   => 'required|integer|min:1',
    ]);

    DB::transaction(function () use ($data, $session) {
        $program = Program::with([
            'courses' => fn ($q) => $q->wherePivot('semester', $data['semester']),
            'courses.component'
        ])->findOrFail($data['program_id']);

        if ($program->courses->isEmpty()) {
            throw new \RuntimeException("No courses mapped to '{$program->name}' for semester {$data['semester']}.");
        }

        $students = Student::where('program_id', $program->id)
            ->where('academic_year', 'like', Str::before($session->year, ' ') . '%')
            ->where('semester', $data['semester'])
            ->get();

        if ($students->isEmpty()) {
            throw new \RuntimeException("No students found for '{$program->name}', Semester {$data['semester']}, Year '{$session->year}'.");
        }

        foreach ($students as $student) {
            $semesterCredits = 0;
            $weightedGrades  = 0;
            $hasFail = false;

            foreach ($program->courses as $course) {
                $component = $course->component;

                $marks = Mark::where('student_id', $student->id)
                    ->where('course_id',  $course->id)
                    ->first();

                if (!$marks) continue;

                $internal   = $course->has_internal   ? ($marks->internal   ?? 0) : 0;
                $external   = $course->has_external   ? ($marks->external   ?? 0) : 0;
                $attendance = $course->has_attendance ? ($marks->attendance ?? 0) : 0;

                $total = 0;
                if ($component && $component->total_from) {
                    foreach (explode('+', strtolower($component->total_from)) as $src) {
                        $total += match (trim($src)) {
                            'internal' => $internal,
                            'external' => $external,
                            'attendance' => $attendance,
                            default => 0,
                        };
                    }
                } else {
                    $total = $internal + $external + $attendance;
                }

                // ✅ NEW: Grading logic based on % total (out of 100)
                $gradePoint = match (true) {
                    $total >= 95 => 9,
                    $total >= 84.99 => 8,
                    $total >= 74.99 => 7,
                    $total >= 64.99 => 6,
                    $total >= 54.99 => 5,
                    $total >= 44.99 => 4,
                    $total >= 34.99 => 3,
                    $total >= 24.99 => 2,
                    $total >= 14.99 => 1,
                    default      => 0,
                };

                $gradeLetter = match ($gradePoint) {
                    9 => 'A+',
                    8 => 'A',
                    7 => 'A−',
                    6 => 'B+',
                    5 => 'B',
                    4 => 'B−',
                    3 => 'C+',
                    2 => 'C',
                    1 => 'C−',
                    default => 'F',
                };

                $status = $gradePoint === 0 ? 'FAIL' : 'PASS';
                $hasFail = $hasFail || ($gradePoint === 0);

                $attempt = (ExternalResult::where('student_id', $student->id)
                    ->where('course_id', $course->id)
                    ->max('exam_attempt') ?? 0) + 1;

              ExternalResult::updateOrCreate(
    [
        'student_id' => $student->id,
        'course_id'  => $course->id,
        'semester'   => $data['semester'],
    ],
    [
        'program_id'    => $program->id,
        'internal'      => $internal,
        'external'      => $external,
        'attendance'    => $attendance,
        'total'         => $total,
        'credit'        => (int) $course->credit_hours,
        'grade_point'   => $gradePoint,
        'grade_letter'  => $gradeLetter,
        'result_status' => $status,
        'exam_attempt'  => $attempt,
    ]
);


                $semesterCredits += (int) $course->credit_hours;
                $weightedGrades  += $course->credit_hours * $gradePoint;
            }

            $sgpa = $semesterCredits ? $weightedGrades / $semesterCredits : 0;

            // ✅ Apply Rule: If any F, SGPA Grade is "Nil"
            $sgpaLetter = $hasFail ? 'Nil' : SemesterGpa::letter($sgpa);

            $prev = ResultsSummary::where('student_id', $student->id)
                ->selectRaw('SUM(cumulative_credits) AS credits, SUM(sgpa * cumulative_credits) AS weighted')
                ->first();

            $cumCredits  = ($prev->credits  ?? 0) + $semesterCredits;
            $cumWeighted = ($prev->weighted ?? 0) + ($sgpa * $semesterCredits);
            $cgpa        = $cumCredits ? $cumWeighted / $cumCredits : 0;
            $cgpaLetter  = SemesterGpa::letter($cgpa);

            ResultsSummary::updateOrCreate(
                ['student_id' => $student->id, 'semester' => $data['semester']],
                [
                    'program_id'         => $program->id,
                    'sgpa'               => round($sgpa, 2),
                    'cumulative_credits' => $cumCredits,
                    'cgpa'               => round($cgpa, 2),
                    'sgpa_letter'        => $sgpaLetter,
                    'cgpa_letter'        => $cgpaLetter,
                ]
            );

            ExternalResult::where('student_id', $student->id)
                ->where('semester', $data['semester'])
                ->update([
                    'sgpa' => round($sgpa, 2),
                    'cgpa' => round($cgpa, 2),
                ]);
        }
    });

    return back()->with('success', 'External results processed and stored.');
}



public function downloadBulkResults(Request $request)
{
    $request->validate([
        'institute_id' => 'required|exists:institutes,id',
        'program_id'   => 'required|exists:programs,id',
        'format'       => 'required|in:html,excel',
        'academic_session_id' => 'required|exists:academic_sessions,id',
        'semester' => 'nullable|integer|min:1|max:10'
    ]);

    $selectedSemester = (int) $request->semester;

    $academicSession = AcademicSession::findOrFail($request->academic_session_id);

    $students = Student::with(['institute', 'program'])
        ->where('institute_id', $request->institute_id)
        ->where('program_id', $request->program_id)
        ->where('academic_session_id', $request->academic_session_id)
        ->orderBy('nchm_roll_number')
        ->get();

    if ($students->isEmpty()) {
        return back()->with('error', 'No students found for selected filters.');
    }

    if ($request->format === 'excel') {
        return Excel::download(new ExternalResultsExport($students), 'Results.xlsx');
    }

    foreach ($students as $student) {
        $results = \App\Models\ExternalResult::where('student_id', $student->id)
            ->with('course')
            ->where('semester', $selectedSemester)
            ->get();

        $summary = \App\Models\ResultsSummary::where('student_id', $student->id)
            ->where('semester', $selectedSemester)
            ->first();

        $student->results = $results;
        $student->setRelation('results', $results);
        $student->total_credits      = $results->sum('credit');
        $student->total_points       = $results->sum(fn ($r) => $r->credit * $r->grade_point);
        $student->sgpa               = $results->isNotEmpty() && $student->total_credits > 0
                                        ? $student->total_points / $student->total_credits
                                        : 0;
        $student->cumulative_credits = $summary->cumulative_credits ?? $student->total_credits;
        $student->cumulative_points  = $student->cumulative_credits * $student->sgpa;
        $student->cgpa               = $summary->cgpa ?? $student->sgpa;
    }

    return view('admin.examination.regular.html.bulk', compact('students', 'selectedSemester', 'academicSession'));
}


public function downloadResultByRoll(Request $request)
{
    $request->validate([
        'nchm_roll_number'     => 'required',
        'program_id'           => 'required|exists:programs,id',
        'semester'             => 'required|integer|min:1|max:10',
        'academic_session_id'  => 'required|exists:academic_sessions,id',
    ]);

    $academicSession = AcademicSession::findOrFail($request->academic_session_id);

    $student = Student::where('nchm_roll_number', $request->nchm_roll_number)
        ->where('program_id', $request->program_id)
        ->where('academic_session_id', $request->academic_session_id)
        ->first();

    if (!$student) {
        return back()->with('error', 'Student not found.');
    }

    $selectedSemester = (int) $request->semester;

    $results = \App\Models\ExternalResult::where('student_id', $student->id)
        ->with('course')
        ->where('semester', $selectedSemester)
        ->get();

    $summary = \App\Models\ResultsSummary::where('student_id', $student->id)
        ->where('semester', $selectedSemester)
        ->first();

    $student->results = $results;
    $student->setRelation('results', $results);

    $student->total_credits      = $results->sum('credit');
    $student->total_points       = $results->sum(fn ($r) => $r->credit * $r->grade_point);
    $student->sgpa               = $results->isNotEmpty() && $student->total_credits > 0
                                    ? $student->total_points / $student->total_credits
                                    : 0;
    $student->cumulative_credits = $summary->cumulative_credits ?? $student->total_credits;
    $student->cumulative_points  = $student->cumulative_credits * $student->sgpa;
    $student->cgpa               = $summary->cgpa ?? $student->sgpa;

    return view('admin.examination.regular.html.single', compact('student', 'results', 'selectedSemester', 'academicSession'));
}


//////////////////////////


///////////////////////////////




// public function downloadExcelRegular(Request $request)
// {
//     $programId = $request->query('program_id');
//     $semester = $request->query('semester');
//     $sessionId = $request->query('academic_session_id');

//     $program = Program::findOrFail($programId);

//     $students = Student::where('program_id', $programId)
//                        ->where('academic_session_id', $sessionId)
//                           ->where('semester', $semester) 
//                        ->get();

//     // If `courses` table doesn't have `program_id`, just filter by semester
//  $courses = Course::whereHas('programs', function ($query) use ($programId, $semester) {
//     $query->where('program_id', $programId)
//           ->where('semester', $semester);
// })->get();


//     $marks = Mark::whereIn('student_id', $students->pluck('id'))
//                  ->whereIn('course_id', $courses->pluck('id'))
//                  ->get();

//     $index = [];
//     foreach ($marks as $mark) {
//         $index[$mark->student_id][$mark->course_id] = $mark;
//     }

//     $export = new MatrixMarksExport($students, $courses, $program, $semester, $index);
//     return \Maatwebsite\Excel\Facades\Excel::download($export, 'Regular-Marks-Matrix.xlsx');
// }



// public function downloadExcelRegular(Request $request)
// {
//     // Validate request input (from query string)
//     $request->validate([
//         'program_id' => 'required|exists:programs,id',
//         'semester' => 'required|integer|min:1',
//         'academic_session_id' => 'required|exists:academic_sessions,id',
//     ]);

//     // Always use ->input() when using GET routes with query parameters
//     $programId = $request->input('program_id');
//     $semester = $request->input('semester');
//     $sessionId = $request->input('academic_session_id');

//     // Confirm values received (optional debug)
//     // dd(compact('programId', 'semester', 'sessionId'));

//     $program = Program::findOrFail($programId);
//     $session = AcademicSession::findOrFail($sessionId);

//     // ✅ Ensure students are fetched **specific to the academic session and semester**
//     $students = Student::where('program_id', $programId)
//         ->where('semester', $semester)
//         ->where('academic_session_id', $sessionId)
//         ->get();

//     // ✅ Ensure courses belong to the program and semester
//     $courses = Course::where('semester', $semester)
//         ->whereHas('programs', function ($q) use ($programId) {
//             $q->where('program_id', $programId);
//         })
//         ->get();

//     // ✅ Only get marks for these students and courses
//     $marks = Mark::whereIn('student_id', $students->pluck('id'))
//         ->whereIn('course_id', $courses->pluck('id'))
//         ->get();

//     // ✅ Index marks by student and course
//     $index = [];
//     foreach ($marks as $mark) {
//         $index[$mark->student_id][$mark->course_id] = $mark;
//     }

//     // ✅ Generate Excel export using dynamic file name
//     $export = new MatrixMarksExport($students, $courses, $program, $semester, $index, $session);

//     $filename = "Regular-Marks-Matrix-{$program->code}-S{$semester}-{$session->year}.xlsx";

//     return Excel::download($export, $filename);
// }



public function downloadExcelRegular(Request $request)
{
    $sessionId = $request->input('academic_session_id');
    $programId = $request->input('program_id');
    $semester = $request->input('semester');

    if (!$sessionId || !$programId || !$semester) {
        return redirect()->back()->with('error', 'All filters (session, program, semester) are required to download.');
    }

  return Excel::download(
    new ExternalResultsExport($sessionId, $programId, $semester),  // ✅ 3 arguments
    'ExternalResults.xlsx'
);
}






}