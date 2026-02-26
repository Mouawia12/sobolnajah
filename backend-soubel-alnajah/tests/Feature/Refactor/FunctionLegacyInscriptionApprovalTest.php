<?php

namespace Tests\Feature\Refactor;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class FunctionLegacyInscriptionApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_inscription_via_legacy_store_route_for_own_school(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'student']);
        Role::firstOrCreate(['name' => 'guardian']);
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

        DB::table('sections')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S1', 'ar' => 'ف1', 'en' => 'S1']),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $token = Str::lower(Str::random(6));
        $inscriptionId = DB::table('inscriptions')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'inscriptionetat' => 'new',
            'nomecoleprecedente' => 'Old School',
            'dernieresection' => 'A1',
            'moyensannuels' => 12.5,
            'numeronationaletudiant' => 9000000 + random_int(1, 999999),
            'prenom' => json_encode(['fr' => 'Student' . $token, 'ar' => 'تلميذ', 'en' => 'Student' . $token]),
            'nom' => json_encode(['fr' => 'Last' . $token, 'ar' => 'لقب', 'en' => 'Last' . $token]),
            'email' => "student.{$token}@example.test",
            'gender' => 1,
            'numtelephone' => 551000000 + random_int(1, 999),
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
            'prenomwali' => json_encode(['fr' => 'Guardian' . $token, 'ar' => 'ولي', 'en' => 'Guardian' . $token]),
            'nomwali' => json_encode(['fr' => 'Family' . $token, 'ar' => 'لقب', 'en' => 'Family' . $token]),
            'relationetudiant' => 'father',
            'adressewali' => 'Address',
            'numtelephonewali' => 552000000 + random_int(1, 999),
            'emailwali' => "guardian.{$token}@example.test",
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'statu' => 'procec',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post('/store/' . $inscriptionId);
        $response->assertStatus(302);

        $this->assertDatabaseHas('inscriptions', [
            'id' => $inscriptionId,
            'statu' => 'accept',
        ]);
        $this->assertDatabaseCount('studentinfos', 1);
    }
}
