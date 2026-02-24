<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AgendaScolaire\Grade;
use Illuminate\Support\Facades\DB;

class InsertGrade extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // حذف البيانات القديمة
        //DB::table('grades')->delete();

        Grade::create([
            'name_grades' => [
                'fr' => 'Préparatoire',
                'ar' => 'تحضيري',
                'en' => 'Preparatory'
            ],
        ]);

        Grade::create([
            'name_grades' => [
                'fr' => 'Primaire',
                'ar' => 'ابتدائي',
                'en' => 'Primary'
            ],
        ]);

        Grade::create([
            'name_grades' => [
                'fr' => 'Moyen',
                'ar' => 'متوسط',
                'en' => 'Middle School'
            ],
        ]);

        // الثانوي مقسم
        Grade::create([
            'name_grades' => [
                'fr' => 'Secondaire - Première année',
                'ar' => 'ثانوي - سنة أولى',
                'en' => 'Secondary - First Year'
            ],
        ]);

        Grade::create([
            'name_grades' => [
                'fr' => 'Secondaire - Deuxième année',
                'ar' => 'ثانوي - سنة ثانية',
                'en' => 'Secondary - Second Year'
            ],
        ]);

        Grade::create([
            'name_grades' => [
                'fr' => 'Secondaire - Baccalauréat',
                'ar' => 'ثانوي - بكالوريا',
                'en' => 'Secondary - Baccalaureate'
            ],
        ]);
    }
}
