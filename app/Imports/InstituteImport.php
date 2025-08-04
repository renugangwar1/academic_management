<?php

namespace App\Imports;

use App\Models\Institute;
use Illuminate\Support\Collection;
use App\Models\User;
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
        $code         = (string) $row['code'];
        $name         = (string) $row['name'];
        $contactEmail = $row['email'] ?? null;
        $contactPhone = $row['contact_phone'] ?? null;

        // Skip if institute already exists
        if (Institute::where('code', $code)->exists()) {
            continue;
        }

        // Create institute
        $institute = Institute::create([
            'name'          => $name,
            'code'          => $code,
            'email'         => $contactEmail,
            'contact_phone' => $contactPhone,
            'password'      => Hash::make('1234inst'),
        ]);

        // Create corresponding user account
        $userEmail = $contactEmail ?? strtolower($code) . '@nchmct.institute';

        if (!User::where('email', $userEmail)->exists()) {
            User::create([
                'name'     => $name,
                'email'    => $userEmail,
                'role'     => 'institute',
                'password' => Hash::make('1234inst'),
            ]);
        }
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
            '*.email' => 'nullable|email',
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
            'email' => 'Contact email',
            'contact_phone' => 'Contact phone',
        ];
    }
}
