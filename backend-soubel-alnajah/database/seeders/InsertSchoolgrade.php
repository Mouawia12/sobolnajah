<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School\Schoolgrade;
use Illuminate\Support\Facades\DB;

class InsertSchoolgrade extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('schoolgrades')->delete();

        Schoolgrade::create([
            'school_id' => 1,
            'name_grade' => [
                'fr' => 'Moyen',
                'ar' => 'متوسط',
                'en' => 'Middle School'
            ],
            'notes' => [
                'fr' => '###',
                'ar' => '###',
                'en' => '###'
            ],
        ]);

        Schoolgrade::create([
            'school_id' => 1,
            'name_grade' => [
                'fr' => 'Secondaire',
                'ar' => 'ثانوية',
                'en' => 'High School'
            ],
            'notes' => [
                'fr' => '###',
                'ar' => '###',
                'en' => '###'
            ],
        ]);

        Schoolgrade::create([
            'school_id' => 2,
            'name_grade' => [
                'fr' => 'Préparatoire',
                'ar' => 'تحضيري',
                'en' => 'Preparatory'
            ],
            'notes' => [
                'fr' => '###',
                'ar' => '###',
                'en' => '###'
            ],
        ]);

        Schoolgrade::create([
            'school_id' => 2,
            'name_grade' => [
                'fr' => 'Primaire',
                'ar' => 'إبتدائي',
                'en' => 'Primary'
            ],
            'notes' => [
                'fr' => '###',
                'ar' => '###',
                'en' => '###'
            ],
        ]);

        Schoolgrade::create([
            'school_id' => 2,
            'name_grade' => [
                'fr' => 'Moyen',
                'ar' => 'متوسط',
                'en' => 'Middle School'
            ],
            'notes' => [
                'fr' => '###',
                'ar' => '###',
                'en' => '###'
            ],
        ]);

        Schoolgrade::create([
            'school_id' => 2,
            'name_grade' => [
                'fr' => 'Secondaire',
                'ar' => 'ثانوية',
                'en' => 'High School'
            ],
            'notes' => [
                'fr' => '###',
                'ar' => '###',
                'en' => '###'
            ],
        ]);
    }
}
