<?php

namespace App\Imports;

use App\Models\Institute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\{
    ToCollection,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    SkipsEmptyRows
};
use Maatwebsite\Excel\Concerns\SkipsFailures;

class InstituteImport implements
    ToCollection,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    SkipsEmptyRows
{
    use SkipsFailures;   // gives us $this->failures()

    /**
     * Persist each valid row.
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            // Excel often treats numeric cells as integers.
            // Cast EVERYTHING to string so validation & DB agree.
            $code          = (string) $row['code'];
            $name          = (string) $row['name'];
            $contactEmail  = $row['contact_email'] ?? null;
            $contactPhone  = $row['contact_phone'] ?? null;

            // Skip duplicates (or change to updateOrCreate if desired)
            if (Institute::where('code', $code)->exists()) {
                continue;
            }

            Institute::create([
                'name'           => $name,
                'code'           => $code,
                'contact_email'  => $contactEmail,
                'contact_phone'  => $contactPhone,
                'password'       => Hash::make('1234inst'),
            ]);
        }
    }

    /**
     * Row‑level validation rules.
     */
    public function rules(): array
    {
        return [
            '*.name'          => 'required|string|max:255',
            '*.code'          => 'required',              // already cast to string
            '*.contact_email' => 'nullable|email',
            '*.contact_phone' => 'nullable|digits_between:6,15',
        ];
    }

    /**
     * Friendly column names in the error output.
     */
    public function customValidationAttributes(): array
    {
        return [
            'code'          => 'Institute code',
            'name'          => 'Institute name',
            'contact_email' => 'Contact email',
            'contact_phone' => 'Contact phone',
        ];
    }
}
