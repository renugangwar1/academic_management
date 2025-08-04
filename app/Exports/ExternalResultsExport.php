<?php

namespace App\Exports;

use App\Models\Course;
use App\Models\ExternalResult;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExternalResultsExport implements FromCollection, WithHeadings
{
    protected $programId, $semester, $sessionId, $courses;

    public function __construct($sessionId, $programId, $semester)
    {
        $this->sessionId = $sessionId;
        $this->programId = $programId;
        $this->semester  = $semester;

        // Fetch courses only for this program, semester, and session
        $this->courses = Course::whereHas('externalResults', function ($q) {
                $q->where('program_id', $this->programId)
                  ->where('semester', $this->semester)
                  ->where('academic_session_id', $this->sessionId);
            })
            ->orderBy('course_code')
            ->get([
                'id', 'course_code', 'course_title',
                'has_internal', 'has_external', 'has_attendance'
            ]);
    }

    public function collection()
    {
        return ExternalResult::with(['student', 'course', 'program'])
            ->where('program_id', $this->programId)
            ->where('semester', $this->semester)
            ->where('academic_session_id', $this->sessionId) // ✅ session filter
            ->get()
            ->groupBy('student_id')
            ->map(function ($results) {
                $student = $results->first()->student;
                $program = $results->first()->program;

                $totalMarks       = 0;
                $obtainedMarks    = 0;
                $totalCredits     = 0;
                $obtainedCredits  = 0;
                $creditPoints     = 0;
                $failingSystemIds = $failingCodes = $failingCourses = [];

                $row = [
                    'Programme'         => $program->name,
                    'Student Name'      => $student->name,
                    'Roll Number'       => $student->nchm_roll_number,
                    'Enrolment Number'  => $student->enrolment_number ?? '',
                    'Term'              => 'Semester ' . $this->semester,
                ];

                foreach ($this->courses as $course) {
                    $res = $results->firstWhere('course_id', $course->id);

                    if ($course->has_internal)
                        $row[$course->course_code . ' (Internal)'] = $res->internal ?? '-';

                    if ($course->has_external)
                        $row[$course->course_code . ' (External)'] = $res->external ?? '-';

                    if ($course->has_attendance)
                        $row[$course->course_code . ' (Attendance)'] = $res->attendance ?? '-';

                    $row[$course->course_code . ' (Total)']       = $res->total ?? '-';
                    $row[$course->course_code . ' (Grade)']       = $res->grade_letter ?? '-';
                    $row[$course->course_code . ' (Credit)']      = $res->credit ?? '-';
                    $row[$course->course_code . ' (Grade Point)'] = $res->grade_point ?? '-';
                    $row[$course->course_code . ' (Credit×GP)']   = $res ? ($res->grade_point * $res->credit) : '-';

                    if ($res) {
                        $totalMarks       += 100;
                        $obtainedMarks    += $res->total;
                        $totalCredits     += $res->credit;
                        $obtainedCredits  += $res->grade_point > 0 ? $res->credit : 0;
                        $creditPoints     += $res->grade_point * $res->credit;

                      if ($res->grade_letter === 'F') {
    $failingSystemIds[] = $res->course_id;
    $failingCodes[]     = $res->course->course_code;
    $failingCourses[]   = $res->course->course_title;
}

                    }
                }

                $row = array_merge($row, [
                    'Total marks'                 => $totalMarks,
                    'Obtained Marks'              => $obtainedMarks,
                    'Total Course Credit'         => $totalCredits,
                    'Obtained Credit Point'       => $creditPoints,
                    'SGPA'                        => $results->first()->sgpa,
                    'CGPA'                        => $results->first()->cgpa,
                    'GRADE'                       => $results->first()->grade_letter,
                    'Result Status'               => $results->first()->result_status,
                    'Promotion Status'            => '',
                    'Remarks'                     => '',
                    'Failing Course System Id(s)' => implode(', ', $failingSystemIds),
                    'Failing Course Code(s)'      => implode(', ', $failingCodes),
                    'Failing Course(s)'           => implode(', ', $failingCourses),
                ]);

                return $row;
            })
            ->values();
    }

    public function headings(): array
    {
        $base = [
            'Programme',
            'Student Name',
            'Roll Number',
            'Enrolment Number',
            'Term',
        ];

        $courseColumns = [];
        foreach ($this->courses as $course) {
            if ($course->has_internal)
                $courseColumns[] = $course->course_code . ' (Internal)';

            if ($course->has_external)
                $courseColumns[] = $course->course_code . ' (External)';

            if ($course->has_attendance)
                $courseColumns[] = $course->course_code . ' (Attendance)';

            $courseColumns[] = $course->course_code . ' (Total)';
            $courseColumns[] = $course->course_code . ' (Grade)';
            $courseColumns[] = $course->course_code . ' (Credit)';
            $courseColumns[] = $course->course_code . ' (Grade Point)';
            $courseColumns[] = $course->course_code . ' (Credit×GP)';
        }

        $summary = [
            'Total marks',
            'Obtained Marks',
            'Total Course Credit',
            'Obtained Credit Point',
            'SGPA',
            'CGPA',
            'GRADE',
            'Result Status',
            'Promotion Status',
            'Remarks',
            'Failing Course System Id(s)',
            'Failing Course Code(s)',
            'Failing Course(s)',
        ];

        return array_merge($base, $courseColumns, $summary);
    }
}
