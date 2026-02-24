<?php

namespace App\Models\AgendaScolaire;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;


class Agenda extends Model
{
    use HasFactory;
    use HasTranslations;
    public $translatable = ['name_agenda'];

    protected $fillable=['name_agenda'];
    protected $table = 'agenda';
    public $timestamps = true;


}
