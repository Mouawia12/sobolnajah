<?php

namespace App\Models\AgendaScolaire;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Gallery extends Model
{
    use HasFactory;

    protected $fillable=['publication_id','agenda_id','img_url'];
    protected $table = 'galleries';
    public $timestamps = true;


    public function publication()
    {
        return $this->belongsTo(Publication::class,'publication_id','id');
    }
}
