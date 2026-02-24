<?php

namespace App\Models\AgendaScolaire;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Models\School\Classroom;
use App\Models\School\Schoolgrade;
use App\Models\Specialization\Specialization;





class Exames extends Model
{
    use HasFactory;
    use HasTranslations;
    public $translatable = ['name','module'];
    protected $guarded = [];
    protected $table = 'exames';

    public $timestamps = true;

    public function schoolgrade()
    {
        return $this->belongsTo(Schoolgrade::class,'grade_id','id');
    }


    public function classroom()
    {
        return $this->belongsTo(Classroom::class,'classroom_id','id');
    }

    public function specialization()
    {
        return $this->belongsTo(Specialization::class,'specialization_id','id');
    }
}
