<?php
// app/Http/Controllers/Admin/CourseController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Program;
  use App\Imports\CoursesImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CourseTemplateExport;
use Illuminate\Support\Facades\DB;
use App\Models\CourseComponent;


class CourseController extends Controller
{
    public function index()
    {
      $courses = Course::with('programs')->orderBy('id', 'desc')->paginate(10);

        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        $programs = Program::all();
        return view('admin.courses.create', compact('programs'));
    }

 
public function store(Request $request)
{
    $request->validate([
        'course_code'   => 'required|string|unique:courses,course_code',
        'course_title'  => 'required|string',
        'type'          => 'required|in:Theory,Practical',
        'credit_hours'  => 'required|integer',
        'credit_value'  => 'required|numeric',
    ]);

    $data = $request->only([
        'course_code','course_title','type','credit_hours','credit_value'
    ]);

    $data['has_attendance'] = $request->boolean('has_attendance');
    $data['has_internal']   = $request->boolean('has_internal');
    $data['has_external']   = $request->boolean('has_external');

    Course::create($data);

    return redirect()
        ->route('admin.courses.index')
        ->with('success', 'Course created successfully.');
}



    public function edit(Course $course)
    {
        $programs = Program::all();
        return view('admin.courses.edit', compact('course', 'programs'));
    }

   public function update(Request $request, Course $course)
{
    $request->validate([
        'course_code' => 'required|string|unique:courses,course_code,' . $course->id,
        'course_title' => 'required|string',
        'type' => 'required|in:Theory,Practical',
        'credit_hours' => 'required|integer',
        'credit_value' => 'required|numeric',
        'program_ids' => 'required|array',
        'program_ids.*.program_id' => 'required|exists:programs,id',
        'program_ids.*.semester' => 'required|integer',
    ]);

    $courseData = $request->only([
        'course_code',
        'course_title',
        'type',
        'credit_hours',
        'credit_value',
        'has_attendance',
        'has_internal',
        'has_external',
    ]);

    // Handle checkboxes
    $courseData['has_attendance'] = $request->has('has_attendance');
    $courseData['has_internal'] = $request->has('has_internal');
    $courseData['has_external'] = $request->has('has_external');

    $course->update($courseData);

    // Sync the pivot table
    $syncData = [];
    foreach ($request->program_ids as $map) {
        $syncData[$map['program_id']] = ['semester' => $map['semester']];
    }

    $course->programs()->sync($syncData);

    return redirect()->route('admin.courses.index')->with('success', 'Course updated successfully.');
}


public function showMappingForm(Request $request, $course_id = null)
{
    $allPrograms = Program::select('id', 'name', 'structure')->get();

    // If program_id is passed, filter it
    if ($request->has('program_id')) {
        $programs = $allPrograms->where('id', $request->program_id);
    } else {
        $programs = $allPrograms;
    }

    if ($course_id) {
        $selectedCourse = Course::findOrFail($course_id);
        return view('admin.courses.mapping', compact('selectedCourse', 'programs'));
    }

    $courses = Course::all();
    return view('admin.courses.mapping', compact('courses', 'programs'));
}

public function storeMapping(Request $request)
{
    $request->validate([
        'course_ids'   => 'required|array',
        'course_ids.*' => 'exists:courses,id',
        'program_id'   => 'required|exists:programs,id',
    ]);

    $program = Program::findOrFail($request->program_id);

    // Validate semester/year based on structure
    if ($program->structure === 'semester') {
        $request->validate([
            'semester' => 'required|numeric|min:1|max:10',
        ]);
    } elseif ($program->structure === 'yearly') {
        $request->validate([
            'year' => 'required|numeric|min:1|max:6',
        ]);
    }

    foreach ($request->course_ids as $courseId) {
        $mappingData = [];

        if ($program->structure === 'semester') {
            $mappingData['semester'] = $request->semester;
            $mappingData['year'] = null;
        } elseif ($program->structure === 'yearly') {
            $mappingData['year'] = $request->year;
            $mappingData['semester'] = null;
        }

        DB::table('course_program')->updateOrInsert(
    [
        'course_id' => $courseId,
        'program_id' => $program->id,
        'semester' => $mappingData['semester'],
        'year' => $mappingData['year'],
    ],
    []
);
    }

    return redirect()->route('admin.courses.index')->with('success', 'Courses mapped successfully!');
}




    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('admin.courses.index')->with('success', 'Course deleted successfully.');
    }




  

