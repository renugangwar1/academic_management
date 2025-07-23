<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Program;
use App\Models\Institute;
use App\Models\StudentUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        return view('admin.dashboard', compact(
            'studentCount',
            'programCount',
            'instituteCount',
            'recentUploads'
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
