<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Institute extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'email',
        'contact_phone',
        'password',
    ];

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function programs()
    {
        return $this->belongsToMany(Program::class, 'institute_program');
    }

    public function messages()
{
    return $this->hasMany(\App\Models\Message::class);
}

}
