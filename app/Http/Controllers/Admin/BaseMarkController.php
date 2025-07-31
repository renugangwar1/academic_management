<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{
    AcademicSession,
    Course,
    InternalResult,
    Mark,
    Program,
    Student
};
use App\Exports\{MarksTemplateExport, UploadedMarksExport};
use App\Imports\MarksImport;
use Illuminate\Database\Eloquent\Builder;   // ✔ NEW
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

abstract class BaseMarkController extends Controller
{
    /** Child controller must return either “regular” or “diploma”. */
    abstract protected function programmeType(): string;

    /* ───────────────────────── TEMPLATE DOWNLOAD ─────────────────────── */
    public function downloadTemplate(Request $r)
    {
        $r->validate([
            'mark_type' => 'required|in:internal,external,attendance,all',
        ]);

        return Excel::download(
            new MarksTemplateExport(Student::all(), Course::all(), $r->mark_type),
            "{$this->programmeType()}-marks-template.xlsx"
        );
    }

    /* ─────────────────────── STEP‑1  UPLOAD & PREVIEW ─────────────────── */
    public function uploadMarksFile(Request $request)
{
    $validated = $request->validate([
        'academic_session_id' => 'required|integer',
        'program_id'          => 'required|integer',
        'semester'            => 'required|integer',
        'mark_type'           => 'required|string',
        'marks_file'          => 'required|file|mimes:xlsx,csv,xls',
    ]);

    $data = Excel::toArray([], $request->file('marks_file'));

    // Assume rows have: roll_number, name, subject1, subject2...
    $rows = $data[0];
    $columns = array_slice($rows[0], 2); // skip roll_number and name
    unset($rows[0]); // remove header

    $preview = [];
    foreach ($rows as $row) {
        $entry = [
            'nchm_roll_number' => $row[0],
            'name' => $row[1],
        ];
        foreach ($columns as $i => $subject) {
            $entry[$subject] = $row[$i + 2] ?? null;
        }
        $preview[] = $entry;
    }

    session([
        'programId' => $validated['program_id'],
        'semester'  => $validated['semester'],
    ]);

    return view('admin.examination.regular.upload-marks', [
        'previewData' => $preview,
        'columns'     => $columns,
        'markType'    => $validated['mark_type'],
        'academicSessions' => AcademicSession::all(),
        'programs'          => Program::all(),
        'selectedSessionId' => $validated['academic_session_id'],
    ]);
}

    /* ─────────────────────────── STEP‑2  SAVE ─────────────────────────── */
 public function finalizeMarks(Request $r, AcademicSession $session)
{
    $this->guardSession($session);

    $hasFile = $r->hasFile('marks_file');

    $rules = [
        'mark_type'  => 'required|in:internal,external,attendance,all',
        'program_id' => 'required|exists:programs,id',
    ];

    if ($hasFile) {
        $rules['marks_file'] = 'file|mimes:xls,xlsx,csv';
    } else {
        $rules += [
            'preview_data' => 'required|array',
            'columns'      => 'required|array',
            'semester'     => 'nullable|integer|min:1|max:10',
            'year'         => 'nullable|integer|min:1|max:6',
        ];
    }

    $r->validate($rules);

    /* ❶ Single‑step flow – import directly from file */
    if ($hasFile) {
        $program = Program::findOrFail($r->program_id);

        Excel::import(
            new MarksImport($r->mark_type, $session->id, $program->structure),
            $r->file('marks_file')
        );

        return back()->with('success', 'Marks saved successfully.');
    }

    /* ❷ Two‑step flow – coming from preview */
    foreach ($r->preview_data as $row) {
        $student = Student::where('nchm_roll_number', $row['nchm_roll_number'])->first();
        if (!$student) continue;

        foreach ($r->columns as $label) {
            $courseId = null;
            $suffix   = $r->mark_type; // fallback

            // Try full match with suffix (e.g. "Maths | 10 (Internal)")
            if (preg_match('/^.+?\|(\d+)\s+\(([^)]+)\)$/', $label, $m)) {
                [, $courseId, $suffix] = $m;
            }
            // Fallback: try just course ID (e.g., "Maths | 10")
            elseif (preg_match('/^.+?\|(\d+)$/', $label, $m)) {
                $courseId = $m[1];
            }

            if (!$courseId) continue;

            $course = Course::find((int) $courseId);
            if (!$course) continue;

            $enrolled = DB::table('course_student')
                          ->where('student_id', $student->id)
                          ->where('course_id',  $course->id)
                          ->exists();
            if (!$enrolled) continue;

            $field = match (strtolower($suffix)) {
                'internal'   => 'internal',
                'external'   => 'external',
                'attendance' => 'attendance',
                default      => null,
            };
            if (!$field) continue;

            $val = $row[$label] ?? null;
            if (!is_numeric($val)) continue;

            $term = $r->semester ?? $r->year ?? null;

            $mark = Mark::firstOrNew([
                'student_id' => $student->id,
                'course_id'  => $course->id,
                'session_id' => $session->id,
            ] + ($student->semester ? ['semester' => $term] : ['year' => $term]));

            $mark->$field = (int) $val;
            $mark->total  = ($mark->internal   ?? 0)
                          + ($mark->external   ?? 0)
                          + ($mark->attendance ?? 0);
            $mark->save();
        }
    }

    return back()->with('success', 'Marks saved successfully.');
}


