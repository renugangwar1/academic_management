<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicSession extends Model
{
    use HasFactory;

    // 🛠 UPDATED to match the migration table structure
    protected $fillable = [
        'year', 'term', 'odd_even', 'diploma_year', 'active', 'type'
    ];

  public function programs()
{
    return $this->belongsToMany(Program::class, 'academic_session_program')
                ->withPivot('structure', 'semester') // ✅ correct columns
                ->withTimestamps();
}

public function students()
{
    return $this->hasMany(Student::class);
}

    public function regularPrograms()
    {
        return $this->programs()->wherePivot('structure', 'semester');
    }

    public function diplomaPrograms()
    {
        return $this->programs()->wherePivot('structure', 'yearly');
    }


// app/Models/AcademicSession.php
public function getDisplayNameAttribute()
{
    return trim("{$this->year} " . ucfirst($this->term ?? '') . ' (' . ucfirst($this->odd_even ?? '') . ')');
}




}


