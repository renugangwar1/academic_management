<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSessionHistory extends Model
{
    protected $fillable = [
        'student_id',
        'academic_session_id',
        'program_id',
        'institute_id',
        'from_semester',
        'to_semester',
        'semester',
        'promotion_type',
        'promoted_by',
        'promoted_at',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

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

    public function promotedBy()
    {
        return $this->belongsTo(User::class, 'promoted_by');
    }
}