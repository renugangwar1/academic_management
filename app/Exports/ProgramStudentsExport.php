<?php
namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProgramStudentsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $programId;

    public function __construct($programId)
    {
        $this->programId = $programId;
    }

    public function collection()
    {
        return Student::with('institute', 'courses')
            ->where('program_id', $this->programId)
            ->get()
            ->map(function ($stu) {
                return [
                    'Name' => $stu->name,
                    'NCHM Roll No' => $stu->nchm_roll_number,
                    'Enrolment No' => $stu->enrolment_number,
                    'Institute' => $stu->institute->name ?? 'N/A',
                    'Semester' => $stu->semester,
                    'Academic Year' => $stu->academic_year,
                    'Session' => $stu->session,
                    'Email' => $stu->email,
                    'Mobile' => $stu->mobile,
                  'Date of Birth' => optional($stu->date_of_birth)->format('Y-m-d'),

                    'Category' => $stu->category,
                    'Father Name' => $stu->father_name,
                    'Status' => $stu->status ? 'Active' : 'Inactive',
                    'Courses' => $stu->courses->pluck('course_code')->join(', ')
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Name', 'NCHM Roll No', 'Enrolment No', 'Institute', 'Semester',
            'Academic Year', 'Session', 'Email', 'Mobile', 'Date of Birth',
            'Category', 'Father Name', 'Status', 'Courses'
        ];
    }
}
