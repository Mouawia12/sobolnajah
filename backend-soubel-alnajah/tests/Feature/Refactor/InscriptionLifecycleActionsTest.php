<?php

namespace Tests\Feature\Refactor;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class InscriptionLifecycleActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_status_updates_inscription_status_field(): void
    {
        [$admin, $schoolId, $gradeId, $classroomId] = $this->bootstrapSchoolAdminAndClassroom();
        $inscriptionId = $this->createInscription($schoolId, $gradeId, $classroomId, 'procec');

        $response = $this->actingAs($admin)->post(route('Inscriptions.status', ['id' => $inscriptionId]), [
            'statu' => 'accept',
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('inscriptions', [
            'id' => $inscriptionId,
            'statu' => 'accept',
        ]);
    }

    public function test_destroy_deletes_inscription_record(): void
    {
        [$admin, $schoolId, $gradeId, $classroomId] = $this->bootstrapSchoolAdminAndClassroom();
        $inscriptionId = $this->createInscription($schoolId, $gradeId, $classroomId, 'procec');

        $response = $this->actingAs($admin)->delete(route('Inscriptions.destroy', ['Inscription' => $inscriptionId]));

        $response->assertStatus(302);

        $this->assertDatabaseMissing('inscriptions', [
            'id' => $inscriptionId,
        ]);
    }

    private function bootstrapSchoolAdminAndClassroom(): array
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);

        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School A', 'ar' => 'مدرسة أ', 'en' => 'School A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolId]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolId,
            'name_grade' => json_encode(['fr' => 'G1', 'ar' => 'م1', 'en' => 'G1']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C1', 'ar' => 'ق1', 'en' => 'C1']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$admin, $schoolId, $gradeId, $classroomId];
    }

    private function createInscription(int $schoolId, int $gradeId, int $classroomId, string $status): int
    {
        $uuid = Str::uuid()->toString();

        return DB::table('inscriptions')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'inscriptionetat' => 'new',
            'nomecoleprecedente' => 'Old School',
            'dernieresection' => 'A1',
            'moyensannuels' => 12.5,
            'numeronationaletudiant' => 1000000 + random_int(1, 999999),
            'prenom' => json_encode(['fr' => 'Prenom', 'ar' => 'اسم', 'en' => 'Prenom']),
            'nom' => json_encode(['fr' => 'Nom', 'ar' => 'لقب', 'en' => 'Nom']),
            'email' => "student.{$uuid}@example.test",
            'gender' => 1,
            'numtelephone' => 550000111,
            'datenaissance' => '2012-01-01',
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'adresseactuelle' => 'Address',
            'codepostal' => 16000,
            'residenceactuelle' => 'Residence',
            'etatsante' => 'Good',
            'identificationmaladie' => 'None',
            'alfdlprsaldr' => 'Notes',
            'autresnotes' => null,
            'prenomwali' => json_encode(['fr' => 'Parent', 'ar' => 'ولي', 'en' => 'Parent']),
            'nomwali' => json_encode(['fr' => 'Last', 'ar' => 'لقب', 'en' => 'Last']),
            'relationetudiant' => 'father',
            'adressewali' => 'Address',
            'numtelephonewali' => 550000222,
            'emailwali' => "guardian.{$uuid}@example.test",
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'statu' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
