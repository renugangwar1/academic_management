<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Promotion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class StudentPromotionController extends Controller
{
    public function promote(Request $request)
    {
        try {
            $validated = $request->validate([
                'from_session_id' => 'required|exists:academic_sessions,id',
                'to_session_id'   => 'required|exists:academic_sessions,id|different:from_session_id',
                'program_id'      => 'required|exists:programs,id',
                'semester'        => 'required|integer|min:1|max:9',
                'promotion_type'  => 'required|in:all,passed,failed,manual',
            ]);

            // Explicit type casting
            $validated['from_session_id'] = (int) $validated['from_session_id'];
            $validated['to_session_id']   = (int) $validated['to_session_id'];
            $validated['program_id']      = (int) $validated['program_id'];
            $validated['semester']        = (int) $validated['semester'];

            Log::info('📥 Promotion request received', $validated);

            // Step 1: Build base query
            $baseQuery = Student::query()
                ->where('program_id', $validated['program_id'])
                ->where('academic_session_id', $validated['from_session_id'])
                ->where('semester', $validated['semester']);

            Log::debug('🔍 Base student query SQL', [
                'query'    => $baseQuery->toSql(),
                'bindings' => $baseQuery->getBindings(),
                'context'  => $validated,
            ]);

            $baseCount = $baseQuery->count();
            Log::info("📊 Base student count: $baseCount");

           if ($baseCount === 0) {
    // Diagnostic checks
    $programExists = DB::table('students')->where('program_id', $validated['program_id'])->exists();
    $sessionExists = DB::table('students')->where('academic_session_id', $validated['from_session_id'])->exists();
    $semesterExists = DB::table('students')->where('semester', $validated['semester'])->exists();

    $details = [];

    if (!$programExists) {
        $details[] = "No students found in selected Program (ID: {$validated['program_id']}).";
    }

    if (!$sessionExists) {
        $details[] = "No students found in selected Academic Session (ID: {$validated['from_session_id']}).";
    }

    if (!$semesterExists) {
        $details[] = "No students found in Semester {$validated['semester']}.";
    }

    if (empty($details)) {
        $details[] = "No students found matching all three: Program, Session, and Semester.";
    }

    $errorMsg = '❌ No students found for the selected criteria:<br>' . implode('<br>', $details);

    Log::warning('⚠️ Detailed base student check failed', $details);

    return back()->with('error', $errorMsg);
}


            // Step 2: Apply promotion type filters
            $students = match ($validated['promotion_type']) {
                'passed' => $baseQuery->passed($validated['semester'])->get(),
                'failed' => $baseQuery->failed($validated['semester'])->get(),
                default  => $baseQuery->get(), // all + manual
            };

            Log::info("🎯 Students found after filtering ({$validated['promotion_type']}): " . $students->count());
            Log::debug("👥 Filtered student IDs", ['ids' => $students->pluck('id')->toArray()]);

            if ($students->isEmpty()) {
                $message = match ($validated['promotion_type']) {
                    'passed' => "⚠️ Students exist but none have passed in Semester {$validated['semester']}.",
                    'failed' => "⚠️ Students exist but none are marked as failed in Semester {$validated['semester']}.",
                    'manual' => "⚠️ No students found for manual selection.",
                    default  => "⚠️ No students matched the promotion criteria.",
                };
                Log::notice('⚠️ Promotion aborted: ' . $message);
                return back()->with('warning', $message);
            }

            // Step 3: Handle manual view
            if ($validated['promotion_type'] === 'manual') {
                return view('admin.promotions.manual', [
                    'students'        => $students,
                    'to_session_id'   => $validated['to_session_id'],
                    'from_session_id' => $validated['from_session_id'],
                    'semester'        => $validated['semester'],
                    'program_id'      => $validated['program_id'],
                ]);
            }

            // Step 4: Promote
            return $this->promoteStudents($students, $validated['to_session_id']);
        } catch (Exception $e) {
            Log::error('❌ Exception during promotion', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'An unexpected error occurred. Please try again or contact support.');
        }
    }

    public function promoteManual(Request $request)
    {
        try {
            $validated = $request->validate([
                'student_ids'   => 'required|array',
                'to_session_id' => 'required|exists:academic_sessions,id',
            ]);

            Log::info('📥 Manual promotion triggered', $validated);

            $students = Student::whereIn('id', $validated['student_ids'])->get();

            if ($students->isEmpty()) {
                Log::warning('⚠️ No students found for manual promotion', ['ids' => $validated['student_ids']]);
                return back()->with('warning', 'No valid students selected for promotion.');
            }

            return $this->promoteStudents($students, $validated['to_session_id'], route('admin.exams.results.index'));
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

            $fromSemester = $student->semester;
            $toSemester = $validated['to_semester'];

            if ($fromSemester == $toSemester) {
                return back()->with('warning', "⚠️ The student is already in Semester $toSemester.");
            }

            DB::beginTransaction();

            $student->semester = $toSemester;
            $student->save();

            // Avoid duplicate promotion entry
            if (!Promotion::where([
                'student_id'    => $student->id,
                'from_semester' => $fromSemester,
                'to_semester'   => $toSemester,
            ])->exists()) {
                Promotion::create([
                    'student_id'    => $student->id,
                    'from_semester' => $fromSemester,
                    'to_semester'   => $toSemester,
                    'promoted_by'   => auth()->id(),
                    'promoted_at'   => now(),
                ]);
            }

            DB::commit();

            Log::info("✅ Student {$student->id} promoted from $fromSemester to $toSemester");

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

    private function promoteStudents($students, $toSessionId, $redirectRoute = null)
    {
        DB::beginTransaction();
        try {
            $promotedCount = 0;

            foreach ($students as $student) {
                if ($student->semester >= 10) {
                    Log::notice("🚫 Skipping student {$student->id} - already in final semester");
                    continue;
                }

                $fromSemester = $student->semester;
                $toSemester = $fromSemester + 1;

                $student->semester = $toSemester;
                $student->academic_session_id = $toSessionId;
                $student->save();

                // Avoid duplicate promotion
                if (!Promotion::where([
                    'student_id'    => $student->id,
                    'from_semester' => $fromSemester,
                    'to_semester'   => $toSemester,
                ])->exists()) {
                    Promotion::create([
                        'student_id'    => $student->id,
                        'from_semester' => $fromSemester,
                        'to_semester'   => $toSemester,
                        'promoted_by'   => auth()->id(),
                        'promoted_at'   => now(),
                    ]);
                }

                Log::info("📦 Promoted student {$student->id} from Semester $fromSemester to $toSemester");
                $promotedCount++;
            }

            DB::commit();

            $message = "$promotedCount student(s) promoted successfully.";
            Log::info('🎉 Promotion process completed', ['count' => $promotedCount]);

            return $redirectRoute
                ? redirect()->to($redirectRoute)->with('success', $message)
                : back()->with('success', $message);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('❌ Bulk promotion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Promotion failed: ' . $e->getMessage());
        }
    }
}
