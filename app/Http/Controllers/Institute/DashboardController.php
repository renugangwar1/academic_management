<?php
namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller; // ✅ THIS LINE IS MISSING
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Program;
use App\Models\Student;
use App\Models\AcademicSession;
class DashboardController extends Controller
{
    // public function index()
    // {
    //     $user = Auth::user();

    //     // Pass user to the view
    //     return view('institute.dashboard', compact('user'));
    // }

    public function index()
{
         $user = Auth::user();
        $instituteId = $user->id;

        // Count students for the institute
        $studentCount = Student::where('institute_id', $instituteId)->count();

        // Count programs mapped to this institute
        $programCount = Program::whereHas('institutes', function ($query) use ($instituteId) {
            $query->where('institutes.id', $instituteId);
        })->count();

        // Count active sessions (if sessions are global)
        $sessionCount = AcademicSession::where('active', true)->count(); // or filter per-institute if needed

        return view('institute.dashboard', compact(
            'user',
            'studentCount',
            'programCount',
            'sessionCount'
        ));
    }

public function students()
{
    return view('institute.students');
}

public function examinations()
{
    return view('institute.examinations');
}

public function reappears()
{
    return view('institute.reappears');
}

}
