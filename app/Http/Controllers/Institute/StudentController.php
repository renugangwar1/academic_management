<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Program;
use App\Imports\StudentsImport;
use App\Exports\StudentsTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\StudentUpload;
use Illuminate\Support\Str;
use App\Exports\StudentsExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
class StudentController extends Controller
{
    /**
     * Show all students of the logged-in institute.
     */
    public function index()
    {
        $instituteId = Auth::id(); // default guard, since user is authenticated

        $uploads = StudentUpload::where('institute_id', $instituteId)
        ->orderByDesc('created_at')
        ->get();

       return view('institute.students.index', compact('uploads'));
    }

    /**
     * Show the student creation/import form with mapped programs.
     */
    public function create()
    {
        $user = Auth::user();

        $programs = Program::whereHas('institutes', function ($query) use ($user) {
            $query->where('institutes.id', $user->id);
        })->orderBy('name')->get();

       $instituteId = \DB::table('institutes')
    ->where('code', $user->id)
    ->value('id');

$uploads = StudentUpload::where('institute_id', $instituteId)
    ->latest()
    ->take(10)
    ->get();

        return view('institute.students.create', compact('programs', 'uploads'));

    }

    /**
     * Store a single new student.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'roll_no'  => 'required|string|max:20|unique:students,roll_no',
            'mobile'   => 'nullable|digits:10',
        ]);

        Student::create([
            'name'         => $request->name,
            'roll_no'      => $request->roll_no,
            'mobile'       => $request->mobile,
            'institute_id' => Auth::id(),
        ]);

        return redirect()->route('institute.students.index')
            ->with('success', 'Student added successfully.');
    }

    /**
     * Download Excel template for importing students (based on selected program).
     */
    public function downloadTemplate(Request $request)
    {
        $instituteId = Auth::id();
        $programId = $request->query('program_id');

        if (!$programId) {
            return redirect()->back()->with('error', 'Program ID is missing.');
        }

        $program = Program::whereHas('institutes', function ($query) use ($instituteId) {
            $query->where('institutes.id', $instituteId);
        })->find($programId);

        if (!$program) {
            return redirect()->back()->with('error', 'Program not found or not linked to your institute.');
        }

        return Excel::download(new StudentsTemplateExport($program), 'students_template.xlsx');
    }

    /**
     * Import students from uploaded Excel file.
     */

public function import(Request $request)
{
    $request->validate([
        'student_excel' => 'required|file|mimes:xlsx,xls',
    ]);

    try {
        $instituteId = Auth::id();

        // Get institute code (optional)
        $instituteCode = \DB::table('institutes')
            ->where('id', $instituteId)
            ->value('code');

        if (!$instituteCode) {
            return redirect()->back()->with('error', 'Institute code not found.');
        }

        $file = $request->file('student_excel');

        // Generate a unique filename
        $dateTime = now()->format('Ymd_His');
        $filename = "{$instituteCode}_students_list_{$dateTime}." . $file->getClientOriginalExtension();

        // Store file in storage/app/uploads
        $path = $file->storeAs('uploads', $filename);

        // Store file info in uploads table (but do NOT import students yet)
        StudentUpload::create([
            'institute_id' => $instituteId,
            'filename'     => $filename,
            'file_path'    => $path,
            'status'       => 'pending', // will change to 'approved' when admin imports
        ]);

        return redirect()->route('institute.students.index')
            ->with('success', 'File uploaded successfully. Awaiting admin approval.');
    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Upload failed: ' . $e->getMessage());
    }
}





public function showEnrolledStudents(Request $request)
{
    $instituteId = Auth::id();

    // Log incoming filters
    Log::info('🟡 Filter Request:', $request->only(['search']));

    $query = Student::query()
        ->with('program')
        ->where('institute_id', $instituteId);

    // Simple global search (e.g., name or roll number)
    if ($request->filled('search')) {
        $searchTerm = $request->input('search');
        $query->where(function ($q) use ($searchTerm) {
            $q->where('name', 'like', "%$searchTerm%")
              ->orWhere('nchm_roll_number', 'like', "%$searchTerm%");
        });
    }

    // Log SQL debug info
    Log::info('🟢 Final SQL Query:', [$query->toSql()]);
    Log::info('🟢 Query Bindings:', $query->getBindings());

 $students = $query->paginate(15)->withQueryString();

    return view('institute.students.list', compact('students'));
}


public function export()
{
     $instituteId = Auth::id();

    return Excel::download(new StudentsExport($instituteId), 'enrolled_students.xlsx');
}
}
