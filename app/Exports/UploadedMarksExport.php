<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UploadedMarksExport implements FromArray, WithHeadings
{
    protected $students;
    protected $markType;
    protected $courses;
    protected $structure;   // ⬅️  NEW

    /**
     * @param  \Illuminate\Support\Collection|array  $students
     * @param  \Illuminate\Support\Collection|array  $courses
     * @param  string  $markType   internal|external|attendance|all
     * @param  string|null  $structure  semester|yearly   ⬅️  NEW (optional)
     */
    public function __construct($students, $courses, $markType, $structure = null)
    {
        $this->students  = $students;
        $this->courses   = $courses;
        $this->markType  = $markType;

        // decide ‘semester’ or ‘yearly’ once
        $this->structure = $structure
            ?? ($students[0]->program->structure ?? 'semester');
    }

    /* ------------------------------------------------------------------ */
    /*  DATA                                                              */
    /* ------------------------------------------------------------------ */
   /* ------------------------------------------------------------------ */
/*  DATA                                                              */
/* ------------------------------------------------------------------ */
public function array(): array
{
    $rows = [];

    foreach ($this->students as $student) {
        /* — base cells — */
        $row = [
            'Roll No'  => $student->nchm_roll_number,
            'Name'     => $student->name,
            'Program'  => $student->program->name ?? 'N/A',
            $this->structure === 'yearly' ? 'Year' : 'Semester'
                       => $this->structure === 'yearly'
                          ? $student->year      // make sure this exists
                          : $student->semester,
        ];

        /* — marks keyed by numeric course_id (already eager‑loaded) — */
    $marks = $student->marks->keyBy('course_id');


        foreach ($this->courses as $course) {
            $key = $course->id;
            $code = $course->course_code;
            $mark = $marks->get($key);   // null if no record

            /* Build exactly the columns the user asked for  */
            switch ($this->markType) {
                case 'internal':
                    $row["$code (Internal)"] =
                        $mark?->internal ?? '';  // show value or leave blank
                    break;

                case 'external':
                    $row["$code (External)"] =
                        $mark?->external ?? '';
                    break;

                case 'attendance':
                    $row["$code (Attendance)"] =
                        $mark?->attendance ?? '';
                    break;

                case 'all':
                    $row["$code (Internal)"]   = $mark?->internal   ?? '';
                    $row["$code (External)"]   = $mark?->external   ?? '';
                    $row["$code (Attendance)"] = $mark?->attendance ?? '';
                    break;
            }
        }

        $rows[] = $row;
    }

    return $rows;
}

/* ------------------------------------------------------------------ */
/*  HEADINGS                                                          */
/* ------------------------------------------------------------------ */
public function headings(): array
{
    $headings = [
        'Roll No',
        'Name',
        'Program',
        $this->structure === 'yearly' ? 'Year' : 'Semester',
    ];

    foreach ($this->courses as $course) {
        $code = $course->course_code;

        switch ($this->markType) {
            case 'internal':
                $headings[] = "$code (Internal)";
                break;

            case 'external':
                $headings[] = "$code (External)";
                break;

            case 'attendance':
                $headings[] = "$code (Attendance)";
                break;

            case 'all':
                $headings[] = "$code (Internal)";
                $headings[] = "$code (External)";
                $headings[] = "$code (Attendance)";
                break;
        }
    }

    return $headings;
}
}