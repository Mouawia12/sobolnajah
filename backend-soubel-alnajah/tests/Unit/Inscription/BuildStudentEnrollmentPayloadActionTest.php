<?php

namespace Tests\Unit\Inscription;

use App\Actions\Inscription\BuildStudentEnrollmentPayloadAction;
use PHPUnit\Framework\TestCase;

class BuildStudentEnrollmentPayloadActionTest extends TestCase
{
    public function test_it_builds_student_and_guardian_payloads_from_input(): void
    {
        $action = new BuildStudentEnrollmentPayloadAction();

        $input = [
            'prenomfr' => 'Ali',
            'prenomar' => 'علي',
            'nomfr' => 'Ben',
            'nomar' => 'بن',
            'email' => 'student@example.test',
            'gender' => '1',
            'numtelephone' => '0550000001',
            'datenaissance' => '2012-01-01',
            'lieunaissance' => 'Oran',
            'wilaya' => 'Oran',
            'dayra' => 'Ain El Turk',
            'baladia' => 'Oran',
            'prenomfrwali' => 'Fatima',
            'prenomarwali' => 'فاطمة',
            'nomfrwali' => 'Ben',
            'nomarwali' => 'بن',
            'relationetudiant' => 'mother',
            'adressewali' => 'Adresse',
            'wilayawali' => 'Oran',
            'dayrawali' => 'Ain El Turk',
            'baladiawali' => 'Oran',
            'numtelephonewali' => '0550000002',
            'emailwali' => 'guardian@example.test',
        ];

        $result = $action->execute($input);

        $this->assertSame('Ali', $result['student']['first_name']['fr']);
        $this->assertSame('علي', $result['student']['first_name']['ar']);
        $this->assertSame(1, $result['student']['gender']);
        $this->assertSame('student@example.test', $result['student']['email']);

        $this->assertSame('Fatima', $result['guardian']['first_name']['fr']);
        $this->assertSame('فاطمة', $result['guardian']['first_name']['ar']);
        $this->assertSame('mother', $result['guardian']['relation']);
        $this->assertSame('guardian@example.test', $result['guardian']['email']);
    }
}

