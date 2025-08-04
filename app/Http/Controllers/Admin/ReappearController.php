<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Institute;
use App\Models\Program;
use App\Models\AcademicSession;
use App\Models\InternalResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use App\Models\ReappearGicMark;
use App\Models\Course;




class ReappearController extends Controller
{
    public function index()
    {
        $institutes = Institute::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        $academicSessions = AcademicSession::orderBy('year', 'desc')->get();

        return view('admin.reappears.index', compact('institutes', 'programs', 'academicSessions'));
    }

//  public function downloadReappear(Request $request)
// {
//     $validated = $request->validate([
//         'institute_id' => 'required|exists:institutes,id',
//         'program_id' => 'required|exists:programs,id',
//         'academic_session_id' => 'required|exists:academic_sessions,id',
//         'semester' => 'nullable|integer|min:1|max:10',
//         'year' => 'nullable|integer|min:1|max:6',
//     ]);

//     $program = Program::findOrFail($validated['program_id']);
//     $structure = $program->structure ?? 'semester';
//     $level = $structure === 'yearly' ? $validated['year'] : $validated['semester'];

//     if (!$level) {
//         return back()->with('error', ucfirst($structure) . ' value is required.');
//     }

//     logger("🎯 Reappear download for structure: $structure | level: $level");

//     // 🎯 Step 1: Get student IDs with REAPPEAR status for selected semester, program
//     $reappearStudentIds = InternalResult::where('program_id', $validated['program_id'])
//         ->where('semester', $level)
//         ->whereRaw('LOWER(status) = ?', ['reappear'])
//         ->pluck('student_id')
//         ->unique();

//     if ($reappearStudentIds->isEmpty()) {
//         return back()->with('error', 'No students found with REAPPEAR status.');
//     }

//     // 🎯 Step 2: Load only those students
//     $students = Student::with(['program', 'institute', 'internalResults.course'])
//         ->whereIn('id', $reappearStudentIds)
//         ->where('institute_id', $validated['institute_id'])
//         ->where('program_id', $validated['program_id'])
//         // optionally filter by academic_session_id if needed
//         ->orderBy('nchm_roll_number')
//         ->get();

//     logger("✅ Students with reappear entries found: " . $students->count());

//     // 🎯 Step 3: Filter students who actually have valid reappear courses (courses attached, etc.)
//     $filtered = $this->filterReappearStudents($students, $level, $structure);

//     logger("✅ Final reappear students after course check: " . $filtered->count());

//     if ($filtered->isEmpty()) {
//         return back()->with('error', 'No valid reappear admit cards found.');
//     }

//     return Pdf::loadView('pdf.reappear_admitcards', [
//         'students' => $filtered
//     ])->download('reappear_admitcards_' . now()->format('Ymd_His') . '.pdf');
// }

// public function downloadReappearSingle(Request $request)
// {
//     try {
//         $validated = $request->validate([
//             'program_id' => 'required|exists:programs,id',
//             'nchm_roll_number' => 'required|exists:students,nchm_roll_number',
//             'academic_session_id' => 'required|exists:academic_sessions,id',
//             'semester' => 'nullable|integer|min:1|max:10',
//             'year' => 'nullable|integer|min:1|max:6',
//         ]);

//         $program = Program::find($validated['program_id']);
//         if (!$program) {
//             return back()->with('error', 'Program not found. Please check your input.');
//         }

//         $structure = $program->structure ?? 'semester';
//         $level = $structure === 'yearly' ? $validated['year'] : $validated['semester'];

//         if (!$level) {
//             return back()->with('error', ucfirst($structure) . ' value is required.');
//         }

//         logger("🎯 Reappear admit card (single) for structure: $structure | level: $level");

//         $student = Student::with(['program', 'institute', 'internalResults.course'])
//             ->where('nchm_roll_number', $validated['nchm_roll_number'])
//             ->where('program_id', $validated['program_id'])
//             // 🛠 Removed strict academic_session_id filtering
//             ->first();

//         if (!$student) {
//             logger('❌ No student found with:', [
//                 'nchm_roll_number' => $validated['nchm_roll_number'],
//                 'program_id' => $validated['program_id'],
//             ]);
//             return back()->with('error', 'Student not found. Please check your input.');
//         }

//         $eligibleStudent = $this->filterReappearStudents(collect([$student]), $level, $structure)->first();

//         if (!$eligibleStudent) {
//             return back()->with('error', 'No valid reappear subjects found for this student.');
//         }

//         return Pdf::loadView('pdf.reappear_admitcard', [
//             'student' => $eligibleStudent
//         ])->download("reappear_admitcard_{$student->nchm_roll_number}.pdf");

//     } catch (\Exception $e) {
//         \Log::error('Unexpected error generating reappear admit card', ['error' => $e->getMessage()]);
//         return back()->with('error', 'An unexpected error occurred while generating the admit card. Please try again.');
//     }
// }





