<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Course;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class AssignedCoursesImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            $nchmRoll = trim($row['nchm_roll_number'] ?? '');

            if (empty($nchmRoll)) {
                Log::warning("Row $rowNumber skipped: missing NCHM roll number.");
                continue;
            }

            $student = Student::where('nchm_roll_number', $nchmRoll)->first();

            if (!$student) {
                Log::warning("Row $rowNumber skipped: student not found.");
                continue;
            }

            // Gather all course_code_* values
            $courseCodes = [];
            foreach ($row->toArray() as $key => $value) {
                if (str_starts_with($key, 'course_code') && !empty($value)) {
                    $courseCodes[] = trim($value);
                }
            }

            if (empty($courseCodes)) {
                Log::warning("Row $rowNumber skipped: no course codes found.");
                continue;
            }

            $courseIds = Course::whereIn('course_code', $courseCodes)->pluck('id')->toArray();

            if (empty($courseIds)) {
                Log::warning("Row $rowNumber skipped: no valid course IDs found.");
                continue;
            }

            // Add new courses without removing old ones
            $student->courses()->syncWithoutDetaching($courseIds);
        }
    }
}
