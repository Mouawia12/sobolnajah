<?php

namespace App\Models\AgendaScolaire;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteStudent extends Model
{
    use HasFactory;
    protected $fillable=['urlfile1','urlfile2','urlfile3'];


    public function student()
    {
        return $this->belongsTo(StudentInfo::class,'student_id','id');
    }

    
}
