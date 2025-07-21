<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Student, Program, Institute, AcademicSession};
use Illuminate\Http\Request;
use App\Imports\StudentsImport;
use App\Exports\StudentsTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use App\Exports\FilteredStudentsExport;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);

        $students = Student::with(['program', 'institute', 'academicSession'])
            ->when($request->program_id, fn($q) => $q->where('program_id', $request->program_id))
            ->when($request->academic_session_id, fn($q) => $q->where('academic_session_id', $request->academic_session_id))
            ->when($request->semester, fn($q) => $q->where('semester', $request->semester))
            ->when($request->year, fn($q) => $q->where('year', $request->year))
            ->when($request->search, function ($q) use ($request) {
                $s = $request->search;
                $q->where(fn($x) => $x->where('name', 'like', "%$s%")
                    ->orWhere('nchm_roll_number', 'like', "%$s%")
                    ->orWhere('enrolment_number', 'like', "%$s%")
                    ->orWhere('email', 'like', "%$s%")
                    ->orWhere('mobile', 'like', "%$s%"));
            })
            ->paginate($perPage)
            ->appends($request->all());

        return view('admin.students.index', compact('students', 'perPage'));
    }

    public function create()
    {
        return view('admin.students.create', [
            'programs'   => Program::all(),
            'institutes' => Institute::all(),
        ]);
    }

    public function store(Request $request)
    {
        $program = Program::findOrFail($request->program_id);
        $structure = $program->structure;

        $rules = $this->getValidationRules($structure);
        $validated = $request->validate($rules);
        $validated['status'] = $request->boolean('status');

        // Normalize semester/year values
        $validated = $this->normalizeLevelFields($validated);

        // Resolve or create academic session
        $session = AcademicSession::firstOrCreate(
            ['year' => $request->year, 'term' => $request->term],
            ['odd_even' => $request->term === 'July' ? 'odd' : 'even']
        );
        $validated['academic_session_id'] = $session->id;
        unset($validated['year'], $validated['term']);

        Student::create($validated);

        return redirect()->route('admin.students.index')->with('success', 'Student added successfully.');
    }

    public function edit(Student $student)
    {
        return view('admin.students.edit', [
            'student'    => $student,
            'programs'   => Program::all(),
            'institutes' => Institute::all(),
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $program = Program::findOrFail($request->program_id);
        $structure = $program->structure;

        $rules = $this->getValidationRules($structure);
        $validated = $request->validate($rules);
        $validated['status'] = $request->boolean('status');

        $validated = $this->normalizeLevelFields($validated);

        $session = AcademicSession::firstOrCreate(
            ['year' => $request->year, 'term' => $request->term],
            ['odd_even' => $request->term === 'July' ? 'odd' : 'even']
        );
        $validated['academic_session_id'] = $session->id;
        unset($validated['year'], $validated['term']);

        $student->update($validated);

        return redirect()->route('admin.students.index')->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        try {
            $student->delete();
            return redirect()->route('admin.students.index')->with('success', 'Student deleted successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to delete student: ' . $e->getMessage());
        }
    }

    public function downloadTemplate(Program $program)
    {
        $filename = Str::slug($program->name) . '_students_template.xlsx';
        return Excel::download(new StudentsTemplateExport($program), $filename);
    }

  public function import(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls,csv',
    ]);

    $import = new StudentsImport;   // ← only if your constructor takes no args

    try {
        Excel::import($import, $request->file('file'));
        // Or queueImport(...) if you prefer background jobs.

        return back()->with([
            'success'  => "{$import->importedCount()} students imported.",
            'failures' => $import->failures()->isNotEmpty() ? $import->failures() : null,
        ]);

    } catch (\Throwable $e) {
        report($e);
        return back()->with('error', 'Import failed: '.$e->getMessage());
    }
}


    private function getValidationRules(string $structure): array
    {
        $rules = [
            'name'             => 'required|string',
            'nchm_roll_number' => ['nullable', 'regex:/^\d{10}$/'],
            'enrolment_number' => 'nullable|string',
            'email'            => 'nullable|email',
            'mobile'           => 'nullable|string|max:15',
            'date_of_birth'    => 'nullable|date',
            'category'         => 'nullable|string',
            'father_name'      => 'nullable|string',
            'program_id'       => 'required|exists:programs,id',
            'institute_id'     => 'required|exists:institutes,id',
            'year'             => 'required|string',
            'term'             => 'required|in:Jan,July',
            'status'           => 'nullable|boolean',
        ];

        if (in_array($structure, ['yearly', 'year_wise'])) {
            $rules['year_level'] = 'required|integer|min:1|max:4';
        } else {
            $rules['semester'] = 'required|integer|min:1|max:8';
        }

        return $rules;
    }

    private function normalizeLevelFields(array $data): array
    {
        if (!empty($data['year_level']) && is_numeric($data['year_level'])) {
            $data['semester'] = null;
        } elseif (!empty($data['semester']) && is_numeric($data['semester'])) {
            $data['year_level'] = null;
        }

        return $data;
    }

    public function export(Request $request)
    {
        return Excel::download(new FilteredStudentsExport($request), 'filtered_students.xlsx');
    }
}
