<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseComponent extends Model
{
    use HasFactory;

   protected $fillable = [
    'course_id',
    'internal_max',
    'internal_min',
    'external_max',
    'external_min',
    'attendance_max',
    'attendance_min',
    'total_from',
    'total_marks',
    'min_passing_marks',
];


    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
