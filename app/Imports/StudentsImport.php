<?php
namespace App\Imports;

use App\Models\AcademicSession;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Maatwebsite\Excel\Concerns\{
    ToCollection, WithHeadingRow, WithValidation,
    WithChunkReading, SkipsOnFailure, SkipsFailures
};

class StudentsImport implements ToCollection, WithHeadingRow, WithValidation,
                                 WithChunkReading, SkipsOnFailure
{
    use SkipsFailures;

    public function __construct(
        protected ?int    $programId = null,
        protected ?string $structure = null
    ) {}

    /** cache <year_term string → session id> */
    protected array $sessions     = [];
       protected array $instituteCache = [];     
    protected int   $rowsImported = 0;

    /* ───────── Handle one chunk ───────── */
    public function collection(Collection $rows): void
{
    // 1) Ensure all (year, term) pairs exist
    $rows
        ->map(fn ($r) => [$r['year'], $r['term']])
        ->unique()
        ->each(fn ($pair) => $this->getSessionId(...$pair));

    $now = now();

    $insert = $rows->map(function ($r) use ($now) {
        $sessionId = $this->getSessionId($r['year'], $r['term']);

        // Get institute_id from code, using cache
        $code = trim($r['institute_code']);
        $instituteId = $this->instituteCache[$code]
            ??= DB::table('institutes')->where('code', $code)->value('id');

        if (!$instituteId) {
            throw new \Exception("Invalid institute_code: {$code}");
        }

      return [
    'name'                 => $r['name'],
    'nchm_roll_number'     => $r['nchm_roll_number'],
    'enrolment_number'     => $r['enrolment_number'],
    'program_id'           => $this->programId ?? $r['program_id'],
    'institute_id'         => $instituteId,
    'semester'             => $this->structure === 'semester'
                                ? (ctype_digit((string) $r['semester']) ? (int) $r['semester'] : throw new \Exception("Invalid semester: {$r['semester']}"))
                                : null,
    'year'                 => $this->structure === 'yearly'
                                ? (ctype_digit((string) $r['year_level']) ? (int) $r['year_level'] : throw new \Exception("Invalid year_level: {$r['year_level']}"))
                                : null,
    'academic_session_id'  => $sessionId, // ✅ this will now map correctly
    'email'                => $r['email'],
    'mobile'               => $r['mobile'],
    'date_of_birth'        => $this->parseDate($r['date_of_birth']),
    'category'             => $r['category'],
    'father_name'          => $r['father_name'],
    'status'               => $r['status'] ?? 1,
    'created_at'           => $now,
    'updated_at'           => $now,
];


    })->all();

    if ($insert) {
        DB::table('students')->insert($insert);
        $this->rowsImported += count($insert);
    }
}


    /* ── Small helper that normalises term + caches session id ── */
    protected function getSessionId(string $year, string $term): int
    {
        $year = trim($year);
        $term = ucfirst(strtolower(trim($term)));   // “jan” → “Jan”

        $key = $year.'_'.$term;

        return $this->sessions[$key] ??= AcademicSession::firstOrCreate(
            ['year' => $year, 'term' => $term],
            ['odd_even' => $term === 'July' ? 'odd' : 'even']
        )->id;
    }

    public function importedCount(): int { return $this->rowsImported; }
    public function chunkSize(): int     { return 1000; }   // adjust as you like

    /* ───────── Validation ───────── */
   public function rules(): array
{
    $rules = [
        '*.name'             => 'required|string',
        '*.nchm_roll_number' => 'required|unique:students,nchm_roll_number',
        '*.institute_code'   => 'required|exists:institutes,code',
        '*.year_level'       => 'nullable|integer|min:1|max:10',
        '*.term'             => 'required|in:Jan,July',
        '*.email'            => 'nullable|email',
        '*.mobile'           => 'nullable|digits_between:6,15',
        '*.date_of_birth'    => ['nullable', function ($attr, $value, $fail) {
            if ($value !== '' && $value !== null && ! $this->parseDate($value)) {
                $fail("Bad date \"{$value}\" – use DD-MM-YYYY, DD/MM/YYYY, YYYY-MM-DD, or an Excel date.");
            }
        }],
    ];

    if (! $this->programId) {
        $rules['*.program_id'] = 'required|exists:programs,id';
    }

    return $rules;
}


    public function customValidationMessages(): array
    {
        return [
            '*.nchm_roll_number.unique' => 'This NCHM Roll No. already exists.',
            '*.term.in'                => 'Term must be Jan or July.',
        ];
    }

    /* ───────── Date helper ───────── */
    protected function parseDate($value): ?string
    {
        if ($value === null || $value === '') return null;

        if (is_numeric($value) && (int) $value > 25568) {
            try { return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d'); }
            catch (\Exception) {}
        }

        foreach (['d-m-Y', 'd/m/Y', 'Y-m-d'] as $fmt) {
            try { return Carbon::createFromFormat($fmt, $value)->format('Y-m-d'); }
            catch (\Exception) {}
        }

        if ($ts = strtotime($value)) return date('Y-m-d', $ts);
        return null;
    }
}
