<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AssignedCoursesTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'nchm_roll_number',
            'semester',
            'course_code_1',
            'course_code_2',
            'course_code_3',
            'course_code_4', // Add as many as needed
        ];
    }

    public function array(): array
    {
        return [
            ['2541001012', '1', 'CS101', 'CS102', 'CS103', 'CS104'],
        ];
    }
}
