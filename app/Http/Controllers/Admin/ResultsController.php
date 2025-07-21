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

    return view('admin.examination.regular.results', [
        'session'    => $session,
        'sessions'   => $sessions,
        'programs'   => $programs,
        'programId'  => null,   // default empty
        'semester'   => null,   // default empty
        'program'    => null,
        'students'   => null,
        'courses'    => null,
        'index'      => null,
    ]);
    }





    /**
 * Return students, courses and a quick‑lookup index of marks
 */
public function marksMatrix(int $programId, int $semester)
{
    // 1. courses offered in this semester
    $courses = Program::findOrFail($programId)
        ->courses()
        ->wherePivot('semester', $semester)
        ->orderBy('course_code')
        ->get(['courses.id', 'courses.course_code']); // fixed

    // 2. students in that program / semester
    $students = Student::where('program_id', $programId)
                       ->where('semester', $semester)
                       ->orderBy('nchm_roll_number')
                       ->get(['id', 'nchm_roll_number', 'name']);

    // 3. all related marks in ONE query
    $marks = Mark::whereIn('student_id', $students->pluck('id'))
                 ->whereIn('course_id',  $courses->pluck('id'))
                 ->get(['student_id', 'course_id', 'internal', 'external']);

    // 4. index marks by [student][course]
    $index = [];
    foreach ($marks as $m) {
        $index[$m->student_id][$m->course_id] = $m;
    }

    return compact('students', 'courses', 'index');
}


