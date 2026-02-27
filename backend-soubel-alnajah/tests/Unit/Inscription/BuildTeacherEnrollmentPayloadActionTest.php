<?php

namespace Tests\Unit\Inscription;

use App\Actions\Inscription\BuildLocalizedNameAction;
use App\Actions\Inscription\BuildTeacherEnrollmentPayloadAction;
use Tests\TestCase;

class BuildTeacherEnrollmentPayloadActionTest extends TestCase
{
    public function test_it_builds_user_and_teacher_payload_from_input(): void
    {
        $action = new BuildTeacherEnrollmentPayloadAction(new BuildLocalizedNameAction());

        $payload = $action->execute([
            'name_teacherfr' => 'Teacher FR',
            'name_teacherar' => 'معلم',
            'email' => 'teacher@example.test',
            'specialization_id' => 5,
            'gender' => 1,
            'joining_date' => '2026-02-26',
            'address' => 'Some Street',
        ]);

        $this->assertSame([
            'fr' => 'Teacher FR',
            'ar' => 'معلم',
            'en' => 'Teacher FR',
        ], $payload['user']['name']);
        $this->assertSame('teacher@example.test', $payload['user']['email']);
        $this->assertSame(5, $payload['teacher']['specialization_id']);
        $this->assertSame(1, $payload['teacher']['gender']);
        $this->assertSame('2026-02-26', $payload['teacher']['joining_date']);
        $this->assertSame('Some Street', $payload['teacher']['address']);
        $this->assertSame($payload['user']['name'], $payload['teacher']['name']);
    }
}
