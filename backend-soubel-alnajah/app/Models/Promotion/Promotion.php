<?php

namespace App\Models\Promotion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;


use App\Models\Inscription\StudentInfo;

use App\Models\School\School;
use App\Models\School\Schoolgrade;
use App\Models\School\Classroom;
use App\Models\School\Section;




class Promotion extends Model
{
    use HasFactory;
    use HasTranslations;
    public $translatable = ['prenom','nom'];
    protected $guarded=[];

    public function student()
    {
        return $this->belongsTo(StudentInfo::class,'student_id');
    }

    public function f_school()
    {
        return $this->belongsTo(School::class, 'from_school');
    }

    public function f_grade()
    {
        return $this->belongsTo(Schoolgrade::class, 'from_grade');
    }


    // علاقة بين الترقيات الصفوف الدراسية لجلب اسم الصف في جدول الترقيات

    public function f_classroom()
    {
        return $this->belongsTo(Classroom::class, 'from_Classroom');
    }

    // علاقة بين الترقيات الاقسام الدراسية لجلب اسم القسم  في جدول الترقيات

    public function f_section()
    {
        return $this->belongsTo(Section::class, 'from_section');
    }

    public function t_school()
    {
        return $this->belongsTo(School::class, 'from_grade');
    }

    // علاقة بين الترقيات والمراحل الدراسية لجلب اسم المرحلة في جدول الترقيات

    public function t_grade()
    {
        return $this->belongsTo(Schoolgrade::class, 'to_grade');
    }


    // علاقة بين الترقيات الصفوف الدراسية لجلب اسم الصف في جدول الترقيات

    public function t_classroom()
    {
        return $this->belongsTo(Classroom::class, 'to_Classroom');
    }

    // علاقة بين الترقيات الاقسام الدراسية لجلب اسم القسم  في جدول الترقيات

    public function t_section()
    {
        return $this->belongsTo(Section::class, 'to_section');
    }
















}
