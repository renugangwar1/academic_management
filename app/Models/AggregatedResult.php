<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AggregatedResult extends Model
{
    protected $fillable = [
        'student_id',
        'program_id',
        'semester',
        'year',
        'internal_marks',
        'internal_reappear',
        'external_marks',
        'external_reappear',
        'attendance_marks',
        'total_marks',
        'final_result',
        'total_reappear_subjects',
        'remarks',
        'sgpa',
        'cgpa',
        'cumulative_credits',
        'exam_attempt',
        'compiled_at',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}
