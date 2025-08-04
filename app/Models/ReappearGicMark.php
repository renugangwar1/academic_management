<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReappearGicMark extends Model
{
    use HasFactory;
    protected $fillable = [
    'student_id',
    'roll_number',
    'student_name',
    'academic_session_id',
    'program_id',
    'institute_id',
    'semester',
    'year',
    'course_code',
    'course_name',
    'reappear_type',
    'gic_marks',
];
public function institute()
{
    return $this->belongsTo(Institute::class, 'institute_id');
}

public function program()
{
    return $this->belongsTo(Program::class, 'program_id');
}
public function student()
{
    return $this->belongsTo(Student::class);
}

}
