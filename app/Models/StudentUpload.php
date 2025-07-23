<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class StudentUpload extends Model
{
  protected $fillable = ['institute_id', 'filename', 'file_path', 'status'];

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }
}
