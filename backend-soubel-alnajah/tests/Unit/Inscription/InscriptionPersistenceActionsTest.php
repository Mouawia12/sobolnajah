<?php

namespace Tests\Unit\Inscription;

use App\Actions\Inscription\CreateInscriptionAction;
use App\Actions\Inscription\UpdateInscriptionAction;
use App\Models\Inscription\Inscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class InscriptionPersistenceActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_inscription_action_persists_new_record(): void
    {
        [$schoolId, $gradeId, $classroomId] = $this->bootstrapAcademicTree();
        $uuid = Str::uuid()->toString();

        $payload = [
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'inscriptionetat' => 'new',
            'nomecoleprecedente' => 'Old School',
            'dernieresection' => 'A1',
            'moyensannuels' => 13.0,
            'numeronationaletudiant' => 100100100,
            'prenom' => ['fr' => 'Ali', 'ar' => 'علي', 'en' => 'Ali'],
            'nom' => ['fr' => 'Ben', 'ar' => 'بن', 'en' => 'Ben'],
            'email' => "create.{$uuid}@example.test",
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
            'prenomwali' => ['fr' => 'Walid', 'ar' => 'والد', 'en' => 'Walid'],
            'nomwali' => ['fr' => 'Ben', 'ar' => 'بن', 'en' => 'Ben'],
            'relationetudiant' => 'father',
            'adressewali' => 'Address',
            'numtelephonewali' => 550000222,
            'emailwali' => "wali.{$uuid}@example.test",
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'statu' => 'procec',
        ];

        $record = app(CreateInscriptionAction::class)->execute($payload);

        $this->assertInstanceOf(Inscription::class, $record);
        $this->assertDatabaseHas('inscriptions', [
            'id' => $record->id,
            'email' => "create.{$uuid}@example.test",
            'statu' => 'procec',
        ]);
    }

    public function test_update_inscription_action_updates_existing_record(): void
    {
        [$schoolId, $gradeId, $classroomId] = $this->bootstrapAcademicTree();
        $inscriptionId = $this->createInscription($schoolId, $gradeId, $classroomId);
        $inscription = Inscription::query()->findOrFail($inscriptionId);

        app(UpdateInscriptionAction::class)->execute($inscription, [
            'statu' => 'accept',
            'adresseactuelle' => 'Updated Address',
        ]);

        $this->assertDatabaseHas('inscriptions', [
            'id' => $inscriptionId,
            'statu' => 'accept',
            'adresseactuelle' => 'Updated Address',
        ]);
    }

    private function bootstrapAcademicTree(): array
    {
        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School A', 'ar' => 'مدرسة أ', 'en' => 'School A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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

        return [$schoolId, $gradeId, $classroomId];
    }

    private function createInscription(int $schoolId, int $gradeId, int $classroomId): int
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
            'numeronationaletudiant' => 2000000 + random_int(1, 999999),
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
            'statu' => 'procec',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