    public function getAcademicSessionsByProgram($programId)
    {
        $sessions = DB::table('academic_session_program')
            ->join('academic_sessions', 'academic_sessions.id', '=', 'academic_session_program.academic_session_id')
            ->where('academic_session_program.program_id', $programId)
            ->select('academic_sessions.id', 'academic_sessions.year')
            ->distinct()
            ->orderBy('academic_sessions.year', 'desc')
            ->get();

        return response()->json($sessions);
    }

    /**
     * Filter students who have reappear courses based on internal results.
     */
private function filterReappearStudents($students, $level, $structure)
{
    return $students->filter(function ($student) use ($level, $structure) {
        $reappearCourses = $this->getReappearCourses($student, $level, $structure);

        if ($reappearCourses->isNotEmpty()) {
            logger("✅ Student {$student->nchm_roll_number} has " . $reappearCourses->count() . " reappear courses.");
            $student->setRelation('reappearCourses', $reappearCourses);
            return true;
        }

        logger("❌ Student {$student->nchm_roll_number} has no reappear courses.");
        return false;
    })->values();
}

    /**
     * Get reappear courses for a student using internal_results.
     */
private function getReappearCourses($student, $level, $structure)
{
    logger("Checking reappear courses for student: {$student->nchm_roll_number} | Level: $level | Structure: $structure");

    // Debug all internal results for visibility
    $allResults = InternalResult::where('student_id', $student->id)->get();
    logger("🧪 All Internal Results for student {$student->nchm_roll_number}: " . $allResults->count());
    foreach ($allResults as $result) {
        logger("🧪 course_id: {$result->course_id}, semester: {$result->semester}, status: {$result->status}");
    }

    $results = InternalResult::with('course')
        ->where('student_id', $student->id)
        ->where('program_id', $student->program_id)
        ->where(DB::raw('LOWER(status)'), 'reappear')
        ->when($level !== null, fn($query) => $query->where('semester', $level))
        ->get();

    logger("Found " . $results->count() . " reappear internal results for student: {$student->nchm_roll_number}");

    foreach ($results as $res) {
        logger("➡️ Result - course_id: {$res->course_id}, semester: {$res->semester}, status: {$res->status}, course name: " . ($res->course->name ?? 'N/A'));
    }

    return $results->filter(fn($result) => $result->course)->map(fn($result) => $result->course)->unique('id')->values();
}
public function fetchReappearStudents(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'institute_id'        => 'required|exists:institutes,id',
            'program_id'          => 'required|exists:programs,id',
            'semester'            => 'nullable|integer|min:1|max:10',
            'year'                => 'nullable|integer|min:1|max:6',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input'  => $request->all()
            ]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();
        $program = Program::find($validated['program_id']);
        if (!$program) {
            return response()->json(['error' => 'Program not found.'], 404);
        }

        $structure = $program->structure ?? 'semester';
        $level = $structure === 'yearly' ? ($validated['year'] ?? null) : ($validated['semester'] ?? null);
        if (!$level) {
            return response()->json(['error' => ucfirst($structure) . ' value is required.'], 422);
        }

        $semesterColumn = 'semester';
        Log::info("🎯 Fetching Reappear Students for {$structure} = {$level}");

