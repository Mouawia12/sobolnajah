<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(LaratrustSeeder::class);
        $this->call(InsertSchool::class);
        $this->call(InsertSchoolgrade::class);
        $this->call(InsertClassroom::class);
        //$this->call(InsertSection::class);
        $this->call(InsertAdmin::class);
        //$this->call(InsertStudent::class);
        $this->call(InsertSpecialization::class);
        $this->call(InsertAgenda::class);
        $this->call(InsertGrade::class);




    }
}
