<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Student; 
use App\Models\Institute; 
use App\Exports\StudentsTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;
use App\Exports\AssignedCoursesTemplateExport;
use App\Imports\AssignedCoursesImport;
use App\Exports\ProgramStudentsExport;
use Illuminate\Pagination\LengthAwarePaginator;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::orderBy('id', 'desc')->paginate(10);
        return view('admin.programs.index', compact('programs'));
    }

    public function create()
    {
        return view('admin.programs.create');
    }

   public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'duration' => 'required|integer|min:1',
        'duration_unit' => 'required|in:year,month,day',
        'structure' => 'required|in:semester,yearly,short_term',
    ]);

    Program::create($validated);

    return redirect()->route('admin.programs.index')->with('success', 'Program created successfully.');
}

    public function edit($id)
    {
        $program = Program::findOrFail($id);
        return view('admin.programs.edit', compact('program'));
    }

   public function update(Request $request, $id)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'duration' => 'required|integer|min:1',
        'duration_unit' => 'required|in:year,month,day',
        'structure' => 'required|in:semester,yearly,short_term',
    ]);

    $program = Program::findOrFail($id);
    $program->update($validated);

    return redirect()->route('admin.programs.index')->with('success', 'Program updated successfully.');
}


    public function destroy($id)
    {
        $program = Program::findOrFail($id);
        $program->delete();

        return redirect()->route('admin.programs.index')->with('success', 'Program deleted successfully.');
    }



 public function settings($id)
{
     $program = Program::with(['courses', 'institutes'])->findOrFail($id);
    $students = Student::where('program_id', $id)->get();

    return view('admin.programs.settings', compact('program', 'students'));
}
public function viewInfo($id)
{
    $program = Program::findOrFail($id);
    return view('admin.programs.view_info', compact('program'));
}



public function viewCourses($id)
{
    $program = Program::with('courses')->findOrFail($id);

    $coursesBySemester = $program->courses
        ->groupBy(function ($course) {
            return $course->pivot->semester ?? 'N/A';
        })
        ->sortKeys();

    return view('admin.programs.view_courses', compact('program', 'coursesBySemester'));
}



public function viewStudents(Request $request, $id)
{
    $program = Program::findOrFail($id);

    $selectedSemester = $request->input('semester') === 'all' ? null : (int) $request->input('semester');

    $query = Student::with(['courses', 'institute'])
        ->where('program_id', $id);

    // Apply semester filter if present
    if ($selectedSemester) {
        $query->where('semester', $selectedSemester);
    }

    $students = $query->paginate(10)->withQueryString(); // retain filters in pagination

    // Get distinct semesters to populate dropdown
    $availableSemesters = Student::where('program_id', $id)
                                ->distinct()
                                ->pluck('semester')
                                ->sort();

    // Get courses mapped to this program & semester
    $mappedCoursesQuery = $program->courses();

    if ($selectedSemester) {
        $mappedCoursesQuery->wherePivot('semester', $selectedSemester);
    }

    $mappedCourses = $mappedCoursesQuery->get();

    return view('admin.programs.view_students', compact(
        'program', 'students', 'availableSemesters', 'selectedSemester', 'mappedCourses'
    ));
}



// public function viewStudents($id)
// {
//     $program = Program::findOrFail($id);
//     $students = \App\Models\Student::where('program_id', $id)->get();
//     return view('admin.programs.view_students', compact('program', 'students'));
// }

public function viewInstitutes($programId)
{
    $program = Program::findOrFail($programId);
    $allInstitutes = Institute::all();
    $mappedInstitutes = $program->institutes()->get();

    return view('admin.programs.institutes', compact('program', 'allInstitutes', 'mappedInstitutes'));
}


public function showImportForm($programId)
{
    $program = Program::findOrFail($programId);
    return view('admin.programs.import_students', compact('program'));
}
public function importStudents(Request $request, $programId)
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls,csv',
    ]);

    $program = Program::findOrFail($programId);
    $import  = new StudentsImport($program->id, $program->structure);

    Excel::import($import, $request->file('file'));

    // If you still need raw debug, uncomment:
    // dd($import->failures()->toArray());

    return back()->with([
        'success'  => "{$import->importedCount()} students imported.",
        'failures' => $import->failures()->isNotEmpty() ? $import->failures() : null,
    ]);
}


