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
use Illuminate\Support\Facades\Log;



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
          'session_id' => 'required|exists:academic_sessions,id',
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


// public function handleUploadMarksRegular(Request $r)
// {
//     Log::info('Uploading Marks: Incoming request', $r->all());

//     try {
//       $session = AcademicSession::findOrFail($r->academic_session_id);

//      Log::info('Session found', ['academic_session_id' => $r->academic_session_id]);


//         [$preview, $courseCols, $markType] = $this->buildPreview($r);
//         Log::info('Preview built', [
//             'course_columns' => $courseCols,
//             'mark_type' => $markType,
//             'preview_sample' => array_slice($preview, 0, 3), // log first 3 rows
//         ]);

//         return redirect()
//             ->route('admin.regular.exams.marks.upload', ['session' => $session->id])
//             ->with([
//                 'previewData' => $preview,
//                 'columns'     => $courseCols,
//                 'markType'    => $markType,
//                 'programId'   => $r->program_id,
//                 'semester'    => $r->semester,
//             ]);

//     } catch (\Throwable $e) {
//         Log::error('Error during mark upload', [
//             'message' => $e->getMessage(),
//             'trace' => $e->getTraceAsString(),
//         ]);
//         return back()->withErrors('Something went wrong. Please check the log.');
//     }
// }

public function handleUploadMarksRegular(Request $r)
{
    Log::info('Uploading Marks: Incoming request', $r->all());

    try {
        $session = AcademicSession::findOrFail($r->academic_session_id);

        Log::info('Session found', ['academic_session_id' => $r->academic_session_id]);

        [$preview, $courseCols, $markType] = $this->buildPreview($r);

        Log::info('Preview built', [
            'course_columns' => $courseCols,
            'mark_type' => $markType,
            'preview_sample' => array_slice($preview, 0, 3),
        ]);

        // ✅ SAVE MARKS INTO DATABASE
        $fieldToUpdate = match($markType) {
            'internal'   => 'internal_marks',
            'external'   => 'external_marks',
            'attendance' => 'attendance_marks',
            default      => null,
        };

        if ($fieldToUpdate) {
            foreach ($preview as $row) {
                $student = Student::where('nchm_roll_number', $row['nchm_roll_number'])->first();
                if (!$student) continue;

                foreach ($courseCols as $courseCode) {
                    $course = Course::where('course_code', $courseCode)->first();
                    if (!$course) continue;

                    $existing = Mark::where([
                        'student_id' => $student->id,
                        'course_id'  => $course->id,
                        'session_id' => $r->academic_session_id,
                        'semester'   => $r->semester,
                    ])->first();

                    if ($existing) {
                        $existing->$fieldToUpdate = $row[$courseCode];
                        $existing->save();
                    } else {
                        Mark::create([
                            'student_id'   => $student->id,
                            'course_id'    => $course->id,
                            'session_id'   => $r->academic_session_id,
                            'semester'     => $r->semester,
                            $fieldToUpdate => $row[$courseCode],
                        ]);
                    }
                }
            }

            Log::info('Marks successfully saved.');
        } else {
            Log::warning("Invalid mark type provided: {$markType}");
        }

        return redirect()
            ->route('admin.regular.exams.marks.upload', ['session' => $session->id])
            ->with([
                'previewData' => $preview,
                'columns'     => $courseCols,
                'markType'    => $markType,
                'programId'   => $r->program_id,
                'semester'    => $r->semester,
            ]);

    } catch (\Throwable $e) {
        Log::error('Error during mark upload', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return back()->withErrors('Something went wrong. Please check the log.');
    }
}


private function buildPreview(Request $r): array
{
    if (!$r->hasFile('marks_file')) {
        Log::warning('No marks_file found in request');
        throw new \Exception('No marks file uploaded.');
    }

    $sheet = Excel::toCollection(null, $r->file('marks_file'))->first();
    if (!$sheet || $sheet->isEmpty()) {
        Log::warning('Uploaded file is empty or unreadable');
        throw new \Exception('Uploaded file is empty or not a valid Excel.');
    }

    $header = $sheet->first()->toArray();
    Log::info('Excel Header Row', $header);

    $courseCols = array_slice($header, 4); // Adjusted from 2 to 4
    $preview = [];

    foreach ($sheet->skip(1) as $row) {
        $data = array_values($row->toArray());
        if (!trim($data[0] ?? '')) continue;

        $rec = [
            'nchm_roll_number' => trim($data[0]),
            'name'             => trim($data[1] ?? ''),
        ];

        foreach ($courseCols as $i => $h) {
            $rec[$h] = $data[$i + 4] ?? null;
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
    $session  = AcademicSession::findOrFail($r->session_id); // ✅ FIXED

    $isSem    = $program->structure === 'semester';

    $r->validate($isSem
        ? ['semester' => 'required|integer|min:1|max:10']
        : ['year'     => 'required|integer|min:1|max:6']);

    $term = $isSem ? $r->semester : $r->year;

    $courses = $program->courses()
        ->wherePivot($isSem ? 'semester' : 'year', $term)
        ->get();

    if ($courses->isEmpty()) {
        return back()->withErrors(['no_data' => 'No courses found for the selected combination.']);
    }

    $students = Student::with([
        'program',
        'marks' => function ($q) use ($courses, $session, $term, $isSem) {
            $q->whereIn('course_id', $courses->pluck('id'))
              ->where('session_id',  $session->id)
              ->when($isSem,
                     fn ($q) => $q->where('semester', $term),
                     fn ($q) => $q->where('year',     $term));
        }
    ])
    ->where('program_id', $program->id)
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

    return Excel::download(
        new UploadedMarksExport($students, $courses, $r->mark_type, $program->structure),
        sprintf('marks_%s_P%s_T%s_%s.xlsx',
            $session->year, $program->id, $term, $r->mark_type)
    );
}



}
