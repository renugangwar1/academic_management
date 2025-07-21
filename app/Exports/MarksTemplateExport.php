<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MarksTemplateExport implements FromArray, WithHeadings
{
    public function __construct(
        protected $students,
        protected $courses,
        protected string $markType = 'all',
    ) {
    }

    /* ---------------------------------------------------------- */
    /*  DATA BODY (after header)                                  */
    /* ---------------------------------------------------------- */

    public function array(): array
    {
        $rows = [];

        foreach ($this->students as $student) {
            $row = [
                'Roll No'  => $student->nchm_roll_number,
                'Name'     => $student->name,
                'Program'  => $student->program->name ?? 'N/A',
                'Semester' => $student->semester,
            ];

            foreach ($this->courses as $course) {
                foreach ($this->labelSuffixes($course) as $suffix) {
                    $row[$this->label($course, $suffix)] = '';
                }
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /* ---------------------------------------------------------- */
    /*  HEADER ROW                                                */
    /* ---------------------------------------------------------- */

    public function headings(): array
    {
        $headings = [
            'Roll No',
            'Name',
            'Program',
            'Semester',
        ];

        foreach ($this->courses as $course) {
            foreach ($this->labelSuffixes($course) as $suffix) {
                $headings[] = $this->label($course, $suffix);
            }
        }

        return $headings;
    }

    /* ---------------------------------------------------------- */
    /*  Helpers                                                   */
    /* ---------------------------------------------------------- */

    private function label($course, string $suffix): string
    {
        // Embeds the DB id after a pipe so the import can pick it up
        return "{$course->course_code}|{$course->id} ({$suffix})";
    }

    private function labelSuffixes($course): array
    {
        $map = [
            'internal'   => $course->has_internal,
            'external'   => $course->has_external,
            'attendance' => $course->has_attendance,
        ];

        return match ($this->markType) {
            'internal', 'external', 'attendance' => $map[$this->markType] ? [$this->markType === 'attendance' ? 'Attendance' : ucfirst($this->markType)] : [],
            default => array_map(fn($k) => $k === 'attendance' ? 'Attendance' : ucfirst($k),
                                array_keys(array_filter($map))),
        };
    }
}