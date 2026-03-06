<?php

namespace Tests\Feature\Portal;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class StudentGuardianPortalAccessTest extends TestCase
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

        Role::firstOrCreate(['name' => 'student']);
        Role::firstOrCreate(['name' => 'guardian']);
        Role::firstOrCreate(['name' => 'admin']);
    }

    public function test_student_and_guardian_can_access_chat_page(): void
    {
        [$schoolId, $sectionId] = $this->bootstrapSchoolAndSection();

        ['studentUser' => $studentUser, 'guardianUser' => $guardianUser] = $this->createStudentWithGuardian(
            $schoolId,
            $sectionId,
            'student-chat'
        );

        $this->actingAs($studentUser)
            ->get(route('Chats.index'))
            ->assertStatus(200);

        $this->actingAs($guardianUser)
            ->get(route('Chats.index'))
            ->assertStatus(200);
    }

    public function test_student_can_view_reports_page_and_download_own_file(): void
    {
        Storage::fake('local');

        [$schoolId, $sectionId] = $this->bootstrapSchoolAndSection();

        ['studentUser' => $studentUser, 'studentId' => $studentId] = $this->createStudentWithGuardian(
            $schoolId,
            $sectionId,
            'student-reports'
        );

        DB::table('note_students')->insert([
            'student_id' => $studentId,
            'urlfile1' => 'student-report.pdf',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Storage::disk('local')->put('private/notes/student-report.pdf', 'fake-pdf-content');

        $this->actingAs($studentUser)
            ->get(route('reports.index'))
            ->assertStatus(200)
            ->assertSee(route('DownloadNoteFromAdmin', ['url' => 'student-report.pdf']), false);

        $this->actingAs($studentUser)
            ->get(route('DownloadNoteFromAdmin', ['url' => 'student-report.pdf']))
            ->assertStatus(200);
    }

    public function test_guardian_can_view_children_reports_on_reports_page(): void
    {
        Storage::fake('local');

        [$schoolId, $sectionId] = $this->bootstrapSchoolAndSection();

        $guardianUser = User::factory()->create([
            'school_id' => $schoolId,
            'must_change_password' => false,
            'email' => 'guardian.shared@example.test',
        ]);
        $guardianUser->attachRole('guardian');

        $guardianId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'Shared', 'ar' => 'مشترك', 'en' => 'Shared']),
            'nomwali' => json_encode(['fr' => 'Guardian', 'ar' => 'ولي', 'en' => 'Guardian']),
            'relationetudiant' => 'mother',
            'adressewali' => 'Address',
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'numtelephonewali' => 550000020,
            'user_id' => $guardianUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentUserA = User::factory()->create([
            'school_id' => $schoolId,
            'must_change_password' => false,
            'email' => 'child.a@example.test',
        ]);
        $studentUserA->attachRole('student');

        $studentUserB = User::factory()->create([
            'school_id' => $schoolId,
            'must_change_password' => false,
            'email' => 'child.b@example.test',
        ]);
        $studentUserB->attachRole('student');

        $studentIdA = DB::table('studentinfos')->insertGetId([
            'user_id' => $studentUserA->id,
            'section_id' => $sectionId,
            'parent_id' => $guardianId,
            'gender' => 1,
            'prenom' => json_encode(['fr' => 'ChildA', 'ar' => 'طفل أ', 'en' => 'ChildA']),
            'nom' => json_encode(['fr' => 'One', 'ar' => 'واحد', 'en' => 'One']),
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'datenaissance' => '2012-01-01',
            'numtelephone' => 550000021,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('studentinfos')->insertGetId([
            'user_id' => $studentUserB->id,
            'section_id' => $sectionId,
            'parent_id' => $guardianId,
            'gender' => 0,
            'prenom' => json_encode(['fr' => 'ChildB', 'ar' => 'طفل ب', 'en' => 'ChildB']),
            'nom' => json_encode(['fr' => 'Two', 'ar' => 'اثنان', 'en' => 'Two']),
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'datenaissance' => '2013-01-01',
            'numtelephone' => 550000022,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('note_students')->insert([
            'student_id' => $studentIdA,
            'urlfile1' => 'guardian-child-a.pdf',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Storage::disk('local')->put('private/notes/guardian-child-a.pdf', 'fake-pdf-content');

        $this->actingAs($guardianUser)
            ->get(route('reports.index'))
            ->assertStatus(200)
            ->assertSee('ChildA')
            ->assertSee('ChildB')
            ->assertSee(route('DownloadNoteFromAdmin', ['url' => 'guardian-child-a.pdf']), false);
    }

    public function test_guardian_cannot_download_report_for_unrelated_student(): void
    {
        Storage::fake('local');

        [$schoolId, $sectionId] = $this->bootstrapSchoolAndSection();

        ['guardianUser' => $authorizedGuardian] = $this->createStudentWithGuardian($schoolId, $sectionId, 'authorized');

        ['studentId' => $otherStudentId] = $this->createStudentWithGuardian($schoolId, $sectionId, 'other');

        DB::table('note_students')->insert([
            'student_id' => $otherStudentId,
            'urlfile1' => 'foreign-note.pdf',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Storage::disk('local')->put('private/notes/foreign-note.pdf', 'fake-pdf-content');

        $this->actingAs($authorizedGuardian)
            ->get(route('DownloadNoteFromAdmin', ['url' => 'foreign-note.pdf']))
            ->assertStatus(403);
    }

    private function bootstrapSchoolAndSection(): array
    {
        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School Portal', 'ar' => 'مدرسة البوابة', 'en' => 'School Portal']),
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

        $sectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S1', 'ar' => 'ف1', 'en' => 'S1']),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$schoolId, $sectionId];
    }

    private function createStudentWithGuardian(int $schoolId, int $sectionId, string $token): array
    {
        $studentUser = User::factory()->create([
            'school_id' => $schoolId,
            'must_change_password' => false,
            'email' => "student-{$token}@example.test",
        ]);
        $studentUser->attachRole('student');

        $guardianUser = User::factory()->create([
            'school_id' => $schoolId,
            'must_change_password' => false,
            'email' => "guardian-{$token}@example.test",
        ]);
        $guardianUser->attachRole('guardian');

        $guardianId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'Guardian', 'ar' => 'ولي', 'en' => 'Guardian']),
            'nomwali' => json_encode(['fr' => strtoupper($token), 'ar' => 'لقب', 'en' => strtoupper($token)]),
            'relationetudiant' => 'father',
            'adressewali' => 'Address',
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'numtelephonewali' => 550000030,
            'user_id' => $guardianUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentId = DB::table('studentinfos')->insertGetId([
            'user_id' => $studentUser->id,
            'section_id' => $sectionId,
            'parent_id' => $guardianId,
            'gender' => 1,
            'prenom' => json_encode(['fr' => 'Student', 'ar' => 'تلميذ', 'en' => 'Student']),
            'nom' => json_encode(['fr' => strtoupper($token), 'ar' => 'لقب', 'en' => strtoupper($token)]),
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'datenaissance' => '2012-01-01',
            'numtelephone' => 550000031,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'studentUser' => $studentUser,
            'guardianUser' => $guardianUser,
            'studentId' => $studentId,
            'guardianId' => $guardianId,
        ];
    }
}
