<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inscription\Inscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InsertStudent extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
 
        //DB::table('inscriptions')->delete();
        for($i=0;$i<14;$i++){
        Inscription::create([
            'school_id' => 1,
            'grade_id' => 3,
            'classroom_id' => 1,
            'inscriptionetat' => 'nouvelleinscription',
            'nomecoleprecedente' => 'boudiaf',
            'dernieresection' => 'bacaloria',
            'moyensannuels' => 10,
            'numeronationaletudiant' => 113343113,
            'prenom' =>  ['fr'=> 'Mouawia', 'ar'=> 'معاوية'] ,
            'nom' =>  ['fr'=> 'Youmbai', 'ar'=> 'يمبعي'],
            'email' => Str::random(10).'@gmail.com',
            'gender' => 1,
            'numtelephone' => 698822707,
            'datenaissance' => '2022-06-25',
            'lieunaissance' => 'temacine',
            'wilaya' => 'El Oued',
            'dayra' => 'gumar',
            'baladia' => 'Taghzout',
            'adresseactuelle' => '5 July 1962',
            'codepostal' => 39040,
            'residenceactuelle' => 'parents',
            'etatsante' => 'bien',
            'identificationmaladie' => 'rien je suis bien',
            'alfdlprsaldr' => 'oui',
            'autresnotes' => "",
            'prenomwali' => ['fr'=> 'Ben amor', 'ar'=> 'بن عمر'],
            'nomwali' => ['fr'=> 'Youmbai', 'ar'=> 'يمبعي'],
            'relationetudiant' => 'pere',
            'adressewali' => '5 July 1962',
            'numtelephonewali' => 12312312,
            'emailwali' => Str::random(10).'@gmail.com',
            'wilayawali' => 'El Oued',
            'dayrawali' => 'gumar',
            'baladiawali' => 'Taghzout',
            'statu' => 'procec',
        ]);
        Inscription::create([
            'school_id' => 1,
            'grade_id' => 3,
            'classroom_id' => 4,
            'inscriptionetat' => 'nouvelleinscription',
            'nomecoleprecedente' => 'boudiaf',
            'dernieresection' => 'bacaloria',
            'moyensannuels' => 10,
            'numeronationaletudiant' => 113343113,
            'prenom' =>  ['fr'=> 'Mouawia', 'ar'=> 'معاوية'] ,
            'nom' =>  ['fr'=> 'Youmbai', 'ar'=> 'يمبعي'],
            'email' => Str::random(10).'@gmail.com',
            'gender' => 1,
            'numtelephone' => 698822707,
            'datenaissance' => '2022-06-25',
            'lieunaissance' => 'temacine',
            'wilaya' => 'El Oued',
            'dayra' => 'gumar',
            'baladia' => 'Taghzout',
            'adresseactuelle' => '5 July 1962',
            'codepostal' => 39040,
            'residenceactuelle' => 'parents',
            'etatsante' => 'bien',
            'identificationmaladie' => 'rien je suis bien',
            'alfdlprsaldr' => 'oui',
            'autresnotes' => "",
            'prenomwali' => ['fr'=> 'Ben amor', 'ar'=> 'بن عمر'],
            'nomwali' => ['fr'=> 'Youmbai', 'ar'=> 'يمبعي'],
            'relationetudiant' => 'pere',
            'adressewali' => '5 July 1962',
            'numtelephonewali' => 12312312,
            'emailwali' => Str::random(10).'@gmail.com',
            'wilayawali' => 'El Oued',
            'dayrawali' => 'gumar',
            'baladiawali' => 'Taghzout',
            'statu' => 'procec',
        ]);
    }
    }
}
