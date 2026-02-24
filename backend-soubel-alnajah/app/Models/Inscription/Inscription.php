<?php

namespace App\Models\Inscription;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Translatable\HasTranslations;
use App\Models\School\Classroom;



class Inscription extends Model
{
    use HasFactory;
    use HasTranslations;
    public $translatable = ['prenom','nom','prenomwali','nomwali','name_class'];


    protected $guarded = [];

    protected $table = 'inscriptions';
    public $timestamps = true;


    public function classroom()
    {
        return $this->belongsTo(Classroom::class,'classroom_id','id');
    }

}
