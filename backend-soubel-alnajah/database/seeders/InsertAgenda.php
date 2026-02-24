<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AgendaScolaire\Agenda;


class InsertAgenda extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // مسح البيانات القديمة وإعادة ضبط ID
        //Agenda::truncate();

        $agendas = [
            [
                'ar' => 'تكريمات',
                'fr' => 'Cérémonies',
                'en' => 'Ceremonies',
            ],
            [
                'ar' => 'اجتماعات',
                'fr' => 'Réunions',
                'en' => 'Meetings',
            ],
            [
                'ar' => 'أنشطة رياضية',
                'fr' => 'Activités sportives',
                'en' => 'Sports Activities',
            ],
            [
                'ar' => 'أنشطة ثقافية',
                'fr' => 'Activités culturelles',
                'en' => 'Cultural Activities',
            ],
            [
                'ar' => 'رحلات مدرسية',
                'fr' => 'Voyages scolaires',
                'en' => 'School Trips',
            ],
            [
                'ar' => 'ورشات عمل',
                'fr' => 'Ateliers',
                'en' => 'Workshops',
            ],
            [
                'ar' => 'إعلانات',
                'fr' => 'Annonces',
                'en' => 'Announcements',
            ],
            [
                'ar' => 'مناسبات وطنية',
                'fr' => 'Événements nationaux',
                'en' => 'National Events',
            ],
            [
                'ar' => 'مناسبات دينية',
                'fr' => 'Événements religieux',
                'en' => 'Religious Events',
            ],
            [
                'ar' => 'امتحانات',
                'fr' => 'Examens',
                'en' => 'Exams',
            ],
        ];

        foreach ($agendas as $agenda) {
            Agenda::create([
                'name_agenda' => $agenda,
            ]);
        }
    }
}
