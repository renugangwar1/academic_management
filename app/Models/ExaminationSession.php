<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExaminationSession extends Model
{
    use HasFactory;
    protected $fillable = [
    'name',
    'academic_year',
    'session',
    'exam_type',
    'start_date',
    'end_date',
];

}
