<?php

namespace App\Actions\Inscription;

class BuildInscriptionPayloadAction
{
    public function forStore(array $input): array
    {
        return array_merge($this->basePayload($input), [
            'gender' => $input['gender'] ?? null,
            'statu' => 'procec',
        ]);
    }

    public function forUpdate(array $input): array
    {
        return array_merge($this->basePayload($input), [
            'statu' => $input['statu'] ?? null,
        ]);
    }

    private function basePayload(array $input): array
    {
        return [
            'school_id' => $input['school_id'] ?? null,
            'grade_id' => $input['grade_id'] ?? null,
            'classroom_id' => $input['classroom_id'] ?? null,
            'inscriptionetat' => $input['inscriptionetat'] ?? null,
            'nomecoleprecedente' => $input['nomecoleprecedente'] ?? null,
            'dernieresection' => $input['dernieresection'] ?? null,
            'moyensannuels' => $input['moyensannuels'] ?? null,
            'numeronationaletudiant' => $input['numeronationaletudiant'] ?? null,
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
            'email' => $input['email'] ?? null,
            'numtelephone' => $input['numtelephone'] ?? null,
            'datenaissance' => $input['datenaissance'] ?? null,
            'lieunaissance' => $input['lieunaissance'] ?? null,
            'wilaya' => $input['wilaya'] ?? null,
            'dayra' => $input['dayra'] ?? null,
            'baladia' => $input['baladia'] ?? null,
            'adresseactuelle' => $input['adresseactuelle'] ?? null,
            'codepostal' => $input['codepostal'] ?? null,
            'residenceactuelle' => $input['residenceactuelle'] ?? null,
            'etatsante' => $input['etatsante'] ?? null,
            'identificationmaladie' => $input['identificationmaladie'] ?? null,
            'alfdlprsaldr' => $input['alfdlprsaldr'] ?? null,
            'autresnotes' => $input['autresnotes'] ?? null,
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
            'numtelephonewali' => $input['numtelephonewali'] ?? null,
            'emailwali' => $input['emailwali'] ?? null,
            'wilayawali' => $input['wilayawali'] ?? null,
            'dayrawali' => $input['dayrawali'] ?? null,
            'baladiawali' => $input['baladiawali'] ?? null,
        ];
    }
}
