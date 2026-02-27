<?php
namespace App\Models\AgendaScolaire;

use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    use HasFactory;
    use HasTranslations;
    public $translatable = ['title','body'];

    protected $fillable=['grade_id','agenda_id','title','body','like'];
    protected $table = 'publications';
    public $timestamps = true;


    public function galleries()
    {
        return $this->hasMany(Gallery::class,'publication_id');
    }

    public function gallery()
    {
        return $this->hasOne(Gallery::class, 'publication_id', 'id');
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class,'grade_id','id');
    }

    public function agenda()
    {
        return $this->belongsTo(Agenda::class,'agenda_id','id');
    }
}
