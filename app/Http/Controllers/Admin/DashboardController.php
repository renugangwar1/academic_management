<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Program;
use App\Models\Institute;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'studentCount' => Student::count(),
            'programCount' => Program::count(),
            'instituteCount' => Institute::count()
        ]);
    }
}
