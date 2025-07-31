<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    // Allow mass assignment for these fields
    protected $fillable = [
        'institute_id',
          'admin_id',
        'subject',
         'is_admin',
        'message',
    ];

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }
}
