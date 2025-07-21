<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseMarkController;
use App\Models\{AcademicSession, Program, Student, Course};
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MarksTemplateExport;
use App\Exports\UploadedMarksExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\Builder;  


class RegularMarkController extends BaseMarkController
{
    protected function programmeType(): string
    {
        return 'regular';
    }
// RegularMarkController.php
public function downloadTemplate(Request $r)
{
    $data = $r->validate([
        'program_id' => 'required|exists:programs,id',
        'semester'   => 'required|integer|min:1',
        'mark_type'  => 'required|in:internal,external,attendance,all',
    ]);

    $program = Program::with(['courses' => function ($q) use ($data) {
        $q->wherePivot('semester', $data['semester'])->orderBy('course_code');
    }])->findOrFail($data['program_id']);

    $students = Student::where('program_id', $program->id)
        ->where('semester', $data['semester'])
        ->orderBy('nchm_roll_number')
        ->get();

    $courses = $program->courses;

    if ($students->isEmpty() || $courses->isEmpty()) {
        return redirect()->back()
            ->withErrors(['no_data' =>
                'No students or courses found for the selected criteria.']);
    }

    $fileName = sprintf(
        'template_P%s_S%s_%s.xlsx',
        $program->id,
        $data['semester'],
        $data['mark_type']
    );

    return Excel::download(
        new MarksTemplateExport($students, $courses, $data['mark_type']),
        $fileName
    );
}
// RegularMarkController.php
public function handleUploadMarksRegular(Request $r)
{
    $session = AcademicSession::findOrFail($r->session_id);

    [$preview, $courseCols, $markType] = $this->buildPreview($r);

    return redirect()
        ->route('admin.regular.exams.marks.upload', $session->id)
        ->with([
            'previewData' => $preview,
            'columns'     => $courseCols,
            'markType'    => $markType,

            // 🔑 NEW – flash these so the Blade has them
            'programId'   => $r->program_id,
            'semester'    => $r->semester,
        ]);
}

// RegularMarkController.php
private function buildPreview(Request $r): array
{
    $sheet   = Excel::toCollection(null, $r->file('marks_file'))->first();
    $header  = $sheet->first()->toArray();

    $courseCols = array_slice($header, 4);   // ← was 2
    $preview = [];

    foreach ($sheet->skip(1) as $row) {
        $data = array_values($row->toArray());
        if (!trim($data[0] ?? '')) continue;

        $rec = [
            'nchm_roll_number' => trim($data[0]),
            'name'             => trim($data[1] ?? ''),
            // keep Program & Semester just for display (optional)
        ];

        foreach ($courseCols as $i => $h) {
            $rec[$h] = $data[$i + 4] ?? null;           // ← index shift
        }
        $preview[] = $rec;
    }
    return [$preview, $courseCols, $r->mark_type];
}



 public function downloadUploadedMarks(Request $r)
    {
        $r->validate([
            'session_id' => 'required|exists:academic_sessions,id',
            'program_id' => 'required|exists:programs,id',
            'mark_type'  => 'required|in:internal,external,attendance,all',
        ]);

        $program  = Program::findOrFail($r->program_id);
        $session  = AcademicSession::findOrFail($r->session_id);
        $isSem    = $program->structure === 'semester';

        /* validate & resolve term */
        $r->validate($isSem
            ? ['semester' => 'required|integer|min:1|max:10']
            : ['year'     => 'required|integer|min:1|max:6']);

        $term = $isSem ? $r->semester : $r->year;

        /* courses mapped to that term */
        $courses = $program->courses()
                           ->wherePivot($isSem ? 'semester' : 'year', $term)
                           ->get();

        if ($courses->isEmpty()) {
            return back()->withErrors(['no_data' => 'No courses found for the selected combination.']);
        }

        /* students that actually have marks for that term & session */
       $students = Student::with([
        'program',
        // ⬇️ 1)  remove the Builder type‑hint here
        'marks' => function ($q) use ($courses, $session, $term, $isSem) {
            $q->whereIn('course_id', $courses->pluck('id'))
              ->where('session_id',  $session->id)
              ->when($isSem,
                     fn ($q) => $q->where('semester', $term),
                     fn ($q) => $q->where('year',     $term));
        }])
    ->where('program_id', $program->id)
    // ⬇️ 2)  leave the Builder type‑hint on the whereHas closure – here it **is** a Builder
    ->whereHas('marks', function (Builder $q) use ($courses, $session, $term, $isSem) {
        $q->whereIn('course_id', $courses->pluck('id'))
          ->where('session_id',  $session->id)
          ->when($isSem,
                 fn ($q) => $q->where('semester', $term),
                 fn ($q) => $q->where('year',     $term));
    })
    ->orderBy('nchm_roll_number')
    ->get();

        if ($students->isEmpty()) {
            return back()->withErrors(['no_data' => 'No marks found for the selected combination.']);
        }

        /* stream Excel */
        return Excel::download(
            new UploadedMarksExport($students, $courses, $r->mark_type, $program->structure),
            sprintf('marks_%s_P%s_T%s_%s.xlsx',
                $session->year, $program->id, $term, $r->mark_type)
        );
    }

}
