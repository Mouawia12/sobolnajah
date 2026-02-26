<?php

namespace App\Actions\Inscription;

use App\Models\Inscription\Inscription;
use App\Models\School\Section;
use App\Services\StudentEnrollmentService;
use Illuminate\Support\Facades\DB;

class ApproveInscriptionAction
{
    public function __construct(private StudentEnrollmentService $enrollmentService)
    {
    }

    public function execute(Inscription $inscription, Section $section): void
    {
        DB::transaction(function () use ($inscription, $section) {
            $inscription->update(['statu' => 'accept']);

            $studentPayload = [
                'first_name' => [
                    'fr' => $inscription->getTranslation('prenom', 'fr'),
                    'ar' => $inscription->getTranslation('prenom', 'ar'),
                    'en' => $inscription->getTranslation('prenom', 'fr'),
                ],
                'last_name' => [
                    'fr' => $inscription->getTranslation('nom', 'fr'),
                    'ar' => $inscription->getTranslation('nom', 'ar'),
                    'en' => $inscription->getTranslation('nom', 'fr'),
                ],
                'email' => $inscription->email,
                'gender' => $inscription->gender,
                'phone' => $inscription->numtelephone,
                'birth_date' => $inscription->datenaissance,
                'birth_place' => $inscription->lieunaissance,
                'wilaya' => $inscription->wilaya,
                'dayra' => $inscription->dayra,
                'baladia' => $inscription->baladia,
            ];

            $guardianPayload = [
                'first_name' => [
                    'fr' => $inscription->getTranslation('prenomwali', 'fr'),
                    'ar' => $inscription->getTranslation('prenomwali', 'ar'),
                    'en' => $inscription->getTranslation('prenomwali', 'fr'),
                ],
                'last_name' => [
                    'fr' => $inscription->getTranslation('nomwali', 'fr'),
                    'ar' => $inscription->getTranslation('nomwali', 'ar'),
                    'en' => $inscription->getTranslation('nomwali', 'fr'),
                ],
                'relation' => $inscription->relationetudiant,
                'address' => $inscription->adressewali,
                'wilaya' => $inscription->wilayawali,
                'dayra' => $inscription->dayrawali,
                'baladia' => $inscription->baladiawali,
                'phone' => $inscription->numtelephonewali,
                'email' => $inscription->emailwali,
            ];

            $this->enrollmentService->createStudent($studentPayload, $guardianPayload, $section);
        });
    }
}