public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv',
        'program_id' => 'required|exists:programs,id',
    ]);

    Excel::import(new CoursesImport($request->program_id), $request->file('file'));

    return redirect()->route('admin.courses.index')->with('success', 'Courses imported successfully.');
}


public function downloadTemplate($programId)
{
    $program = Program::findOrFail($programId);
    $filename = 'Course_Template_' . $program->name . '.xlsx';

    return Excel::download(new CourseTemplateExport($program), $filename);
}


public function bulkMapForm()
{
    $courses = Course::with('programs')->get();
    $programs = Program::all();

    return view('admin.courses.bulk-map', compact('courses', 'programs'));
}

public function bulkMapStore(Request $request)
{
    $request->validate([
        'program_id' => 'required|exists:programs,id',
        'courses' => 'required|array',
        'courses.*' => 'exists:courses,id',
    ]);

    $program = Program::findOrFail($request->program_id);

    // Validate semester/year based on structure
    if ($program->structure === 'semester') {
        $request->validate([
            'semester' => 'required|integer|min:1|max:10',
        ]);
    } elseif ($program->structure === 'yearly') {
        $request->validate([
            'year' => 'required|integer|min:1|max:6',
        ]);
    }

    $courseIds = $request->input('courses');

    foreach ($courseIds as $courseId) {
        $mappingData = [];

        if ($program->structure === 'semester') {
            $mappingData['semester'] = $request->semester;
            $mappingData['year'] = null;
        } elseif ($program->structure === 'yearly') {
            $mappingData['year'] = $request->year;
            $mappingData['semester'] = null;
        }

      DB::table('course_program')->updateOrInsert(
    [
        'course_id' => $courseId,
        'program_id' => $program->id,
        'semester' => $mappingData['semester'],
        'year' => $mappingData['year'],
    ],
    []
);
    }

    return redirect()->route('admin.courses.index')->with('success', 'Courses mapped successfully.');
}



public function showComponentForm()
{
    $courses = Course::with('programs')->paginate(10); // Or fetch all if needed

    return view('admin.courses.components', compact('courses'));
}


public function addComponent($id)
{
    $course = Course::findOrFail($id);
    return view('admin.courses.add-component', compact('course'));
}

public function saveComponent(Request $request, $id)
{
    $course = Course::findOrFail($id);

    $data = [
        'course_id'          => $course->id,
        'total_from'         => $request->input('total_from'),
        'total_marks'        => $request->input('total_marks'),
        'min_passing_marks'  => $request->input('min_passing_marks'),
    ];

    if ($course->has_internal) {
        $data['internal_max'] = $request->input('internal_max');
        $data['internal_min'] = $request->input('internal_min');
    }

    if ($course->has_external) {
        $data['external_max'] = $request->input('external_max');
        $data['external_min'] = $request->input('external_min');
    }

    if ($course->has_attendance) {
        $data['attendance_max'] = $request->input('attendance_max');
        $data['attendance_min'] = $request->input('attendance_min');
    }

    CourseComponent::updateOrCreate(['course_id' => $course->id], $data);

    return redirect()->route('admin.courses.components')->with('success', 'Component saved successfully.');
}

public function viewComponent($id)
{
    $course = Course::findOrFail($id);
    $component = $course->component; // Assuming relationship exists

    return view('admin.courses.view-component', compact('course', 'component'));
}


public function copyComponent(Request $request, $sourceCourse)
{
    $request->validate([
        'target_courses' => 'required|array',
        'target_courses.*' => 'exists:courses,id',
    ]);

    $source = CourseComponent::where('course_id', $sourceCourse)->first();
    if (!$source) {
        return back()->with('error', 'Source component not found.');
    }

    foreach ($request->target_courses as $targetId) {
        CourseComponent::updateOrCreate(
            ['course_id' => $targetId],
            [
                'internal_max'        => $source->internal_max,
                'internal_min'        => $source->internal_min,
                'external_max'        => $source->external_max,
                'external_min'        => $source->external_min,
                'attendance_max'      => $source->attendance_max,
                'attendance_min'      => $source->attendance_min,
                'total_from'          => $source->total_from,
                'total_marks'         => $source->total_marks,
                'min_passing_marks'   => $source->min_passing_marks,
            ]
        );
    }

    return redirect()->route('admin.courses.components')->with('success', 'Component copied successfully.');
}

}
