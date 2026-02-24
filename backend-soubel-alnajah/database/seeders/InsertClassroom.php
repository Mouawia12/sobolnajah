<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School\Classroom;
use Illuminate\Support\Facades\DB;

class InsertClassroom extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('classrooms')->delete();

        Classroom::create([
            'school_id' => 2,
            'grade_id' => 3,
            'name_class' => [
                'fr' => 'première année',
                'ar' => 'سنة اولى',
                'en' => 'first year'
            ],
        ]);

        Classroom::create([
            'school_id' => 2,
            'grade_id' => 4,
            'name_class' => [
                'fr' => 'première année',
                'ar' => 'سنة اولى',
                'en' => 'first year'
            ],
        ]);
        Classroom::create([
            'school_id' => 2,
            'grade_id' => 4,
            'name_class' => [
                'fr' => 'deuxième année',
                'ar' => 'سنة ثانية',
                'en' => 'second year'
            ],
        ]);
        Classroom::create([
            'school_id' => 2,
            'grade_id' => 4,
            'name_class' => [
                'fr' => 'troisième année',
                'ar' => 'سنة ثالثة',
                'en' => 'third year'
            ],
        ]);
        Classroom::create([
            'school_id' => 2,
            'grade_id' => 4,
            'name_class' => [
                'fr' => 'quatrième année',
                'ar' => 'سنة رابعة',
                'en' => 'fourth year'
            ],
        ]);
        Classroom::create([
            'school_id' => 2,
            'grade_id' => 4,
            'name_class' => [
                'fr' => 'cinquième année',
                'ar' => 'سنة خامسة',
                'en' => 'fifth year'
            ],
        ]);

        Classroom::create([
            'school_id' => 2,
            'grade_id' => 5,
            'name_class' => [
                'fr' => 'première année',
                'ar' => 'سنة اولى',
                'en' => 'first year'
            ],
        ]);
        Classroom::create([
            'school_id' => 2,
            'grade_id' => 5,
            'name_class' => [
                'fr' => 'deuxième année',
                'ar' => 'سنة ثانية',
                'en' => 'second year'
            ],
        ]);
        Classroom::create([
            'school_id' => 2,
            'grade_id' => 5,
            'name_class' => [
                'fr' => 'troisième année',
                'ar' => 'سنة ثالثة',
                'en' => 'third year'
            ],
        ]);
        Classroom::create([
            'school_id' => 2,
            'grade_id' => 5,
            'name_class' => [
                'fr' => 'quatrième année',
                'ar' => 'سنة رابعة',
                'en' => 'fourth year'
            ],
        ]);

        Classroom::create([
            'school_id' => 2,
            'grade_id' => 6,
            'name_class' => [
                'fr' => 'Sciences du tronc commun première année',
                'ar' => 'سنة اولى جذع مشترك علوم',
                'en' => 'First year common core sciences'
            ],
        ]);
        Classroom::create([
            'school_id' => 2,
            'grade_id' => 6,
            'name_class' => [
                'fr' => 'Littérature du tronc commun de première année',
                'ar' => 'سنة اولى جذع مشترك اداب',
                'en' => 'First year common core literature'
            ],
        ]);
        Classroom::create([
            'school_id' => 2,
            'grade_id' => 6,
            'name_class' => [
                'fr' => 'Deuxième science expérimentale',
                'ar' => 'ثانية علوم تجريبية',
                'en' => 'Second year experimental sciences'
            ],
        ]);
        Classroom::create([
            'school_id' => 2,
            'grade_id' => 6,
            'name_class' => [
                'fr' => 'Deuxième gestion et économie',
                'ar' => 'ثانية تسيير واقتصاد',
                'en' => 'Second year management and economics'
            ],
        ]);
        Classroom::create([
            'school_id' => 2,
            'grade_id' => 6,
            'name_class' => [
                'fr' => 'deuxièmes langues étrangères',
                'ar' => 'ثانية لغات اجنبية',
                'en' => 'Second year foreign languages'
            ],
        ]);
        Classroom::create([
            'school_id' => 2,
            'grade_id' => 6,
            'name_class' => [
                'fr' => 'troisième année Sciences expérimentales',
                'ar' => 'ثالثة علوم تجريبية',
                'en' => 'Third year experimental sciences'
            ],
        ]);
        Classroom::create([
            'school_id' => 2,
            'grade_id' => 6,
            'name_class' => [
                'fr' => 'troisième année Gestion et économie',
                'ar' => 'ثالثة تسيير واقتصاد',
                'en' => 'Third year management and economics'
            ],
        ]);
        Classroom::create([
            'school_id' => 2,
            'grade_id' => 6,
            'name_class' => [
                'fr' => 'troisième année Langues étrangères',
                'ar' => 'ثالثة لغات اجنبية',
                'en' => 'Third year foreign languages'
            ],
        ]);
        Classroom::create([
            'school_id' => 2,
            'grade_id' => 6,
            'name_class' => [
                'fr' => 'méthodes d’ingénierie',
                'ar' => 'هندسة طرائق',
                'en' => 'Engineering methods'
            ],
        ]);

        Classroom::create([
            'school_id' => 1,
            'grade_id' => 1,
            'name_class' => [
                'fr' => 'première année',
                'ar' => 'سنة اولى',
                'en' => 'first year'
            ],
        ]);
        Classroom::create([
            'school_id' => 1,
            'grade_id' => 1,
            'name_class' => [
                'fr' => 'deuxième année',
                'ar' => 'سنة ثانية',
                'en' => 'second year'
            ],
        ]);
        Classroom::create([
            'school_id' => 1,
            'grade_id' => 1,
            'name_class' => [
                'fr' => 'troisième année',
                'ar' => 'سنة ثالثة',
                'en' => 'third year'
            ],
        ]);
        Classroom::create([
            'school_id' => 1,
            'grade_id' => 1,
            'name_class' => [
                'fr' => 'quatrième année',
                'ar' => 'سنة رابعة',
                'en' => 'fourth year'
            ],
        ]);

        Classroom::create([
            'school_id' => 1,
            'grade_id' => 2,
            'name_class' => [
                'fr' => 'Sciences du tronc commun première année',
                'ar' => 'سنة اولى جذع مشترك علوم',
                'en' => 'First year common core sciences'
            ],
        ]);
        Classroom::create([
            'school_id' => 1,
            'grade_id' => 2,
            'name_class' => [
                'fr' => 'Littérature du tronc commun de première année',
                'ar' => 'سنة اولى جذع مشترك اداب',
                'en' => 'First year common core literature'
            ],
        ]);
        Classroom::create([
            'school_id' => 1,
            'grade_id' => 2,
            'name_class' => [
                'fr' => 'Deuxième science expérimentale',
                'ar' => 'ثانية علوم تجريبية',
                'en' => 'Second year experimental sciences'
            ],
        ]);
        Classroom::create([
            'school_id' => 1,
            'grade_id' => 2,
            'name_class' => [
                'fr' => 'Deuxième gestion et économie',
                'ar' => 'ثانية تسيير واقتصاد',
                'en' => 'Second year management and economics'
            ],
        ]);
        Classroom::create([
            'school_id' => 1,
            'grade_id' => 2,
            'name_class' => [
                'fr' => 'deuxièmes langues étrangères',
                'ar' => 'ثانية لغات اجنبية',
                'en' => 'Second year foreign languages'
            ],
        ]);
        Classroom::create([
            'school_id' => 1,
            'grade_id' => 2,
            'name_class' => [
                'fr' => 'troisième année Sciences expérimentales',
                'ar' => 'ثالثة علوم تجريبية',
                'en' => 'Third year experimental sciences'
            ],
        ]);
        Classroom::create([
            'school_id' => 1,
            'grade_id' => 2,
            'name_class' => [
                'fr' => 'troisième année Gestion et économie',
                'ar' => 'ثالثة تسيير واقتصاد',
                'en' => 'Third year management and economics'
            ],
        ]);
        Classroom::create([
            'school_id' => 1,
            'grade_id' => 2,
            'name_class' => [
                'fr' => 'troisième année Langues étrangères',
                'ar' => 'ثالثة لغات اجنبية',
                'en' => 'Third year foreign languages'
            ],
        ]);
        Classroom::create([
            'school_id' => 1,
            'grade_id' => 2,
            'name_class' => [
                'fr' => 'méthodes d’ingénierie',
                'ar' => 'هندسة طرائق',
                'en' => 'Engineering methods'
            ],
        ]);
    }
}
