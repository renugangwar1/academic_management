<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mark extends Model
{
    use HasFactory;

    protected $fillable = [
         'student_id',
        'course_id',
        'session_id',  
        'semester',    
        'year',         
        'internal',
        'external',
        'attendance',
        'total',
    ];

      protected $casts = [
        'internal'   => 'float',
        'external'   => 'float',
        'attendance' => 'float',
        'total'      => 'float',
    ];
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
