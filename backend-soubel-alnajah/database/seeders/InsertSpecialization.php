<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Specialization\Specialization;

class InsertSpecialization extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('specializations')->delete();

        $specializations = [
            ['ar' => 'رياضيات', 'fr' => 'Math', 'en' => 'Math'],
            ['ar' => 'انجليزية', 'fr' => 'Anglais', 'en' => 'English'],
            ['ar' => 'عربية', 'fr' => 'Arabe', 'en' => 'Arabic'],
            ['ar' => 'فرنسية', 'fr' => 'Française', 'en' => 'French'],
            ['ar' => 'علوم', 'fr' => 'Sciences', 'en' => 'Science'],
            ['ar' => 'اداب', 'fr' => 'Littérature', 'en' => 'Literature'],
            ['ar' => 'اقتصاد', 'fr' => 'Économie', 'en' => 'Economy'],
            ['ar' => 'فيزياء', 'fr' => 'Physique', 'en' => 'Physics'],
            ['ar' => 'تاريخ', 'fr' => 'Histoire', 'en' => 'History'],
            ['ar' => 'جغرافيا', 'fr' => 'Géographie', 'en' => 'Geography'],
            ['ar' => 'تربية اسلامية', 'fr' => 'Éducation islamique', 'en' => 'Islamic Education'],
            ['ar' => 'تربية مدنية', 'fr' => 'Éducation Civique', 'en' => 'Civic Education'],
            ['ar' => 'اعلام الي', 'fr' => 'Informatique', 'en' => 'Computer Science'],
            ['ar' => 'قانون', 'fr' => 'Droit', 'en' => 'Law'],
            ['ar' => 'تسيير محاسبي', 'fr' => 'Gestion comptable', 'en' => 'Accounting Management'],
            ['ar' => 'فلسفة', 'fr' => 'Philosophie', 'en' => 'Philosophy'],
        ];

        foreach ($specializations as $S) {
            Specialization::create([
                'name' => [
                    'ar' => $S['ar'],
                    'fr' => $S['fr'],
                    'en' => $S['en'],
                ]
            ]);
        }
    }
}
