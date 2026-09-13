<?php

namespace Tests\Feature\Print;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class MorePrintTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            LocaleSessionRedirect::class,
            LocalizationRedirect::class,
            LocaleViewPath::class,
        ]);

        foreach (['admin', 'accountant'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }

    public function test_absences_list_print_renders(): void
    {
        [$admin, $schoolId, $sectionId] = $this->bootstrap('A');
        $studentId = $this->createStudent($schoolId, $sectionId);
        DB::table('absences')->insert([
            'student_id' => $studentId, 'date' => now()->toDateString(),
            'hour_1' => 0, 'hour_2' => 1, 'hour_3' => 2, 'hour_4' => 1, 'hour_5' => 1,
            'hour_6' => 1, 'hour_7' => 1, 'hour_8' => 1, 'hour_9' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('absences.print'));
        $response->assertStatus(200);
        $response->assertSee(trans('main_sidebar.Absences'));
    }

    public function test_payments_list_print_renders_with_total(): void
    {
        [$admin, $schoolId, $sectionId] = $this->bootstrap('A');
        $studentId = $this->createStudent($schoolId, $sectionId);
        $contractId = DB::table('student_contracts')->insertGetId([
            'school_id' => $schoolId, 'student_id' => $studentId, 'academic_year' => '2026-2027',
            'total_amount' => 1000, 'plan_type' => 'yearly', 'status' => 'active',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('payments')->insert([
            'school_id' => $schoolId, 'contract_id' => $contractId, 'receipt_number' => 'R-1',
            'paid_on' => now()->toDateString(), 'amount' => 250, 'payment_method' => 'cash',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('accounting.payments.print'));
        $response->assertStatus(200);
        $response->assertSee('250.00');
    }

    public function test_inscriptions_list_print_renders(): void
    {
        [$admin, $schoolId, $sectionId] = $this->bootstrap('A');
        $classroomId = (int) DB::table('sections')->where('id', $sectionId)->value('classroom_id');
        $gradeId = (int) DB::table('classrooms')->where('id', $classroomId)->value('grade_id');

        DB::table('inscriptions')->insert([
            'school_id' => $schoolId, 'grade_id' => $gradeId, 'classroom_id' => $classroomId,
            'inscriptionetat' => 'nouvelleinscription', 'nomecoleprecedente' => 'old', 'dernieresection' => 'old',
            'moyensannuels' => 12.5, 'numeronationaletudiant' => 100000000 + random_int(1, 99999),
            'prenom' => json_encode(['fr' => 'Nour', 'ar' => 'نور', 'en' => 'Nour']),
            'nom' => json_encode(['fr' => 'Ben', 'ar' => 'بن', 'en' => 'Ben']),
            'email' => 'ins-' . uniqid() . '@example.test', 'gender' => 1,
            'numtelephone' => 550001234, 'datenaissance' => '2012-01-01', 'lieunaissance' => 'City',
            'wilaya' => 'W', 'dayra' => 'D', 'baladia' => 'B', 'adresseactuelle' => 'A', 'codepostal' => 16000,
            'residenceactuelle' => 'R', 'etatsante' => 'good', 'identificationmaladie' => 'none',
            'alfdlprsaldr' => 'n', 'autresnotes' => null, 'prenomwali' => 'P', 'nomwali' => 'N',
            'relationetudiant' => 'father', 'adressewali' => 'A', 'numtelephonewali' => 551000000 + random_int(1, 999),
            'emailwali' => 'w-' . uniqid() . '@example.test', 'wilayawali' => 'W', 'dayrawali' => 'D',
            'baladiawali' => 'B', 'statu' => 'procec', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('Inscriptions.print'));
        $response->assertStatus(200);
        $response->assertSee('نور');
    }

    public function test_parents_list_print_renders(): void
    {
        [$admin, $schoolId] = $this->bootstrap('A');
        $parentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'P', 'ar' => 'و', 'en' => 'P']),
            'email' => 'pp-' . uniqid() . '@example.test', 'password' => bcrypt('x'),
            'school_id' => $schoolId, 'must_change_password' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('my_parents')->insert([
            'prenomwali' => json_encode(['fr' => 'Ali', 'ar' => 'علي', 'en' => 'Ali']),
            'nomwali' => json_encode(['fr' => 'Wali', 'ar' => 'ولي', 'en' => 'Wali']),
            'relationetudiant' => 'father', 'adressewali' => 'A', 'wilayawali' => 'W',
            'dayrawali' => 'D', 'baladiawali' => 'B', 'numtelephonewali' => 550001234,
            'user_id' => $parentUserId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('Parents.print'));
        $response->assertStatus(200);
        $response->assertSee('علي');
    }

    private function bootstrap(string $suffix): array
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        $admin->attachRole('admin');
        $admin->attachRole('accountant');

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'S' . $suffix, 'ar' => 'مدرسة ' . $suffix, 'en' => 'S' . $suffix]),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolId]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolId, 'name_grade' => json_encode(['fr' => 'G', 'ar' => 'م', 'en' => 'G']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolId, 'grade_id' => $gradeId, 'name_class' => json_encode(['fr' => 'C', 'ar' => 'ق', 'en' => 'C']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $sectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolId, 'grade_id' => $gradeId, 'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S', 'ar' => 'ف', 'en' => 'S']), 'Status' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return [$admin, $schoolId, $sectionId];
    }

    private function createStudent(int $schoolId, int $sectionId): int
    {
        $userId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'S', 'ar' => 'ت', 'en' => 'S']),
            'email' => 'st-' . uniqid() . '@example.test', 'password' => bcrypt('x'),
            'school_id' => $schoolId, 'must_change_password' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $parentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'P', 'ar' => 'و', 'en' => 'P']),
            'email' => 'pa-' . uniqid() . '@example.test', 'password' => bcrypt('x'),
            'school_id' => $schoolId, 'must_change_password' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $parentId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'P', 'ar' => 'و', 'en' => 'P']),
            'nomwali' => json_encode(['fr' => 'L', 'ar' => 'ل', 'en' => 'L']),
            'relationetudiant' => 'father', 'adressewali' => 'A', 'wilayawali' => 'W',
            'dayrawali' => 'D', 'baladiawali' => 'B', 'numtelephonewali' => 550000000 + random_int(1, 999),
            'user_id' => $parentUserId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return DB::table('studentinfos')->insertGetId([
            'user_id' => $userId, 'section_id' => $sectionId, 'parent_id' => $parentId, 'gender' => 1,
            'prenom' => json_encode(['fr' => 'Etu', 'ar' => 'تلميذ', 'en' => 'Etu']),
            'nom' => json_encode(['fr' => 'N', 'ar' => 'ل', 'en' => 'N']),
            'lieunaissance' => 'C', 'wilaya' => 'W', 'dayra' => 'D', 'baladia' => 'B',
            'datenaissance' => '2012-01-01', 'numtelephone' => 550000000 + random_int(1, 999),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
