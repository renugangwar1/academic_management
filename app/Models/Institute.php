<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institute extends Model
{
   use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'contact_email',
        'contact_phone',
        'password',
    ];

    public function students() {
    return $this->hasMany(Student::class);
}
public function programs()
{
    return $this->belongsToMany(Program::class, 'institute_program');
}

}
