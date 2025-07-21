<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InstituteTemplateExport implements FromArray, WithHeadings
{
    /**
     * Rows you want to pre‑populate.
     * Keep them blank if you only need headings.
     */
    public function array(): array
    {
        return [
            ['ABC Institute', 'ABC001', 'abc@example.com', '9876543210'],
            ['XYZ Institute', 'XYZ002', 'xyz@example.com', '9123456789'],
        ];
    }

    /**
     * Excel column headers ( **must** match what
     * your `InstituteImport` expects ).
     */
    public function headings(): array
    {
        return ['name', 'code', 'contact_email', 'contact_phone'];
    }
}
