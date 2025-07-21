<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InternalResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'program_id',
        'course_id',
        'semester',
        'internal_marks',
        'status',
        'compiled_at',
    ];

    public function student() { return $this->belongsTo(Student::class); }

    public function course()  { return $this->belongsTo(Course::class); }

    public function program() { return $this->belongsTo(Program::class); }
}