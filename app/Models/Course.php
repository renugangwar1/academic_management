<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_code',
        'course_title',
        'type',
        'credit_hours',
        'credit_value',
        'has_attendance',
        'has_internal',
        'has_external',
    ];

  public function programs()
    {
        return $this->belongsToMany(Program::class, 'course_program')
                    ->withPivot('semester','year')
                    ->withTimestamps();
    }

   
    public function students()
    {
        return $this->belongsToMany(Student::class, 'course_student')
        ->withPivot('is_optional')            
        ->withTimestamps();
    }

    public function component()
{
    return $this->hasOne(CourseComponent::class);
}

public function internalResults()
{
    return $this->hasMany(InternalResult::class);
}

public function externalResults()
{
    return $this->hasMany(ExternalResult::class);
}

}
