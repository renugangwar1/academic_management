<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Mark;
use App\Traits\HasSessionHistory;
class Student extends Model
{
    use HasFactory;
 protected $fillable = [
    'name','nchm_roll_number','enrolment_number',
    'program_id','institute_id',
    'semester','year',
    'academic_session_id',         
    'email','mobile','date_of_birth',
    'category','father_name','status','original_academic_session_id',
];

    protected $casts = [
        'semester' => 'integer',
        'year'     => 'integer',
        'status'   => 'boolean',
        'date_of_birth' => 'date',
    ];


public function academicSession()
{
    return $this->belongsTo(AcademicSession::class);
}

   public function program()
{
    return $this->belongsTo(Program::class);
}

public function institute()
{
    return $this->belongsTo(Institute::class);
}

public function marks() {
    return $this->hasMany(Mark::class);
}

public function courses()
{
    return $this->belongsToMany(Course::class, 'course_student')
        ->withPivot('is_optional')            
    ->withTimestamps();
            
}


 public function internalResults()
    {
        return $this->hasMany(InternalResult::class);
    }

   
 public function passedCourses()
{
    return $this->belongsToMany(Course::class, 'course_student')
        ->withPivot('is_optional')
        ->wherePivot('is_optional', false)          // exclude optional subjects
        ->whereExists(function ($q) {
            $q->select(DB::raw(1))
              ->from('internal_results')
              ->whereColumn('internal_results.course_id',  'courses.id')
              ->whereColumn('internal_results.student_id', 'course_student.student_id')
              ->where('internal_results.status', 'PASS');
        });
}




public function appearingCourses()
{
    return $this->belongsToMany(Course::class, 'course_student')
                ->withPivot('is_optional')
                ->wherePivot('is_optional', false);
}

public function optionalCourses()
{
    return $this->belongsToMany(Course::class, 'course_student')
                ->withPivot('is_optional')
                ->wherePivot('is_optional', true);
}

public function passedAppearingCourses()
{
    return $this->belongsToMany(Course::class, 'course_student')
        ->withPivot('is_optional')
        ->wherePivot('is_optional', false) // exclude optional
        ->whereHas('internalResults', function ($query) {
            $query->where('student_id', $this->id)
                  ->where('status', 'PASS');
        });
}

// public function getPassedAppearingCourses()
// {
//     return $this->appearingCourses()
//         ->whereHas('internalResults', function ($query) {
//             $query->where('student_id', $this->id)
//                   ->where('status', 'PASS');
//         })->get();
// }
public function getPassedAppearingCourses($semester = null)
{
    $query = $this->appearingCourses()
        ->whereHas('internalResults', function ($query) {
            $query->where('student_id', $this->id)
                  ->where('status', 'PASS');
        });

    if ($semester !== null) {
        // Filter courses by semester
        $query->whereHas('programs', function ($q) use ($semester) {
            $q->where('course_program.semester', $semester);
        });
    } else {
        // If no semester passed, maybe return empty or handle explicitly
        return collect();
    }

    return $query->get();
}

public function promotions()
{
    return $this->hasMany(Promotion::class);
}
public function scopeWithPassedResults($query, $semester)
{
    return $query->whereHas('externalResults', function ($q) use ($semester) {
        $q->where('semester', $semester)
          ->whereRaw('LOWER(TRIM(result_status)) = ?', ['pass']);
    });
}

public function externalResults()
{
    return $this->hasMany(\App\Models\ExternalResult::class, 'student_id');
}

public function scopePassed($query, $semester)
{
    return $query->whereHas('externalResults', function ($q) use ($semester) {
        $q->where('semester', $semester)
          ->whereRaw('LOWER(TRIM(result_status)) = ?', ['pass']);
    });
}

public function scopeFailed($query, $semester)
{
    return $query->whereHas('externalResults', function ($q) use ($semester) {
        $q->where('semester', $semester)
          ->whereRaw('LOWER(TRIM(result_status)) != ?', ['pass']);
    });
}

public function reappearCourses()
{
    return $this->hasMany(InternalResult::class)
                ->where('status', 'REAPPEAR');
}

public function sessionHistories()
{
    return $this->hasMany(StudentSessionHistory::class);
}

}
