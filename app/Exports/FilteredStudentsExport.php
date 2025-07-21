<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Http\Request;

class FilteredStudentsExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        return Student::with(['program', 'institute'])
            ->when($this->request->program_id, fn($q) => $q->where('program_id', $this->request->program_id))
            ->when($this->request->search, function ($q) {
                $s = $this->request->search;
                $q->where(fn($x) => $x->where('name', 'like', "%$s%")
                    ->orWhere('nchm_roll_number', 'like', "%$s%")
                    ->orWhere('enrolment_number', 'like', "%$s%")
                    ->orWhere('email', 'like', "%$s%")
                    ->orWhere('mobile', 'like', "%$s%"));
            })
            ->get()
            ->map(function ($student) {
                return [
                    'NCHM Roll No'    => $student->nchm_roll_number,
                    'Enrolment No'    => $student->enrolment_number,
                    'Name'            => $student->name,
                    'Program'         => $student->program->name ?? '-',
                    'Institute'       => $student->institute->name ?? '-',
                    'Academic Year'   => $student->academic_year,
                    'Session'         => ucfirst($student->session),
                    'Email'           => $student->email,
                    'Mobile'          => $student->mobile,
                    'DOB'             => optional($student->date_of_birth)->format('d-m-Y'),
                    'Category'        => $student->category,
                    'Father Name'     => $student->father_name,
                    'Status'          => $student->status ? 'Active' : 'Inactive',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'NCHM Roll No',
            'Enrolment No',
            'Name',
            'Program',
            'Institute',
            'Academic Year',
            'Session',
            'Email',
            'Mobile',
            'DOB',
            'Category',
            'Father Name',
            'Status',
        ];
    }
}
