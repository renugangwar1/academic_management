<?php
// app/Imports/MarksImport.php
namespace App\Imports;

use App\Models\{Student, Mark, Course};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class MarksImport implements ToCollection
{
    public function __construct(
        private string $markType,
        private int    $sessionId  , // ➊ new arg
          private string $structure // 'semester' or 'year'
    ) {}

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) return;

        $header       = $rows->first()->toArray();
        $courseLabels = array_slice($header, 4);   // after Semester / Year col
        $startCol     = 4;

        foreach ($rows->skip(1) as $row) {
            $cells   = $row->toArray();
            $roll    = trim($cells[0] ?? '');
            $student = $roll ? Student::where('nchm_roll_number',$roll)->first() : null;
            if (!$student) continue;

            /** figure out the term shown in col‑4                               *
             *  – works for both semester‐ and year‐based programmes             */
            $term = (int) ($cells[3] ?? null);   // empty → 0, fine for nullable

            $col = $startCol;
            foreach ($courseLabels as $label) {

                /* --- parse "HM101|15 (Internal)" ----------------------------- */
                if (!preg_match('/^(.+?)\|(\d+)\s+\(([^)]+)\)$/', $label, $m)) {
                    $col += $this->colsPerCourse();    // malformed header – skip
                    continue;
                }
                [, , $id, $suffix] = $m;
                $course = Course::find((int) $id);
                if (!$course) { $col += $this->colsPerCourse(); continue; }

                /* --- optional‑course guard ----------------------------------- */
                $enrolled = DB::table('course_student')
                            ->where('student_id',$student->id)
                            ->where('course_id' ,$course->id)
                            ->exists();
                if (!$enrolled) { $col += $this->colsPerCourse(); continue; }

                /* --- map suffix → field, honour mark‑type filter -------------- */
                $field = match (strtolower($suffix)) {
                    'internal'   => $this->accept('internal',   $course) ? 'internal'   : null,
                    'external'   => $this->accept('external',   $course) ? 'external'   : null,
                    'attendance' => $this->accept('attendance', $course) ? 'attendance' : null,
                    default      => null,
                };

                $value = $this->toInt($cells[$col] ?? null);
                $col++;

                if (!$field || $value === null) continue;

                /* --- upsert marks row ---------------------------------------- */
                $base = [
    'student_id' => $student->id,
    'course_id'  => $course->id,
    'session_id' => $this->sessionId,
];

if ($this->structure === 'semester') {
    $base['semester'] = $term;
} else {
    $base['year'] = $term;
}

                $mark = Mark::firstOrNew($base);
                $mark->$field = $value;
                $mark->total  = ($mark->internal   ?? 0)
                              + ($mark->external   ?? 0)
                              + ($mark->attendance ?? 0);
                $mark->save();
            }
        }
    }

    /* ---------- helpers -------------------------------------------------- */
    private function colsPerCourse(): int
    {
        return in_array($this->markType, ['internal','external','attendance']) ? 1 : 3;
    }

    private function accept(string $type, Course $c): bool
    {
        return match ($this->markType) {
            'internal'   => $type==='internal'   && $c->has_internal,
            'external'   => $type==='external'   && $c->has_external,
            'attendance' => $type==='attendance' && $c->has_attendance,
            default      => match($type) {
                'internal'   => $c->has_internal,
                'external'   => $c->has_external,
                'attendance' => $c->has_attendance,
            },
        };
    }

    private function toInt(mixed $v): ?int
    {
        return is_numeric($v) ? (int) $v : null;
    }
}
