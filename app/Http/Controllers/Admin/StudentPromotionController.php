<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Promotion;
use App\Models\AcademicSession;
use App\Models\StudentSessionHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class StudentPromotionController extends Controller
{
    public function promote(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('admin.promotions.selection', [
                'sessions' => AcademicSession::orderBy('id')->get(),
            ]);
        }

        try {
            $validated = $request->validate([
                'from_session_id' => 'required|exists:academic_sessions,id',
                'to_session_id'   => 'required|exists:academic_sessions,id|different:from_session_id',
                'program_id'      => 'required|exists:programs,id',
                'semester'        => 'required|integer|min:1|max:9',
                'promotion_type'  => 'required|in:all,passed,failed,manual',
            ]);

            $validated = array_map('intval', $validated);
            Log::info('📥 Promotion request received', $validated);

            $baseQuery = Student::query()
                ->where('program_id', $validated['program_id'])
                ->where('academic_session_id', $validated['from_session_id'])
                ->where('semester', $validated['semester']);

            if (!$baseQuery->exists()) {
                return back()->with('error', "❌ No students found matching the given criteria.");
            }

            if ($validated['promotion_type'] === 'manual') {
                return redirect()->route('promotions.manual', $validated);
            }

            // ✅ Apply filter
            $students = $this->filterStudentsForPromotion($baseQuery, $validated['semester'], $validated['program_id']);

            if ($students->isEmpty()) {
                return back()->with('warning', "⚠️ No students matched the promotion criteria.");
            }

            return $this->promoteStudents($students, $validated['to_session_id'], $validated['promotion_type']);
        } catch (Exception $e) {
            Log::error('❌ Exception during promotion', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function promoteManual(Request $request)
    {
        if ($request->isMethod('get')) {
            $validated = $request->validate([
                'from_session_id' => 'required|exists:academic_sessions,id',
                'to_session_id'   => 'required|exists:academic_sessions,id|different:from_session_id',
                'program_id'      => 'required|exists:programs,id',
                'semester'        => 'required|integer|min:1|max:9',
            ]);

            $students = Student::with([
                'institute',
                'externalResults' => fn($query) => $query
                    ->where('semester', $validated['semester'])
                    ->where('program_id', $validated['program_id']),
            ])
                ->where('program_id', $validated['program_id'])
                ->where('academic_session_id', $validated['from_session_id'])
                ->where('semester', $validated['semester'])
                ->orderBy('name')
                ->get();

            $currentSession = AcademicSession::find($validated['from_session_id']);
            $sessions = AcademicSession::where('id', '>', $currentSession->id)->orderBy('id')->get();

            return view('admin.promotions.manual', [
                'students'        => $students,
                'to_session_id'   => $validated['to_session_id'],
                'from_session_id' => $validated['from_session_id'],
                'semester'        => $validated['semester'],
                'program_id'      => $validated['program_id'],
                'sessions'        => $sessions,
                'currentSession'  => $currentSession,
            ]);
        }

        try {
            $validated = $request->validate([
                'student_ids'   => 'required|array',
                'to_session_id' => 'required|exists:academic_sessions,id',
            ]);

            $students = Student::whereIn('id', $validated['student_ids'])->get();

            // ✅ Apply eligibility check on manually selected students
            $eligible = $students->filter(fn($student) =>
                $this->isEligibleForPromotion($student)
            );

            if ($eligible->isEmpty()) {
                return back()->with('warning', 'No eligible students selected for promotion.');
            }

            return $this->promoteStudents($eligible, $validated['to_session_id'], 'manual');
        } catch (Exception $e) {
            Log::error('❌ Manual promotion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Manual promotion failed: ' . $e->getMessage());
        }
    }

    public function promoteSingle(Request $request, $studentId)
    {
        try {
            $student = Student::findOrFail($studentId);

            $validated = $request->validate([
                'to_semester' => 'required|integer|min:1|max:10',
            ]);

            if ($student->semester == $validated['to_semester']) {
                return back()->with('warning', "⚠️ The student is already in Semester {$validated['to_semester']}.");
            }

            // ✅ Check eligibility
            if (!$this->isEligibleForPromotion($student)) {
                return back()->with('error', 'Student does not meet promotion criteria (CGPA < 3 or not PASS).');
            }

            DB::beginTransaction();

            $fromSemester = $student->semester;

            $this->logStudentSession($student, 'manual', $fromSemester, $validated['to_semester'], $student->academic_session_id);

            $student->semester = $validated['to_semester'];
            $student->save();

            Promotion::firstOrCreate([
                'student_id'    => $student->id,
                'from_semester' => $fromSemester,
                'to_semester'   => $validated['to_semester'],
            ], [
                'promoted_by' => auth()->id(),
                'promoted_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Student promoted successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('❌ Single promotion failed', [
                'student_id' => $studentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Promotion failed: ' . $e->getMessage());
        }
    }

    private function promoteStudents($students, $toSessionId, string $promotionType = 'auto')
    {
        DB::beginTransaction();
        try {
            $promotedCount = 0;
            $finalSemester = 10;

            foreach ($students as $student) {
                if ($student->semester >= $finalSemester) {
                    continue;
                }

                $fromSemester = $student->semester;
                $newSemester = $fromSemester + 1;

                $this->logStudentSession($student, $promotionType, $fromSemester, $newSemester, $toSessionId);

                $student->semester = $newSemester;
                $student->original_academic_session_id ??= $student->academic_session_id;
                $student->academic_session_id = $toSessionId;
                $student->save();

                Promotion::firstOrCreate([
                    'student_id'    => $student->id,
                    'from_semester' => $fromSemester,
                    'to_semester'   => $newSemester,
                ], [
                    'promoted_by' => auth()->id(),
                    'promoted_at' => now(),
                ]);

                $promotedCount++;
            }

            DB::commit();
            return back()->with('success', "$promotedCount student(s) promoted successfully.");
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('❌ Bulk promotion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Promotion failed: ' . $e->getMessage());
        }
    }

    private function logStudentSession(
        Student $student,
        string $promotionType = 'auto',
        ?int $fromSemester = null,
        ?int $toSemester = null,
        ?int $newAcademicSessionId = null
    ): void {
        $newAcademicSessionId = $newAcademicSessionId ?? $student->academic_session_id;

        $exists = StudentSessionHistory::where([
            'student_id'          => $student->id,
            'academic_session_id' => $newAcademicSessionId,
            'from_semester'       => $fromSemester,
            'to_semester'         => $toSemester,
        ])->exists();

        if ($exists) {
            Log::info("🔁 Duplicate promotion skipped for student_id={$student->id}, semester={$toSemester}");
            return;
        }

        StudentSessionHistory::create([
            'student_id'           => $student->id,
            'academic_session_id'  => $newAcademicSessionId,
            'program_id'           => $student->program_id,
            'institute_id'         => $student->institute_id,
            'from_semester'        => $fromSemester,
            'to_semester'          => $toSemester,
            'semester'             => $toSemester,
            'promotion_type'       => $promotionType,
            'promoted_by'          => auth()->id(),
            'promoted_at'          => now(),
        ]);
    }

    private function filterStudentsForPromotion($query, $currentSemester, $programId)
    {
        return $query->whereHas('externalResults', function ($q) use ($currentSemester, $programId) {
            $q->where('semester', $currentSemester)
              ->where('program_id', $programId)
              ->where('cgpa', '>=', 3)
              ->where('result_status', 'PASS');
        })->get();
    }

    private function isEligibleForPromotion(Student $student): bool
    {
        return $student->externalResults()
            ->where('semester', $student->semester)
            ->where('program_id', $student->program_id)
            ->where('cgpa', '>=', 3)
            ->where('result_status', 'PASS')
            ->exists();
    }
}
