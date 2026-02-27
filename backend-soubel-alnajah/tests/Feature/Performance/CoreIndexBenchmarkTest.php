<?php

namespace Tests\Feature\Performance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CoreIndexBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    public function test_inscriptions_admin_query_is_significantly_faster_with_index(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Benchmark requires MySQL index hints.');
        }

        [$targetSchoolId, $targetStatus] = $this->seedInscriptionsBenchmarkDataset();

        $baseSql = 'SELECT id FROM inscriptions WHERE school_id = ? AND statu = ? ORDER BY created_at DESC LIMIT 20';
        $ignoredSql = str_replace('FROM inscriptions', 'FROM inscriptions IGNORE INDEX (idx_inscriptions_school_status_created)', $baseSql);
        $forcedSql = str_replace('FROM inscriptions', 'FROM inscriptions FORCE INDEX (idx_inscriptions_school_status_created)', $baseSql);

        // Warm-up to avoid one-time setup noise.
        DB::select($ignoredSql, [$targetSchoolId, $targetStatus]);
        DB::select($forcedSql, [$targetSchoolId, $targetStatus]);

        $ignoredMs = $this->averageExecutionTimeMs($ignoredSql, [$targetSchoolId, $targetStatus], 8);
        $forcedMs = $this->averageExecutionTimeMs($forcedSql, [$targetSchoolId, $targetStatus], 8);

        $this->assertLessThan(
            $ignoredMs * 0.60,
            $forcedMs,
            sprintf('Expected indexed query to be >=40%% faster. ignore=%.3fms force=%.3fms', $ignoredMs, $forcedMs)
        );
    }

    public function test_notifications_admin_query_is_significantly_faster_with_index(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Benchmark requires MySQL index hints.');
        }

        $targetNotifiableId = $this->seedNotificationsBenchmarkDataset();

        $baseSql = 'SELECT id FROM notifications WHERE notifiable_id = ? ORDER BY created_at DESC LIMIT 20';
        $ignoredSql = str_replace(
            'FROM notifications',
            'FROM notifications IGNORE INDEX (idx_notifications_notifiable_created, idx_notifications_notifiable_read)',
            $baseSql
        );
        $forcedSql = str_replace(
            'FROM notifications',
            'FROM notifications FORCE INDEX (idx_notifications_notifiable_created)',
            $baseSql
        );

        // Warm-up to avoid one-time setup noise.
        DB::select($ignoredSql, [$targetNotifiableId]);
        DB::select($forcedSql, [$targetNotifiableId]);

        $ignoredMs = $this->averageExecutionTimeMs($ignoredSql, [$targetNotifiableId], 10);
        $forcedMs = $this->averageExecutionTimeMs($forcedSql, [$targetNotifiableId], 10);

        $this->assertLessThan(
            $ignoredMs * 0.60,
            $forcedMs,
            sprintf('Expected indexed notifications query to be >=40%% faster. ignore=%.3fms force=%.3fms', $ignoredMs, $forcedMs)
        );
    }

    private function averageExecutionTimeMs(string $sql, array $bindings, int $runs): float
    {
        $totalMs = 0.0;

        for ($i = 0; $i < $runs; $i++) {
            $start = hrtime(true);
            DB::select($sql, $bindings);
            $elapsedNs = hrtime(true) - $start;
            $totalMs += $elapsedNs / 1_000_000;
        }

        return $totalMs / $runs;
    }

    private function seedInscriptionsBenchmarkDataset(): array
    {
        $targetSchoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'Target', 'ar' => 'هدف', 'en' => 'Target']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $otherSchoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'Other', 'ar' => 'آخر', 'en' => 'Other']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $targetGradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $targetSchoolId,
            'name_grade' => json_encode(['fr' => 'G1', 'ar' => 'م1', 'en' => 'G1']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $otherGradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $otherSchoolId,
            'name_grade' => json_encode(['fr' => 'G2', 'ar' => 'م2', 'en' => 'G2']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $targetClassroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $targetSchoolId,
            'grade_id' => $targetGradeId,
            'name_class' => json_encode(['fr' => 'C1', 'ar' => 'ق1', 'en' => 'C1']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $otherClassroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $otherSchoolId,
            'grade_id' => $otherGradeId,
            'name_class' => json_encode(['fr' => 'C2', 'ar' => 'ق2', 'en' => 'C2']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $targetStatus = 'procec';
        $rows = [];

        for ($i = 1; $i <= 6000; $i++) {
            $isTarget = $i <= 2400;
            $schoolId = $isTarget ? $targetSchoolId : $otherSchoolId;
            $gradeId = $isTarget ? $targetGradeId : $otherGradeId;
            $classroomId = $isTarget ? $targetClassroomId : $otherClassroomId;

            $rows[] = [
                'school_id' => $schoolId,
                'grade_id' => $gradeId,
                'classroom_id' => $classroomId,
                'inscriptionetat' => 'etat',
                'nomecoleprecedente' => 'ecole',
                'dernieresection' => 'section',
                'moyensannuels' => 10.5,
                'numeronationaletudiant' => 100000000 + $i,
                'prenom' => 'Student ' . $i,
                'nom' => 'Bench ' . $i,
                'email' => 'bench-' . $i . '@example.test',
                'gender' => 1,
                'numtelephone' => 550000000 + $i,
                'datenaissance' => '2012-01-01',
                'lieunaissance' => 'City',
                'wilaya' => 'Wilaya',
                'dayra' => 'Dayra',
                'baladia' => 'Baladia',
                'adresseactuelle' => 'Address',
                'codepostal' => 10000,
                'residenceactuelle' => 'Residence',
                'etatsante' => 'Good',
                'identificationmaladie' => 'None',
                'alfdlprsaldr' => 'note',
                'autresnotes' => null,
                'prenomwali' => 'Wali',
                'nomwali' => 'Guardian',
                'relationetudiant' => 'father',
                'adressewali' => 'W Address',
                'numtelephonewali' => 560000000 + $i,
                'emailwali' => 'guardian-' . $i . '@example.test',
                'wilayawali' => 'Wilaya',
                'dayrawali' => 'Dayra',
                'baladiawali' => 'Baladia',
                'statu' => $isTarget ? $targetStatus : 'accept',
                'created_at' => now()->subSeconds(6000 - $i),
                'updated_at' => now()->subSeconds(6000 - $i),
            ];

            if (count($rows) === 500) {
                DB::table('inscriptions')->insert($rows);
                $rows = [];
            }
        }

        if (!empty($rows)) {
            DB::table('inscriptions')->insert($rows);
        }

        return [$targetSchoolId, $targetStatus];
    }

    private function seedNotificationsBenchmarkDataset(): int
    {
        $targetNotifiableId = 4001;
        $otherNotifiableId = 9001;
        $rows = [];

        for ($i = 1; $i <= 12000; $i++) {
            $isTarget = $i <= 5200;
            $notifiableId = $isTarget ? $targetNotifiableId : $otherNotifiableId;

            $rows[] = [
                'id' => (string) Str::uuid(),
                'type' => \App\Notifications\StudentSchoolCertificateNotification::class,
                'notifiable_type' => \App\Models\User::class,
                'notifiable_id' => $notifiableId,
                'data' => json_encode([
                    'namefr' => 'Bench',
                    'namear' => 'اختبار',
                    'email' => 'bench-' . $i . '@example.test',
                    'year' => '2026',
                ]),
                'read_at' => $i % 3 === 0 ? now()->subMinutes($i % 120) : null,
                'created_at' => now()->subSeconds(12000 - $i),
                'updated_at' => now()->subSeconds(12000 - $i),
            ];

            if (count($rows) === 500) {
                DB::table('notifications')->insert($rows);
                $rows = [];
            }
        }

        if (!empty($rows)) {
            DB::table('notifications')->insert($rows);
        }

        return $targetNotifiableId;
    }
}