        $studentFailMap = DB::table('results_summaries')
            ->where('program_id', $validated['program_id'])
            ->where('semester', $level)
            ->whereNotNull('failing_course_ids')
            ->pluck('failing_course_ids', 'student_id')
            ->map(function ($ids) {
                return collect(explode(',', $ids))->map(fn($id) => (int) trim($id))->all();
            })
            ->toArray();

        $internal = DB::table('internal_results')
            ->join('students', 'internal_results.student_id', '=', 'students.id')
            ->leftJoin('courses', 'internal_results.course_id', '=', 'courses.id')
            ->where('internal_results.program_id', $validated['program_id'])
            ->where('internal_results.' . $semesterColumn, $level)
            ->whereRaw('LOWER(TRIM(internal_results.status)) = ?', ['reappear'])
            ->where('students.institute_id', $validated['institute_id'])
            ->where('students.academic_session_id', $validated['academic_session_id'])
            ->select([
                'students.id as student_id',
                'students.name as student_name',
                'students.nchm_roll_number as roll_number',
                'internal_results.course_id',
                DB::raw('COALESCE(courses.course_code, "N/A") as course_code'),
                DB::raw('COALESCE(courses.course_title, "N/A") as course_name'),
                DB::raw("'Internal' as reappear_type"),
                'internal_results.' . $semesterColumn . ' as semester'
            ]);

        $external = DB::table('external_results')
            ->join('students', 'external_results.student_id', '=', 'students.id')
            ->leftJoin('courses', 'external_results.course_id', '=', 'courses.id')
            ->where('external_results.program_id', $validated['program_id'])
            ->where('external_results.' . $semesterColumn, $level)
            ->where('external_results.academic_session_id', $validated['academic_session_id'])
            ->where('students.institute_id', $validated['institute_id'])
            ->where(function ($query) {
                $query->whereRaw('LOWER(TRIM(result_status)) = ?', ['fail'])
                      ->orWhereRaw('LOWER(TRIM(result_status)) = ?', ['reappear']);
            })
            ->select([
                'students.id as student_id',
                'students.name as student_name',
                'students.nchm_roll_number as roll_number',
                'external_results.course_id',
                DB::raw('COALESCE(courses.course_code, "N/A") as course_code'),
                DB::raw('COALESCE(courses.course_title, "N/A") as course_name'),
                DB::raw("'External' as reappear_type"),
                'external_results.' . $semesterColumn . ' as semester'
            ]);

        $combinedResults = $internal->unionAll($external)->get();

        $reappearData = $combinedResults->filter(function ($item) use ($studentFailMap) {
            return isset($studentFailMap[$item->student_id]) &&
                   in_array((int) $item->course_id, $studentFailMap[$item->student_id]);
        })->sortBy('roll_number')->values();

        Log::info("✅ Total reappear records found: " . $reappearData->count());

        // ✅ Preload existing marks
        $existingMarks = DB::table('reappear_gic_marks')->get()
            ->groupBy(fn($row) => $row->student_id . '_' . $row->course_code);

        // ✅ Preload course internal_max values
        $courseMaxMap = DB::table('course_components')
            ->select('course_id', 'internal_max')
            ->get()
            ->pluck('internal_max', 'course_id');

        // ✅ Append already_stored and internal_max
        $reappearData = $reappearData->map(function ($item) use ($existingMarks, $courseMaxMap) {
            $key = $item->student_id . '_' . $item->course_code;
            $item->already_stored = isset($existingMarks[$key]);
            $item->internal_max = $courseMaxMap[$item->course_id] ?? 100;
            return $item;
        });

        if ($reappearData->isEmpty()) {
            return response()->json(['message' => 'No reappear students found.'], 200);
        }

        return response()->json($reappearData);

    } catch (\Throwable $e) {
        Log::error('❌ Error fetching reappear students', [
            'message' => $e->getMessage(),
            'trace'   => $e->getTraceAsString(),
        ]);
        return response()->json(['error' => 'Server error'], 500);
    }
}




