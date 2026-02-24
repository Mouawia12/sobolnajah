<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class InsertAdmin extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->delete();

        User::create([
            'name' => [
                'fr' => 'sobol najah',
                'ar' => 'سبل النجاح',
                'en' => 'Sobol Najah'
            ],
            'email' => 'admin@sobolnajah.com',
            'password' => Hash::make(12345678),
        ])->attachRole('admin');

        User::create([
            'name' => [
                'fr' => 'mouawia youmbai',
                'ar' => 'معاوية يمبعي',
                'en' => 'Mouawia Youmbai'
            ],
            'email' => 'mouawia@sobolnajah.com',
            'password' => Hash::make(12345678),
        ])->attachRole('student');
    }
}
