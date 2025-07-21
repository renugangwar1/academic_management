<?php

namespace App\Exports;

use App\Models\Program;               // ⬅ we inject Program
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class StudentsTemplateExport implements FromArray, WithHeadings, WithEvents
{
    protected Program $program;        // current programme

    public function __construct(Program $program)
    {
        $this->program = $program;
    }

    /** Column headings ********************************************************/
   public function headings(): array
{
    return [
        'name', 'nchm_roll_number', 'enrolment_number',
      'program_id', 'institute_code',  
        'semester', 'year_level',          // year_level = 1,2,3… if yearly
        'year',                            // 2023-2024
        'term',                            // Jan / July  (dropdown)
        'email', 'mobile', 'date_of_birth',
        'category', 'father_name', 'status'
    ];
}

public function array(): array
{
    return [[
        'John Doe', '1234567890', 'NCHMCT/2024/0010',
         $this->program->id, '101',  
        $this->program->structure === 'semester' ? 1 : null,
        $this->program->structure === 'yearly'   ? 1 : null,
        '2023-2024', 'July',
        'john@example.com', '9876543210',
        '2000-01-01', 'GEN', 'Michael Doe', 1
    ]];
}

public function registerEvents(): array
{
    return [
        AfterSheet::class => function (AfterSheet $e) {
            $sheet = $e->sheet->getDelegate();
            // dropdown now on column J (10) because ‘term’ moved
            for ($r = 2; $r <= 1000; $r++) {
                $v = $sheet->getCell('J'.$r)->getDataValidation();
                $v->setType(DataValidation::TYPE_LIST)
                  ->setFormula1('"Jan,July"');
            }
        },
    ];
}
}
