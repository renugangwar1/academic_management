<?php
namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentsExport implements FromCollection, WithHeadings
{
    protected $instituteId;

    public function __construct($instituteId)
    {
        $this->instituteId = $instituteId;
    }

    public function collection()
    {
        return Student::where('institute_id', $this->instituteId)
            ->with('program')
            ->get()
            ->map(function ($student) {
                return [
                    'Name'       => $student->name,
                    'Roll No'    => $student->nchm_roll_number,
                    'Program'    => $student->program->name ?? 'N/A',
                    'Semester'   => $student->semester ?? 'N/A',
                    'Mobile'     => $student->mobile,
                    'Email'      => $student->email,
                ];
            });
    }

    public function headings(): array
    {
        return ['Name', 'Roll No', 'Program', 'Semester', 'Mobile', 'Email'];
    }
}