public function downloadStudentTemplate($id)
{
    $program = \App\Models\Program::findOrFail($id);
    $filename = 'students_template_' . $program->id . '.xlsx';

    return Excel::download(new StudentsTemplateExport($program), $filename);
}


public function showInstitutes(Program $program)
{
    $allInstitutes = Institute::all(); // Get all institutes for the dropdown

    // Load institutes currently mapped with program
    $mappedInstitutes = $program->institutes()->get();

    return view('admin.programs.institutes', compact('program', 'allInstitutes', 'mappedInstitutes'));
}

public function updateInstitutes(Request $request, Program $program)
{
    // Validate the input — institutes array is optional but if present must be array of integers
    $validated = $request->validate([
        'institutes' => 'nullable|array',
        'institutes.*' => 'integer|exists:institutes,id',
    ]);

    // Sync the selected institutes (or empty array if none selected)
    $program->institutes()->sync($validated['institutes'] ?? []);

    return redirect()->route('admin.programs.institutes', $program->id)
                     ->with('success', 'Institutes mapping updated successfully.');
}


// public function assignCourses($programId)
// {
//     $program = Program::with(['courses'])->findOrFail($programId);

//     // Get all students of this program grouped by semester
//     $students = Student::where('program_id', $programId)
//         ->with('courses')
//         ->orderBy('semester')
//         ->get()
//         ->groupBy('semester');

//     return view('admin.programs.assign_courses', compact('program', 'students'));
// }
public function showAssignCoursesForm($id)
{
    $program = Program::with(['courses'])->findOrFail($id);

    // Group students by semester
    $studentsRaw = Student::with(['courses'])
        ->where('program_id', $id)
        ->get()
        ->groupBy('semester');

    $students = [];

    foreach ($studentsRaw as $semester => $group) {
        $currentPage = request()->input("page_{$semester}", 1);
        $perPage = 10;
        $items = $group->forPage($currentPage, $perPage)->values();

       $students[$semester] = new LengthAwarePaginator(
    $items,
    $group->count(),
    $perPage,
    $currentPage,
    [
        'pageName' => "page_{$semester}",
        'path' => request()->url(), // important for correct links
        'query' => request()->query(), // keeps all other parameters
    ]
    );
    }

    return view('admin.programs.assign_courses', compact('program', 'students'));
}



public function saveAssignments(Request $request, $programId)
{
    $program = Program::with('courses')->findOrFail($programId);
    $allProgramCourseIds = $program->courses->pluck('id')->toArray();
    $courseAssignments = $request->input('courses', []);

    foreach ($courseAssignments as $studentId => $selectedCourseIds) {
        $student = Student::find($studentId);
        if (!$student) continue;

        // Clear previous assignments
        $student->courses()->detach();

        // Attach all courses with is_optional set properly
        foreach ($allProgramCourseIds as $courseId) {
            $isOptional = !in_array($courseId, $selectedCourseIds);
            $student->courses()->attach($courseId, ['is_optional' => $isOptional]);
        }
    }

    return back()->with('success', 'Courses assigned to students successfully.');
}




public function downloadAssignmentTemplate($id)
{
    $program = Program::findOrFail($id);
    $fileName = 'assign_courses_template_' . $program->name . '.xlsx';

    return Excel::download(new AssignedCoursesTemplateExport, $fileName);
}

public function importAssignments(Request $request, $id)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv'
    ]);

    Excel::import(new AssignedCoursesImport, $request->file('file'));

    return back()->with('success', 'Assigned courses imported successfully.');
}



public function exportStudents(Request $request, $id)
{
    $semester = $request->input('semester');

    return Excel::download(new ProgramStudentsExport($id, $semester), 'students_program_' . $id . '.xlsx');
}
}
