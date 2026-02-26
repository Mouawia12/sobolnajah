<?php

namespace App\Actions\Inscription;

use App\Models\Inscription\StudentInfo;
use App\Models\School\Section;
use Illuminate\Support\Facades\DB;

class UpdateStudentEnrollmentAction
{
    public function execute(StudentInfo $student, array $input, Section $section): void
    {
        DB::transaction(function () use ($student, $input, $section) {
            $student->update([
                'section_id' => $section->id,
                'prenom' => [
                    'fr' => $input['prenomfr'] ?? null,
                    'ar' => $input['prenomar'] ?? null,
                    'en' => $input['prenomfr'] ?? null,
                ],
                'nom' => [
                    'fr' => $input['nomfr'] ?? null,
                    'ar' => $input['nomar'] ?? null,
                    'en' => $input['nomfr'] ?? null,
                ],
                'gender' => isset($input['gender']) ? (int) $input['gender'] : null,
                'numtelephone' => $input['numtelephone'] ?? null,
                'datenaissance' => $input['datenaissance'] ?? null,
                'lieunaissance' => $input['lieunaissance'] ?? null,
                'wilaya' => $input['wilaya'] ?? null,
                'dayra' => $input['dayra'] ?? null,
                'baladia' => $input['baladia'] ?? null,
            ]);

            if ($student->user) {
                $student->user->update([
                    'name' => [
                        'fr' => $input['prenomfr'] ?? null,
                        'ar' => $input['prenomar'] ?? null,
                        'en' => $input['prenomfr'] ?? null,
                    ],
                    'email' => $input['email'] ?? null,
                    'school_id' => $section->school_id,
                ]);
            }

            if ($student->parent) {
                $student->parent->update([
                    'prenomwali' => [
                        'fr' => $input['prenomfrwali'] ?? null,
                        'ar' => $input['prenomarwali'] ?? null,
                        'en' => $input['prenomfrwali'] ?? null,
                    ],
                    'nomwali' => [
                        'fr' => $input['nomfrwali'] ?? null,
                        'ar' => $input['nomarwali'] ?? null,
                        'en' => $input['nomfrwali'] ?? null,
                    ],
                    'relationetudiant' => $input['relationetudiant'] ?? null,
                    'adressewali' => $input['adressewali'] ?? null,
                    'wilayawali' => $input['wilayawali'] ?? null,
                    'dayrawali' => $input['dayrawali'] ?? null,
                    'baladiawali' => $input['baladiawali'] ?? null,
                    'numtelephonewali' => $input['numtelephonewali'] ?? null,
                ]);

                if ($student->parent->user) {
                    $student->parent->user->update([
                        'name' => [
                            'fr' => $input['prenomfrwali'] ?? null,
                            'ar' => $input['prenomarwali'] ?? null,
                            'en' => $input['prenomfrwali'] ?? null,
                        ],
                        'email' => $input['emailwali'] ?? null,
                        'school_id' => $section->school_id,
                    ]);
                }
            }
        });
    }
}

