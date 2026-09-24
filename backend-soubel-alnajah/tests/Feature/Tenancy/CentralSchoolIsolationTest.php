<?php

namespace Tests\Feature\Tenancy;

use App\Models\Role;
use App\Models\School\Schoolgrade;
use App\Models\School\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class CentralSchoolIsolationTest extends TestCase
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

        Role::firstOrCreate(['name' => 'admin']);
    }

    public function test_global_scope_filters_sections_for_branch_admin(): void
    {
        [$schoolA] = $this->makeSchoolWithSection('A');
        [$schoolB] = $this->makeSchoolWithSection('B');

        $branchAdminA = $this->branchAdmin($schoolA);

        // بلا استدعاء forSchool يدوياً: النطاق العالمي يقيّد تلقائياً بفرع المستخدم.
        $this->actingAs($branchAdminA);
        $this->assertSame(1, Section::query()->count());
        $this->assertSame(1, Schoolgrade::query()->count());
    }

    public function test_global_admin_sees_all_schools(): void
    {
        $this->makeSchoolWithSection('A');
        $this->makeSchoolWithSection('B');

        $globalAdmin = User::factory()->create(['must_change_password' => false, 'school_id' => null]);
        $globalAdmin->attachRole('admin');

        $this->actingAs($globalAdmin);
        $this->assertSame(2, Section::query()->count());
    }

    public function test_creating_autofills_school_id_for_branch_admin(): void
    {
        [$schoolA] = $this->makeSchoolWithSection('A');
        $branchAdminA = $this->branchAdmin($schoolA);

        $this->actingAs($branchAdminA);

        // إنشاء دون تمرير school_id => يُملأ تلقائياً بفرع المستخدم.
        $grade = Schoolgrade::create([
            'name_grade' => ['ar' => 'مرحلة', 'fr' => 'G', 'en' => 'G'],
            'notes' => ['ar' => 'ن', 'fr' => 'N', 'en' => 'N'],
        ]);

        $this->assertSame((int) $schoolA, (int) $grade->school_id);
    }

    public function test_across_schools_bypass_returns_all(): void
    {
        [$schoolA] = $this->makeSchoolWithSection('A');
        $this->makeSchoolWithSection('B');

        $branchAdminA = $this->branchAdmin($schoolA);
        $this->actingAs($branchAdminA);

        $this->assertSame(1, Section::query()->count());
        $this->assertSame(2, Section::acrossSchools()->count());
    }

    public function test_absence_today_is_school_isolated(): void
    {
        [$schoolA, $sectionA] = $this->makeSchoolWithSection('A');
        [$schoolB, $sectionB] = $this->makeSchoolWithSection('B');

        $studentA = $this->makeStudent($schoolA, $sectionA);
        $studentB = $this->makeStudent($schoolB, $sectionB);

        $branchAdminA = $this->branchAdmin($schoolA);

        // تلميذ خارج الفرع => 404 (لا كشف حضور عبر الفروع).
        $this->actingAs($branchAdminA)
            ->getJson(route('absence.today', ['student_id' => $studentB]))
            ->assertStatus(404);

        // تلميذ الفرع نفسه => 200.
        $this->actingAs($branchAdminA)
            ->getJson(route('absence.today', ['student_id' => $studentA]))
            ->assertStatus(200);
    }

    public function test_note_store_cannot_target_foreign_student(): void
    {
        Storage::fake('local');

        [$schoolA, $sectionA] = $this->makeSchoolWithSection('A');
        [$schoolB, $sectionB] = $this->makeSchoolWithSection('B');

        $studentB = $this->makeStudent($schoolB, $sectionB);
        $branchAdminA = $this->branchAdmin($schoolA);

        $response = $this->actingAs($branchAdminA)->post(route('NoteStudents.store'), [
            'student_id' => $studentB,
            'Anneescolaire' => '1',
            'note_file' => $this->fakePdf(),
        ]);

        $response->assertStatus(404);
        // الأهم: لم تُكتب أي بطاقة نقاط لتلميذ فرع آخر.
        $this->assertDatabaseMissing('note_students', ['student_id' => $studentB]);
    }

    /* ============================ أدوات ============================ */

    /** @return array{0:int,1:int} [schoolId, sectionId] */
    private function makeSchoolWithSection(string $suffix): array
    {
        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'S' . $suffix, 'ar' => 'مدرسة ' . $suffix, 'en' => 'S' . $suffix]),
            'created_at' => now(), 'updated_at' => now(),
        ]);
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

        return [$schoolId, $sectionId];
    }

    private function makeStudent(int $schoolId, int $sectionId): int
    {
        $studentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'St', 'ar' => 'تلميذ', 'en' => 'St']),
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
            'dayrawali' => 'D', 'baladiawali' => 'B', 'numtelephonewali' => 550000000 + random_int(1, 9999),
            'user_id' => $parentUserId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return DB::table('studentinfos')->insertGetId([
            'user_id' => $studentUserId, 'section_id' => $sectionId, 'parent_id' => $parentId, 'gender' => 1,
            'prenom' => json_encode(['fr' => 'St', 'ar' => 'تلميذ', 'en' => 'St']),
            'nom' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'lieunaissance' => 'C', 'wilaya' => 'W', 'dayra' => 'D', 'baladia' => 'B',
            'datenaissance' => '2012-01-01', 'numtelephone' => 550000000 + random_int(1, 9999),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function branchAdmin(int $schoolId): User
    {
        $admin = User::factory()->create(['must_change_password' => false, 'school_id' => $schoolId]);
        $admin->attachRole('admin');

        return $admin;
    }

    private function fakePdf(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'note-') . '.pdf';
        file_put_contents($path, "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF");

        return new UploadedFile($path, 'note.pdf', 'application/pdf', null, true);
    }
}
