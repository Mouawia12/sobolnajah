<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School\School;
use Illuminate\Support\Facades\DB;

class InsertSchool extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('schools')->delete();

        School::create([
            'name_school' => [
                'fr' => 'Sobol Najah EL Oued',
                'ar' => 'سبل النجاح الوادي',
                'en' => 'Sobol Najah El Oued'
            ],
        ]);

        School::create([
            'name_school' => [
                'fr' => 'Sobol Najah Annexe Dbila',
                'ar' => 'سبل النجاح فرع الدبيلة',
                'en' => 'Sobol Najah Annex Dbila'
            ],
        ]);
    }
}
