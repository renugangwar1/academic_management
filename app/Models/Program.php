<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'duration',
        'duration_unit',
        'structure',
    ];

// public function courses()
// {
//     return $this->hasMany(Course::class);
// }

 public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_program')
                    ->withPivot('semester','year')
                    ->withTimestamps();
    }


public function students()
{
    return $this->hasMany(Student::class);
}
public function institutes()
{
    return $this->belongsToMany(Institute::class, 'institute_program');
}




public function academicSessions()
{
    return $this->belongsToMany(AcademicSession::class, 'academic_session_program')
                ->withPivot(['structure', 'start_level'])
                ->withTimestamps();
}


}
