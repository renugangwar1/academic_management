<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'student_id',
        'from_semester',
        'to_semester',
        'promoted_by',
        'promoted_at',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function promotedBy()
    {
        return $this->belongsTo(User::class, 'promoted_by'); // Assuming Admins are in users table
    }
}
