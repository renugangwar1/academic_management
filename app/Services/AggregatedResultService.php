<?php

namespace App\Services;

use App\Models\AggregatedResult;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class AggregatedResultService
{
    public function compileSemesterResult(Student $student, int $semester): void
    {
        // Collect data
        $internalMarks  = $this->getInternalMarks($student, $semester);
        $externalMarks  = $this->getExternalMarks($student, $semester);
        $attendance     = $this->getAttendanceMarks($student, $semester);

        // Calculate total
        $total = $this->calculateTotal($internalMarks, $externalMarks, $attendance);

        // Calculate SGPA / CGPA (placeholder)
        $sgpa = $this->calculateSGPA($student, $semester, $total);
        $cgpa = $this->calculateCGPA($student, $semester, $sgpa);

        // Insert or update
        AggregatedResult::updateOrCreate(
            [
                'student_id' => $student->id,
                'program_id' => $student->program_id,
                'semester'   => $semester,
            ],
            [
                'internal_marks'     => json_encode($internalMarks),
                'external_marks'     => json_encode($externalMarks),
                'attendance_marks'   => json_encode($attendance),
                'total_marks'        => json_encode($total),
                'sgpa'               => $sgpa,
                'cgpa'               => $cgpa,
                'compiled_at'        => now(),
            ]
        );
    }

    protected function getInternalMarks(Student $student, int $semester): array
    {
        // Fetch internal marks (you can customize based on your InternalResult model)
        return []; // e.g. ['MATH101' => 24, 'PHY102' => 22]
    }

    protected function getExternalMarks(Student $student, int $semester): array
    {
        // Fetch external marks
        return [];
    }

    protected function getAttendanceMarks(Student $student, int $semester): array
    {
        return [];
    }

    protected function calculateTotal(array $internal, array $external, array $attendance): array
    {
        $total = [];
        foreach ($internal as $code => $imark) {
            $total[$code] = $imark + ($external[$code] ?? 0) + ($attendance[$code] ?? 0);
        }
        return $total;
    }

    protected function calculateSGPA(Student $student, int $semester, array $total): float
    {
        return 7.5; // placeholder
    }

    protected function calculateCGPA(Student $student, int $semester, float $sgpa): float
    {
        return 8.2; // placeholder
    }
}