public function storeGicMarks(Request $request)
{
    $students = $request->input('students', []);

    foreach ($students as $key => $data) {
        if (!isset($data['selected'])) continue;

        $parts = explode('_', $key);
        $studentId = $parts[0] ?? null;
        $courseCode = $parts[1] ?? null;

        if (empty($data['academic_session_id'])) {
            return back()->with('error', 'Missing academic session. Please refetch and try again.');
        }

        if (!isset($data['gic_marks']) || $data['gic_marks'] === '') {
            return back()->with('error', "Internal marks missing for one or more selected students.");
        }

        // ✅ Validate marks against internal_max
        $course = Course::with('component')->find($data['course_id']);
       $internalMax = optional($course->component)->internal_max ?? 100;


        if (!is_numeric($data['gic_marks']) || floatval($data['gic_marks']) > $internalMax) {
            return back()->withErrors([
                "Invalid marks for {$data['roll_number']} - max allowed: {$internalMax}",
            ]);
        }

        ReappearGicMark::create([
            'student_id'           => $studentId,
            'roll_number'          => $data['roll_number'] ?? 'N/A',
            'student_name'         => $data['student_name'] ?? 'N/A',
            'course_code'          => $courseCode,
            'course_name'          => $data['course_name'] ?? 'N/A',
            'reappear_type'        => $data['reappear_type'] ?? 'N/A',
            'gic_marks'            => $data['gic_marks'],
            'academic_session_id'  => $data['academic_session_id'],
            'program_id'           => $data['program_id'],
            'semester'             => $data['semester'],
            'year'                 => $data['year'],
            'institute_id'         => $data['institute_id'],
        ]);
    }

    return redirect()->back()->with('success', 'Marks saved successfully.');
}



public function downloadBulkReappearAdmitCards(Request $request)
{
    $request->validate([
        'academic_session_id' => 'required|exists:academic_sessions,id',
        'program_id'          => 'required|exists:programs,id',
        'institute_id'        => 'required|exists:institutes,id',
        'semester'            => 'nullable|integer',
        'year'                => 'nullable|integer',
    ]);

    $query = ReappearGicMark::where('academic_session_id', $request->academic_session_id)
        ->where('program_id', $request->program_id)
        ->where('institute_id', $request->institute_id);

    if ($request->filled('semester')) {
        $query->where('semester', $request->semester);
    }

    if ($request->filled('year')) {
        $query->where('year', $request->year);
    }

    // Load related data
    $reappearRecords = $query
        ->with(['institute', 'program'])
        ->orderBy('roll_number')
        ->get();

    if ($reappearRecords->isEmpty()) {
        return back()->with('error', 'No reappear records found for the selected filters.');
    }

    // Group by student_id so multiple subjects appear in a single admit card
    $groupedStudents = $reappearRecords->groupBy('student_id');

    $pdf = Pdf::loadView('pdf.reappear_bulk_pdf', [
        'students' => $groupedStudents,
    ])->setPaper('A4', 'portrait');

    return $pdf->download('reappear_bulk_report.pdf');
}


public function downloadReappearSingle(Request $request)
{
    $request->validate([
        'academic_session_id' => 'required|exists:academic_sessions,id',
        'nchm_roll_number'    => 'required|string',
        'program_id'          => 'required|exists:programs,id',
        'semester'            => 'nullable|integer|min:1|max:10',
        'year'                => 'nullable|integer|min:1|max:6',
    ]);

    // Fetch reappear records for single student
    $query = ReappearGicMark::where('academic_session_id', $request->academic_session_id)
        ->where('program_id', $request->program_id)
        ->where('roll_number', $request->nchm_roll_number);

    if ($request->filled('semester')) {
        $query->where('semester', $request->semester);
    }

    if ($request->filled('year')) {
        $query->where('year', $request->year);
    }

     // Load related data
    $reappearRecords = $query
        ->with(['institute', 'program'])
        ->orderBy('roll_number')
        ->get();

    if ($reappearRecords->isEmpty()) {
        return back()->with('error', 'No reappear records found for the given roll number.');
    }

    $groupedStudents = $reappearRecords->groupBy('student_id');

    $pdf = Pdf::loadView('pdf.reappear_bulk_pdf', [
        'students' => $groupedStudents,
    ])->setPaper('A4', 'portrait');

    return $pdf->stream("reappear_admitcard_{$request->nchm_roll_number}.pdf");
}


}