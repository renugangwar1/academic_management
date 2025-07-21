<?php
namespace App\Exports;

use App\Models\Mark;
use App\Models\Student;
use App\Models\Program;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MatrixMarksExport implements FromArray, WithHeadings
{
    protected $students, $courses, $program, $semester, $index;

    public function __construct($students, $courses, $program, $semester, $index)
    {
        $this->students = $students;
        $this->courses = $courses;
        $this->program = $program;
        $this->semester = $semester;
        $this->index = $index;
    }

    public function array(): array
    {
        $data = [];

        foreach ($this->students as $student) {
            $row = [
                $student->nchm_roll_number,
                $student->name,
                $this->program->name,
                $this->semester,
            ];

            $total = 0;
            foreach ($this->courses as $course) {
                $mark = $this->index[$student->id][$course->id] ?? null;
                $int = $mark->internal ?? 0;
                $ext = $mark->external ?? 0;
                $row[] = $int;
                $row[] = $ext;
                $total += $int + $ext;
            }

            $row[] = $total;
            $data[] = $row;
        }

        return $data;
    }

    public function headings(): array
    {
        $headings = ['Roll No', 'Name', 'Program', 'Semester'];

        foreach ($this->courses as $course) {
            $headings[] = $course->course_code . ' (Int)';
            $headings[] = $course->course_code . ' (Ext)';
        }

        $headings[] = 'Total';
        return $headings;
    }
}
