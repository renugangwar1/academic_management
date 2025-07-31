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



class ReappearController extends Controller
{
    public function index()
    {
        $institutes = Institute::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        $academicSessions = AcademicSession::orderBy('year', 'desc')->get();

        return view('admin.reappears.index', compact('institutes', 'programs', 'academicSessions'));
    }

 public function downloadReappear(Request $request)
{
    $validated = $request->validate([
        'institute_id' => 'required|exists:institutes,id',
        'program_id' => 'required|exists:programs,id',
        'academic_session_id' => 'required|exists:academic_sessions,id',
        'semester' => 'nullable|integer|min:1|max:10',
        'year' => 'nullable|integer|min:1|max:6',
    ]);

    $program = Program::findOrFail($validated['program_id']);
    $structure = $program->structure ?? 'semester';
    $level = $structure === 'yearly' ? $validated['year'] : $validated['semester'];

    if (!$level) {
        return back()->with('error', ucfirst($structure) . ' value is required.');
    }

    logger("🎯 Reappear download for structure: $structure | level: $level");

    // 🎯 Step 1: Get student IDs with REAPPEAR status for selected semester, program
    $reappearStudentIds = InternalResult::where('program_id', $validated['program_id'])
        ->where('semester', $level)
        ->whereRaw('LOWER(status) = ?', ['reappear'])
        ->pluck('student_id')
        ->unique();

    if ($reappearStudentIds->isEmpty()) {
        return back()->with('error', 'No students found with REAPPEAR status.');
    }

    // 🎯 Step 2: Load only those students
    $students = Student::with(['program', 'institute', 'internalResults.course'])
        ->whereIn('id', $reappearStudentIds)
        ->where('institute_id', $validated['institute_id'])
        ->where('program_id', $validated['program_id'])
        // optionally filter by academic_session_id if needed
        ->orderBy('nchm_roll_number')
        ->get();

    logger("✅ Students with reappear entries found: " . $students->count());

    // 🎯 Step 3: Filter students who actually have valid reappear courses (courses attached, etc.)
    $filtered = $this->filterReappearStudents($students, $level, $structure);

    logger("✅ Final reappear students after course check: " . $filtered->count());

    if ($filtered->isEmpty()) {
        return back()->with('error', 'No valid reappear admit cards found.');
    }

    return Pdf::loadView('pdf.reappear_admitcards', [
        'students' => $filtered
    ])->download('reappear_admitcards_' . now()->format('Ymd_His') . '.pdf');
}

public function downloadReappearSingle(Request $request)
{
    try {
        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'nchm_roll_number' => 'required|exists:students,nchm_roll_number',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'semester' => 'nullable|integer|min:1|max:10',
            'year' => 'nullable|integer|min:1|max:6',
        ]);

        $program = Program::find($validated['program_id']);
        if (!$program) {
            return back()->with('error', 'Program not found. Please check your input.');
        }

        $structure = $program->structure ?? 'semester';
        $level = $structure === 'yearly' ? $validated['year'] : $validated['semester'];

        if (!$level) {
            return back()->with('error', ucfirst($structure) . ' value is required.');
        }

        logger("🎯 Reappear admit card (single) for structure: $structure | level: $level");

        $student = Student::with(['program', 'institute', 'internalResults.course'])
            ->where('nchm_roll_number', $validated['nchm_roll_number'])
            ->where('program_id', $validated['program_id'])
            // 🛠 Removed strict academic_session_id filtering
            ->first();

        if (!$student) {
            logger('❌ No student found with:', [
                'nchm_roll_number' => $validated['nchm_roll_number'],
                'program_id' => $validated['program_id'],
            ]);
            return back()->with('error', 'Student not found. Please check your input.');
        }

        $eligibleStudent = $this->filterReappearStudents(collect([$student]), $level, $structure)->first();

        if (!$eligibleStudent) {
            return back()->with('error', 'No valid reappear subjects found for this student.');
        }

        return Pdf::loadView('pdf.reappear_admitcard', [
            'student' => $eligibleStudent
        ])->download("reappear_admitcard_{$student->nchm_roll_number}.pdf");

    } catch (\Exception $e) {
        \Log::error('Unexpected error generating reappear admit card', ['error' => $e->getMessage()]);
        return back()->with('error', 'An unexpected error occurred while generating the admit card. Please try again.');
    }
}





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



}
