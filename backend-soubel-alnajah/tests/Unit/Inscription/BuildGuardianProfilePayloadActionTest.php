<?php

namespace Tests\Unit\Inscription;

use App\Actions\Inscription\BuildGuardianProfilePayloadAction;
use App\Actions\Inscription\BuildLocalizedNameAction;
use PHPUnit\Framework\TestCase;

class BuildGuardianProfilePayloadActionTest extends TestCase
{
    public function test_it_builds_guardian_profile_payload(): void
    {
        $action = new BuildGuardianProfilePayloadAction(new BuildLocalizedNameAction());

        $payload = $action->execute([
            'first_name' => ['fr' => 'Fatima', 'ar' => 'فاطمة'],
            'last_name' => ['fr' => 'Ben', 'ar' => 'بن'],
            'relation' => 'mother',
            'address' => 'Address',
            'wilaya' => 'Oran',
            'dayra' => 'Ain El Turk',
            'baladia' => 'Oran',
        ], '0550000002');

        $this->assertSame('Fatima', $payload['prenomwali']['fr']);
        $this->assertSame('بن', $payload['nomwali']['ar']);
        $this->assertSame('mother', $payload['relationetudiant']);
        $this->assertSame('Address', $payload['adressewali']);
        $this->assertSame('0550000002', $payload['numtelephonewali']);
    }
}
