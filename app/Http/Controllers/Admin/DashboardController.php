<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Program;
use App\Models\Institute;
use App\Models\StudentUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Message;
use App\Models\AcademicSession;
use Illuminate\Support\Facades\Log;


class DashboardController extends Controller
{
    /**
     * Show the admin dashboard with counts and recent uploads.
     */
    public function index()
    {
        $studentCount = Student::count();
        $programCount = Program::count();
        $instituteCount = Institute::count();

        $recentUploads = StudentUpload::with('institute')
            ->latest()
            ->take(5)
            ->get();
             $latestUnreadMessage = Message::where('is_read', false)
                                ->latest()
                                ->with('institute')
                                ->first();

        return view('admin.dashboard', compact(
            'studentCount',
            'programCount',
            'instituteCount',
            'recentUploads',
            'latestUnreadMessage'
        ));
    }

    /**
     * Download a student upload file.
     */
    public function download(StudentUpload $upload)
    {
        $path = 'uploads/' . $upload->filename;

        if (!Storage::disk('local')->exists($path)) {
            return back()->with('error', 'File not found.');
        }

        return Storage::disk('local')->download($path);
    }

    /**
     * Approve the student upload.
     */
    public function approve(StudentUpload $upload)
    {
        $upload->status = 'approved';
        $upload->save();

        return back()->with('success', 'Upload approved.');
    }


// public function approve(StudentUpload $upload)
// {
//     Log::info('Approval process started.', ['upload_id' => $upload->id]);

//     // Step 1: Mark upload as approved
//     $upload->status = 'approved';
//     $upload->save();

//     // Step 2: Determine academic session
//     $year = $upload->year ?? $upload->year_level;  // Prioritize 'year'
//     $term = $upload->term;

//     Log::info('Checking academic session data', [
//         'upload_id' => $upload->id,
//         'year' => $year,
//         'year_level' => $upload->year_level,
//         'term' => $term,
//     ]);

//     if (empty($year) || empty($term)) {
//         Log::error('Missing year or term in upload', [
//             'upload_id' => $upload->id,
//             'year' => $year,
//             'term' => $term
//         ]);
//         return back()->with('error', 'Academic session details are missing from the upload.');
//     }

//     // Step 3: Create/find academic session
//     try {
//         $session = AcademicSession::firstOrCreate(
//             ['year' => $year, 'term' => $term],
//             ['odd_even' => strtolower($term) === 'july' ? 'odd' : 'even']
//         );
//     } catch (\Exception $e) {
//         Log::error('Could not create session.', ['error' => $e->getMessage()]);
//         return back()->with('error', 'Failed to find or create session.');
//     }

//     // Step 4: Read Excel & Create Students
//     try {
//         $filePath = storage_path("app/{$upload->file_path}");

//         if (!file_exists($filePath)) {
//             Log::error('File not found', ['path' => $filePath]);
//             return back()->with('error', 'Uploaded file is missing from storage.');
//         }

//         $rows = Excel::toCollection(null, $filePath)->first();

//         if ($rows->isEmpty()) {
//             Log::warning('Excel file is empty.', ['file' => $filePath]);
//             return back()->with('error', 'Excel file contains no student data.');
//         }

//         $createdCount = 0;
//         $skippedCount = 0;

//         foreach ($rows as $index => $row) {
//        $data = array_change_key_case($row->toArray(), CASE_LOWER);


//             $validator = Validator::make($data, [
//                 'name'              => 'required',
//                 'nchm_roll_number'  => 'required|unique:students,nchm_roll_number',
//                 'program_id'        => 'required|exists:programs,id',
//                 'institute_id'      => 'required|exists:institutes,id',
//                 'semester'          => 'nullable|integer',
//                 'year'              => 'nullable|integer',
//             ]);

//             if ($validator->fails()) {
//                 Log::warning('Validation failed for row', [
//                     'row' => $index + 1,
//                     'data' => $data,
//                     'errors' => $validator->errors()->all(),
//                 ]);
//                 $skippedCount++;
//                 continue;
//             }

//             $student = Student::create([
//                 'name'                          => $data['name'],
//                 'nchm_roll_number'              => $data['nchm_roll_number'],
//                 'enrolment_number'              => $data['enrolment_number'] ?? null,
//                 'program_id'                    => $data['program_id'],
//                 'institute_id'                  => $data['institute_id'],
//                 'semester'                      => $data['semester'] ?? null,
//                 'year'                          => $data['year'] ?? $year,
//                 'academic_session_id'           => $session->id,
//                 'original_academic_session_id'  => $session->id,
//                 'email'                         => $data['email'] ?? null,
//                 'mobile'                        => $data['mobile'] ?? null,
//                 'date_of_birth'                 => $data['date_of_birth'] ?? null,
//                 'category'                      => $data['category'] ?? null,
//                 'father_name'                   => $data['father_name'] ?? null,
//                 'status'                        => true,
//             ]);

//             \App\Models\StudentSessionHistory::create([
//                 'student_id'          => $student->id,
//                 'academic_session_id' => $student->academic_session_id,
//                 'program_id'          => $student->program_id,
//                 'institute_id'        => $student->institute_id,
//                 'from_semester'       => null,
//                 'to_semester'         => $student->semester,
//                 'semester'            => $student->semester,
//                 'promotion_type'      => 'initial',
//                 'promoted_by'         => auth()->id(),
//                 'promoted_at'         => now(),
//             ]);

//             $createdCount++;
//         }

//         Log::info("Student import completed: {$createdCount} added, {$skippedCount} skipped.");

//     } catch (\Exception $e) {
//         Log::error('Excel import failed.', ['error' => $e->getMessage()]);
//         return back()->with('error', 'Error importing Excel data: ' . $e->getMessage());
//     }

//     return back()->with('success', "Upload approved. {$createdCount} students imported. {$skippedCount} rows skipped.");
// }

    /**
     * Reject the student upload.
     */
    public function reject(StudentUpload $upload)
    {
        $upload->status = 'rejected';
        $upload->save();

        return back()->with('success', 'Upload rejected.');
    }
}
