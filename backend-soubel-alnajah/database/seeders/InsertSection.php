<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School\Section;
use Illuminate\Support\Facades\DB;

class InsertSection extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sections')->delete();
        Section::create([
            'school_id' => 1,
            'grade_id' => 3,
            'classroom_id' => 1,
            'name_section' => ['fr'=> 'a', 'ar'=> 'أ'],  
            'Status' => 1,  
        ]);
        Section::create([
            'school_id' => 1,
            'grade_id' => 3,
            'classroom_id' => 1,
            'name_section' => ['fr'=> 'b', 'ar'=> 'ب'],  
            'Status' => 1,  
        ]);
        Section::create([
            'school_id' => 1,
            'grade_id' => 3,
            'classroom_id' => 4,
            'name_section' => ['fr'=> 'c', 'ar'=> 'س'],  
            'Status' => 1,  
        ]);
        Section::create([
            'school_id' => 1,
            'grade_id' => 3,
            'classroom_id' => 4,
            'name_section' => ['fr'=> 'j', 'ar'=> 'ج'],  
            'Status' => 1,  
        ]);
    }
}
