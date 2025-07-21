<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Institute;
use App\Models\Program;
class ReappearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $institutes = Institute::orderBy('name')->get();
        $programs   = Program::orderBy('name')->get();

        // resources/views/admin/reappears/index.blade.php
        return view('admin.reappears.index', compact('institutes', 'programs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    
public function downloadReappear(Request $request)
{
    /* 1️⃣  Validate incoming filters */
    $request->validate([
        'institute_id' => 'required|exists:institutes,id',
        'program_id'   => 'required|exists:programs,id',
        'semester'     => 'nullable|integer',
        'year'         => 'nullable|integer',
    ]);

    /* 2️⃣  Build one eager‑loading query */
    $students = Student::with([
            'program',
            'institute',
            // eager‑load only the REAPPEAR results and their courses
            'internalResults' => function ($q) use ($request) {
                $q->where('status', 'REAPPEAR')
                  ->when($request->semester, fn ($q, $sem) => $q->where('semester', $sem))
                  ->with(['course' => function ($q) {
                      $q->select('courses.id', 'course_code');
                  }]);
            },
        ])
        ->where('institute_id', $request->institute_id)
        ->where('program_id',   $request->program_id)
        ->when($request->year,  fn ($q, $y) => $q->where('year', $y))   // if you store year on Student
        ->get();

    /* 3️⃣  Derive reappear course codes and discard students with none */
    $students = $students->filter(function ($student) {
        $codes = $student->internalResults                // ← only REAPPEAR ones are loaded
            ->filter(function ($result) use ($student) {
                // keep only non‑optional courses
                return $result->course &&
                       $result->course
                              ->students
                              ->where('pivot.student_id', $student->id)
                              ->where('pivot.is_optional', false)
                              ->isNotEmpty();
            })
            ->pluck('course.course_code')
            ->unique()
            ->values();

        // attach for Blade
        $student->setRelation('reappearCourses', $codes);

        return $codes->isNotEmpty();                      // keep only if there is something to print
    })->values();                                         // re‑index collection

    /* 4️⃣  Safeguard: nothing to download */
    if ($students->isEmpty()) {
        return back()->with('error', 'No reappear admit cards found.');
    }

    /* 5️⃣  Generate the bulk PDF */
    return Pdf::loadView('pdf.reappear_admitcards', compact('students'))
              ->download('reappear_admitcards.pdf');      // change to →stream() if you prefer preview
}

public function downloadReappearSingle(Request $request)
{
    $request->validate([
        'nchm_roll_number' => 'required|exists:students,nchm_roll_number',
    ]);

    $student = Student::with([
        'program',
        'institute',
        'internalResults.course'
    ])
    ->where('nchm_roll_number', $request->nchm_roll_number)
    ->firstOrFail();

    // Get REAPPEAR courses (non-optional)
    $student->reappearCourses = $student->internalResults()
        ->where('status', 'REAPPEAR')
        ->with('course.students')
        ->get()
        ->filter(function ($result) use ($student) {
            return $result->course &&
                   $result->course->students
                        ->where('pivot.student_id', $student->id)
                        ->where('pivot.is_optional', false)
                        ->isNotEmpty();
        })
        ->map(fn ($result) => $result->course->course_code ?? 'N/A')
        ->unique()
        ->values();

    if ($student->reappearCourses->isEmpty()) {
        return redirect()->back()->with('error', 'No reappear subjects found for this roll number.');
    }

    return PDF::loadView('pdf.reappear_admitcard', compact('student'))
              ->download("reappear_admitcard_{$student->nchm_roll_number}.pdf");
}
}