public function compileFinalResultsRegular(Request $request, $sessionId)
{
    Log::info("▶️ Entered compileFinalResultsRegular", [
        'request' => $request->all(),
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

    // Fetch students
    $studentIds = Student::where('program_id', $programId)
                         ->where('semester', $semester)
                         ->pluck('id');

    Log::info("👨‍🎓 Found students", ['count' => $studentIds->count(), 'ids' => $studentIds]);

    if ($studentIds->isEmpty()) {
        return back()->withErrors(['compile_error' => '❌ No students found for this program and semester.']);
    }

    // Fetch marks
    $marks = Mark::whereIn('student_id', $studentIds)
                 ->where('semester', $semester)
                 ->get(['student_id', 'course_id', 'internal', 'external', 'attendance']);

    Log::info("📝 Found marks", ['count' => $marks->count()]);

    $markIndex = [];
    foreach ($marks as $m) {
        $markIndex[$m->student_id][$m->course_id] = $m;
    }

    // Fetch courses
    $courses = Program::findOrFail($programId)
                      ->courses()
                      ->wherePivot('semester', $semester)
                      ->get(['courses.id', 'courses.credit_value', 'courses.has_external']);

    Log::info("📚 Courses found", ['count' => $courses->count(), 'ids' => $courses->pluck('id')]);

    if ($courses->isEmpty()) {
        return back()->withErrors(['compile_error' => '❌ No courses assigned to this semester for the program.']);
    }

    // Check for missing marks
    $defaultedMarks = [];

foreach ($studentIds as $sid) {
    foreach ($courses as $course) {
        $m = $markIndex[$sid][$course->id] ?? null;

        if (!$m) {
            // Create a fake mark object if it's missing entirely
            $m = new \stdClass();
            $m->student_id = $sid;
            $m->course_id = $course->id;
            $m->internal = 0;
            $m->external = 0;
            $m->attendance = 0;
            $markIndex[$sid][$course->id] = $m;
            $defaultedMarks[] = "Student ID {$sid} - Course ID {$course->id} (created with 0s)";
        } else {
            if (is_null($m->internal)) {
                $m->internal = 0;
                $defaultedMarks[] = "Student ID {$sid} - Course ID {$course->id} (internal defaulted)";
            }
            if (is_null($m->external)) {
                $m->external = 0;
                $defaultedMarks[] = "Student ID {$sid} - Course ID {$course->id} (external defaulted)";
            }
            if (is_null($m->attendance)) {
                $m->attendance = 0;
                $defaultedMarks[] = "Student ID {$sid} - Course ID {$course->id} (attendance defaulted)";
            }
        }
    }
}

if (!empty($defaultedMarks)) {
    Log::warning("⚠️ Defaulted missing marks to zero", $defaultedMarks);
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

                        if (!$m || is_null($m->internal) || is_null($m->external)) {
                            continue;
                        }

                        $cg = new CourseGrade(
                            (float) $course->credit_value,
                            (int) $m->internal,
                            (int) $m->external,
                            (int) $m->attendance
                        );

                        $courseGrades[] = $cg;

                        // External calculation
                        $externalMarks = null;
                        $externalMax = null;
                        $externalPercentage = null;

                        if ($course->has_external) {
                            $markRecord = Mark::where([
                                'student_id' => $sid,
                                'course_id'  => $course->id,
                                'session_id' => $sessionId,
                                'semester'   => $semester,
                            ])->first();

                            $externalMarks = $markRecord?->external;
                            $externalMax   = optional($course->courseComponent)->external_max;

                            if (is_numeric($externalMarks) && is_numeric($externalMax) && $externalMax > 0) {
                                $externalPercentage = round(($externalMarks / $externalMax) * 100, 2);
                            }
                        }

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
                                'internal'        => $cg->internal,
                                'external'        => $cg->external,
                                'attendance'      => $cg->attendance,
                                'total'           => $cg->total,
                                'credit'          => $cg->credit,
                                'grade_point'     => $cg->gradePoint,
                                'grade_letter'    => $cg->gradeLetter,
                             'result_status'   => ($cg->external < ($course->courseComponent->external_min ?? 30)) ? 'FAIL' : $cg->status,
                                'exam_attempt'    => $existing?->exam_attempt ?? 1,
                                'obtained_marks'  => $externalMarks,
                                'total_marks'     => $externalMax,
                                'percentage'      => $externalPercentage,
                            ]
                        );

                    } catch (\Exception $e) {
                        $errors[] = "Error for Student ID {$sid}, Course ID {$course->id}: " . $e->getMessage();
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

                    $allSgpas = ResultsSummary::where('student_id', $sid)
                        ->where('semester', '<', $semester)
                        ->pluck('sgpa')->filter()->toArray();

                    $cgpa = SemesterGpa::cgpa([...$allSgpas, $sgpa]);

                    $prev = ResultsSummary::where('student_id', $sid)
                        ->where('semester', '<', $semester)
                        ->orderByDesc('semester')->first();

                    ResultsSummary::updateOrCreate(
                        ['student_id' => $sid, 'semester' => $semester],
                        [
                            'program_id'         => $programId,
                            'sgpa'               => $sgpa,
                            'cgpa'               => $cgpa,
                            'cumulative_credits' => ($prev->cumulative_credits ?? 0) + $semCredits,
                        ]
                    );

                    ExternalResult::where('student_id', $sid)
                        ->where('semester', $semester)
                        ->get()
                        ->each(function ($result) use ($sgpa, $cgpa) {
                            if (!$result->grade_letter) {
                                $result->grade_letter = ExternalResult::calculateGradeLetter($result->total);
                            }
                            $result->sgpa = $sgpa;
                            $result->cgpa = $cgpa;
                            $result->save();
                        });

                    Log::info("✅ Result compiled for student: $sid");

                } catch (\Exception $e) {
                    $errors[] = "SGPA/CGPA update failed for Student ID {$sid}: " . $e->getMessage();
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



public function downloadExcel(Request $request)
{
    $request->validate([
        'program_id' => 'required|exists:programs,id',
        'semester'   => 'required|integer|min:1|max:10',
    ]);

    $programId = $request->input('program_id');
    $semester  = $request->input('semester');

    return Excel::download(
        new ExternalResultsExport($programId, $semester),
        "external_results_program_{$programId}_sem_{$semester}.xlsx"
    );
}


public function showCalculatedResults(Request $request)
{
    // Step 1: Determine academic year to show (from request or latest)
    $yearToShow = $request->input('academic_year') 
        ?? Student::latest()
            ->with('academicSession:id,year')
            ->get()
            ->pluck('academicSession.year')
            ->first();

    if (!$yearToShow) {
        return back()->with('error', 'No academic year data found.');
    }

    // Step 2: Get current session by year — might return multiple (e.g., Jan & July)
    $currentSession = AcademicSession::where('year', $yearToShow)
        ->orderByDesc('id')
        ->first(); 

    // Step 3: Get ALL sessions for promotion dropdown
    $sessions = AcademicSession::orderByDesc('year')
        ->orderBy('term')
        ->get()
        ->map(function ($session) {
            $session->display = $session->year . ' - ' . $session->term 
                . ($session->type ? ' (' . $session->type . ')' : '');
            return $session;
        });

    // Step 4: Group by program & semester for selected academic year
    $groups = Student::select('program_id', 'semester', 'academic_session_id')
        ->whereHas('academicSession', function ($q) use ($yearToShow) {
            $q->where('year', $yearToShow);
        })
        ->groupBy('program_id', 'semester', 'academic_session_id')
        ->with('program:id,name')
        ->orderBy('program_id')
        ->orderBy('semester')
        ->get();

    return view('admin.examination.regular.calculated_results', [
        'groups'         => $groups,
        'academicYear'   => $yearToShow,
        'sessions'       => $sessions,
        'currentSession' => $currentSession,
    ]);
}




public function downloadExternalResults(int $program_id, int $semester)
{
    $fileName = "external_results_P{$program_id}_S{$semester}.xlsx";

    return Excel::download(
        new ExternalResultsExport($program_id, $semester),
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





}