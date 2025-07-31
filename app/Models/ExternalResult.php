<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



    class ExternalResult extends Model
{
  use HasFactory;

    /** @var string[] */
    protected $fillable = [
        'student_id',
        'program_id',
        'course_id',
        'semester',
          'academic_session_id',
        'internal',
        'external',
        'attendance',
        'total',
        'credit',
        'grade_point',
    // new columns
    'sgpa',
    'cgpa',
    'grade_letter',
    'result_status',
    'exam_attempt',

    // for external marks block
   
    
    
];


    /**
     * Cast numeric columns to float / int automatically.
     */
    protected $casts = [
        'internal'      => 'integer',
        'external'      => 'integer',
        'total'         => 'integer',
        'credit'        => 'integer',
        'semester'      => 'integer',
        'exam_attempt'  => 'integer',

        'grade_point'   => 'float',
        'sgpa'          => 'float',
        'cgpa'          => 'float',
    ];

    /* -----------------------------------------------------------------
     | Relationships
     |------------------------------------------------------------------
     */

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
public function academicSession()
{
    return $this->belongsTo(AcademicSession::class, 'academic_session_id');
}

public static function calculateGradePoint($total): float
{
    return match (true) {
        $total >= 95 => 9,
        $total >= 85 => 8,
        $total >= 75 => 7,
        $total >= 65 => 6,
        $total >= 55 => 5,
        $total >= 45 => 4,
        $total >= 35 => 3,
        $total >= 25 => 2,
        $total >= 15 => 1,
        default      => 0,
    };
}

public static function calculateGradeLetter($total)
{
    if ($total >= 85) return 'A+';
    if ($total >= 75) return 'A';
    if ($total >= 65) return 'B+';
    if ($total >= 55) return 'B';
    if ($total >= 45) return 'C';
    if ($total >= 33) return 'D';
    return 'F';
}

}