    /* ───────────────────── DOWNLOAD UPLOADED MARKS ────────────────────── */
    public function downloadUploadedMarks(Request $r)
    {
        $r->validate([
            'session_id' => 'required|exists:academic_sessions,id',
            'program_id' => 'required|exists:programs,id',
            'mark_type'  => 'required|in:internal,external,attendance,all',
        ]);

        $program  = Program::findOrFail($r->program_id);
        $session  = AcademicSession::findOrFail($r->session_id);
        $isSem    = $program->structure === 'semester';

        /* validate & resolve term */
        $r->validate($isSem
            ? ['semester' => 'required|integer|min:1|max:10']
            : ['year'     => 'required|integer|min:1|max:6']);

        $term = $isSem ? $r->semester : $r->year;

        /* courses mapped to that term */
        $courses = $program->courses()
                           ->wherePivot($isSem ? 'semester' : 'year', $term)
                           ->get();

        if ($courses->isEmpty()) {
            return back()->withErrors(['no_data' => 'No courses found for the selected combination.']);
        }

        /* students that actually have marks for that term & session */
        $students = Student::with([
                'program',
                'marks' => function (Builder $q) use ($courses, $session, $term, $isSem) {
                    $q->whereIn('course_id', $courses->pluck('id'))
                      ->where('session_id',  $session->id)
                      ->when($isSem,
                             fn ($q) => $q->where('semester', $term),
                             fn ($q) => $q->where('year',     $term));
                }])
            ->where('program_id', $program->id)
            ->whereHas('marks', function (Builder $q) use ($courses, $session, $term, $isSem) {
                $q->whereIn('course_id', $courses->pluck('id'))
                  ->where('session_id',  $session->id)
                  ->when($isSem,
                         fn ($q) => $q->where('semester', $term),
                         fn ($q) => $q->where('year',     $term));
            })
            ->orderBy('nchm_roll_number')
            ->get();

        if ($students->isEmpty()) {
            return back()->withErrors(['no_data' => 'No marks found for the selected combination.']);
        }

        /* stream Excel */
        return Excel::download(
            new UploadedMarksExport($students, $courses, $r->mark_type, $program->structure),
            sprintf('marks_%s_P%s_T%s_%s.xlsx',
                $session->year, $program->id, $term, $r->mark_type)
        );
    }

    

public function compileInternalMarks(Request $r)
{
    /* 1. Validate input ------------------------------------------------ */
    $r->validate([
        'session_id' => 'required|exists:academic_sessions,id',
        'program_id' => 'required|exists:programs,id',
        'semester'   => 'required|integer|min:1|max:10',
    ]);

    /* 2. Resolve objects & guard session type ------------------------- */
    $session = AcademicSession::findOrFail($r->session_id);
    $this->guardSession($session);

    $program  = Program::findOrFail($r->program_id);

    /* 3. Courses offered in that semester (bring component->internal_min) */
   $courses = $program->courses()
                   ->wherePivot('semester', $r->semester)
                   ->with('component:id,course_id,internal_min')
                   ->get()
                   ->keyBy('id');

if ($courses->isEmpty()) {
    return back()->withErrors(['no_data' => 'No courses mapped to that semester.']);
}

// Check if any course is missing a component
$missingComponents = $courses->filter(fn($course) => is_null($course->component));

if ($missingComponents->isNotEmpty()) {
    $courseNames = $missingComponents->pluck('course_title')->implode(', ');
    return back()->withErrors([
        'component_missing' => "Component not defined for the following courses: {$courseNames}. Please create components before compiling."
    ]);
}

    /* 4. All students of that semester in this programme -------------- */
    $students = Student::where('program_id', $program->id)
                       ->where('semester',   $r->semester)
                       ->get();

    if ($students->isEmpty()) {
        return back()->withErrors(['no_data' => 'No students found for the selected programme & semester.']);
    }

    /* 5. Pull all marks ---------------------------------------------- */
    $marks = Mark::whereIn('student_id', $students->pluck('id'))
                 ->whereIn('course_id',  $courses->keys())
                 ->where('session_id',   $session->id)
                 ->where('semester',     $r->semester)
                 ->get()
                 ->keyBy(fn ($m) => $m->student_id.'.'.$m->course_id);

    /* 6. Upsert internal results -------------------------------------- */
    DB::transaction(function () use ($students, $courses, $marks, $program, $r) {

       foreach ($students as $stu) {
    foreach ($courses as $course) {
        $key   = $stu->id . '.' . $course->id;
        $score = $marks[$key]->internal ?? null;

        // Get internal_min safely
        $min = optional($course->component)->internal_min;

        // If internal_min is null (not defined), treat as 0 (or skip)
        $min = is_null($min) ? 0 : (float) $min;

        $status = match (true) {
            is_null($score)        => 'FAIL',       // No marks entered
            (float) $score < $min  => 'REAPPEAR',   // Below minimum
            default                => 'PASS',
        };

        InternalResult::updateOrCreate(
            [
                'student_id' => $stu->id,
                'program_id' => $program->id,
                'course_id'  => $course->id,
                'semester'   => $r->semester,
            ],
            [
                'internal_marks' => $score,
                'status'         => $status,
                'compiled_at'    => now(),
            ]
        );
    }
}

    });

    return back()->with('success', 'Internal marks compiled.');
}



    /* ─────────────────────────── HELPERS ──────────────────────────────── */
    private function guardSession(AcademicSession $session): void
    {
        abort_unless($session->type === $this->programmeType(), 404,
            'Wrong programme type for this route.');
    }
}