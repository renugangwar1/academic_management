<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExaminationController extends Controller
{
    public function overview()
    {
         $user = auth()->user();
        return view('institute.examinations');
    }

    public function index()
    {
         $user = auth()->user();
        // Later: Show exam schedules, upload marks, etc.
        return view('institute.examination.index');
    }
}
