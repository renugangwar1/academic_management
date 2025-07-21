<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CourseTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [
                'CS101',                    // course_code
                'Introduction to Programming', // course_title
                'Theory',                  // type (Theory or Practical)
                3,                         // credit_hours
                3.0,                       // credit_value
                1,                         // has_attendance (1 = Yes, 0 = No)
                1,                         // has_internal (1 = Yes, 0 = No)
                1                          // has_external (1 = Yes, 0 = No)
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'course_code',
            'course_title',
            'type',            // "Theory" or "Practical"
            'credit_hours',
            'credit_value',
            'has_attendance',  // 1 = Yes, 0 = No
            'has_internal',    // 1 = Yes, 0 = No
            'has_external',    // 1 = Yes, 0 = No
        ];
    }
}
