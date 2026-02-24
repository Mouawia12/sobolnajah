<?php

namespace App\Models\AgendaScolaire;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;


class Grade extends Model
{
    use HasFactory;
    use HasTranslations;
    public $translatable = ['name_grades'];

    protected $fillable=['name_grades'];
    protected $table = 'grades';
    public $timestamps = true;



   
}
