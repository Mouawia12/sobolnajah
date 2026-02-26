<?php

namespace Tests\Feature\Performance;

use App\Http\Controllers\AgendaScolaire\ExamesController;
use App\Http\Controllers\AgendaScolaire\AgendaController;
use App\Http\Controllers\AgendaScolaire\GradeController;
use App\Http\Controllers\AgendaScolaire\NoteStudentController;
use App\Http\Controllers\Promotion\GraduatedController;
use App\Models\AgendaScolaire\Agenda;
use App\Models\AgendaScolaire\Exames;
use App\Models\AgendaScolaire\Grade;
use App\Models\AgendaScolaire\NoteStudent;
use App\Models\Inscription\StudentInfo;
use App\Models\Role;
use App\Models\Specialization\Specialization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminLegacyIndexFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_graduated_index_filters_soft_deleted_students_with_school_scope(): void
    {
        [$admin, $schoolA] = $this->bootstrapAdmin();
        [, $studentA] = $this->createStudentInSchool($schoolA, 'Ali');
        [$schoolB, $studentB] = $this->createStudentInSchool(null, 'Sara');

        StudentInfo::query()->findOrFail($studentA)->delete();
        StudentInfo::query()->findOrFail($studentB)->delete();

        $this->be($admin);
        $sectionId = DB::table('studentinfos')->where('id', $studentA)->value('section_id');
        $this->app->instance('request', Request::create('/graduated', 'GET', [
            'q' => 'Ali',
            'section_id' => $sectionId,
        ]));

        $response = app(GraduatedController::class)->index();
        $students = $response->getData()['StudentInfo'];

        $this->assertSame(1, $students->total());
        $this->assertSame($schoolA, $students->first()->section->school_id);
        $this->assertNotEquals($schoolB, $students->first()->section->school_id);
    }

    public function test_exames_index_supports_filters_and_school_scope_for_admin(): void
    {
        [$admin, $schoolA] = $this->bootstrapAdmin();
        [$schoolB] = $this->createSchoolWithClassroom('B');

        [$gradeA, $classroomA] = $this->createGradeAndClassroom($schoolA, 'A');
        [$gradeB, $classroomB] = $this->createGradeAndClassroom($schoolB, 'B');

        $specA = Specialization::query()->create([
            'name' => ['ar' => 'رياضيات', 'fr' => 'Math', 'en' => 'Math'],
        ]);
        $specB = Specialization::query()->create([
            'name' => ['ar' => 'فيزياء', 'fr' => 'Physics', 'en' => 'Physics'],
        ]);

        Exames::query()->create([
            'name' => ['ar' => 'اختبار علي', 'fr' => 'Ali Exam', 'en' => 'Ali Exam'],
            'file' => 'private/exames/test-a.pdf',
            'specialization_id' => $specA->id,
            'grade_id' => $gradeA,
            'classroom_id' => $classroomA,
            'Annscolaire' => '2026',
        ]);

        Exames::query()->create([
            'name' => ['ar' => 'اختبار خارجي', 'fr' => 'External Exam', 'en' => 'External Exam'],
            'file' => 'private/exames/test-b.pdf',
            'specialization_id' => $specB->id,
            'grade_id' => $gradeB,
            'classroom_id' => $classroomB,
            'Annscolaire' => '2026',
        ]);

        $this->be($admin);
        $this->app->instance('request', Request::create('/Exames', 'GET', [
            'q' => 'Ali',
            'grade_id' => $gradeA,
            'specialization_id' => $specA->id,
            'Annscolaire' => '2026',
        ]));

        $response = app(ExamesController::class)->index();
        $exames = $response->getData()['Exames'];

        $this->assertSame(1, $exames->total());
        $this->assertSame($gradeA, $exames->first()->grade_id);
        $this->assertSame($classroomA, $exames->first()->classroom_id);
    }

    public function test_exames_index_is_paginated_for_public_view(): void
    {
        [$schoolA] = $this->createSchoolWithClassroom('P');
        [$gradeA, $classroomA] = $this->createGradeAndClassroom($schoolA, 'P');
        $spec = Specialization::query()->create([
            'name' => ['ar' => 'لغة', 'fr' => 'Langue', 'en' => 'Language'],
        ]);

        for ($i = 1; $i <= 25; $i++) {
            Exames::query()->create([
                'name' => ['ar' => 'امتحان ' . $i, 'fr' => 'Exam ' . $i, 'en' => 'Exam ' . $i],
                'file' => 'private/exames/public-' . $i . '.pdf',
                'specialization_id' => $spec->id,
                'grade_id' => $gradeA,
                'classroom_id' => $classroomA,
                'Annscolaire' => '2026',
            ]);
        }

        $this->app->instance('request', Request::create('/Exames', 'GET'));
        $response = app(ExamesController::class)->index();
        $exames = $response->getData()['Exames'];

        $this->assertSame('front-end.exam', $response->getName());
        $this->assertSame(25, $exames->total());
        $this->assertCount(20, $exames->items());
    }

    public function test_grade_index_supports_search_and_pagination(): void
    {
        [$admin] = $this->bootstrapAdmin();

        for ($i = 1; $i <= 23; $i++) {
            Grade::query()->create([
                'name_grades' => ['ar' => 'مستوى ' . $i, 'fr' => 'Grade ' . $i, 'en' => 'Grade ' . $i],
            ]);
        }

        $this->be($admin);
        $this->app->instance('request', Request::create('/Grades', 'GET', ['q' => 'Grade 2']));
        $filteredResponse = app(GradeController::class)->index();
        $filtered = $filteredResponse->getData()['Grade'];
        $this->assertTrue($filtered->total() >= 1);

        $this->app->instance('request', Request::create('/Grades', 'GET'));
        $paginatedResponse = app(GradeController::class)->index();
        $paginated = $paginatedResponse->getData()['Grade'];
        $this->assertSame(23, $paginated->total());
        $this->assertCount(20, $paginated->items());
    }

    public function test_agenda_index_supports_search_and_pagination(): void
    {
        [$admin] = $this->bootstrapAdmin();

        for ($i = 1; $i <= 22; $i++) {
            Agenda::query()->create([
                'name_agenda' => ['ar' => 'أجندة ' . $i, 'fr' => 'Agenda ' . $i, 'en' => 'Agenda ' . $i],
            ]);
        }

        $this->be($admin);
        $this->app->instance('request', Request::create('/Agendas', 'GET', ['q' => 'Agenda 1']));
        $filteredResponse = app(AgendaController::class)->index();
        $filtered = $filteredResponse->getData()['Agenda'];
        $this->assertTrue($filtered->total() >= 1);

        $this->app->instance('request', Request::create('/Agendas', 'GET'));
        $paginatedResponse = app(AgendaController::class)->index();
        $paginated = $paginatedResponse->getData()['Agenda'];
        $this->assertSame(22, $paginated->total());
        $this->assertCount(20, $paginated->items());
    }

    public function test_note_students_show_supports_filters_and_pagination_with_school_scope(): void
    {
        [$admin, $schoolA] = $this->bootstrapAdmin();
        [$schoolB] = $this->createSchoolWithClassroom('D');
        [, $studentA] = $this->createStudentInSchool($schoolA, 'Nadir');
        [, $studentB] = $this->createStudentInSchool($schoolB, 'Outside');

        $noteA = new NoteStudent();
        $noteA->student_id = $studentA;
        $noteA->urlfile1 = 'private-file-a.pdf';
        $noteA->save();

        $noteB = new NoteStudent();
        $noteB->student_id = $studentB;
        $noteB->urlfile1 = 'private-file-b.pdf';
        $noteB->save();

        $sectionId = (int) DB::table('studentinfos')->where('id', $studentA)->value('section_id');
        $this->be($admin);
        $this->app->instance('request', Request::create('/NoteStudents/' . $sectionId, 'GET', [
            'q' => 'Nadir',
            'has_notes' => '1',
        ]));

        $response = app(NoteStudentController::class)->show($sectionId);
        $students = $response->getData()['StudentInfo'];

        $this->assertSame(1, $students->total());
        $this->assertSame($schoolA, $students->first()->section->school_id);
        $this->assertNotNull($students->first()->noteStudent);
    }

    private function bootstrapAdmin(): array
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        [$schoolId] = $this->createSchoolWithClassroom('A');
        $admin->update(['school_id' => $schoolId]);

        return [$admin, $schoolId];
    }

    private function createSchoolWithClassroom(string $suffix): array
    {
        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School ' . $suffix, 'ar' => 'School ' . $suffix, 'en' => 'School ' . $suffix]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        [$gradeId, $classroomId] = $this->createGradeAndClassroom($schoolId, $suffix);

        return [$schoolId, $gradeId, $classroomId];
    }

    private function createGradeAndClassroom(int $schoolId, string $suffix): array
    {
        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolId,
            'name_grade' => json_encode(['fr' => 'G' . $suffix, 'ar' => 'G' . $suffix, 'en' => 'G' . $suffix]),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'N', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C' . $suffix, 'ar' => 'C' . $suffix, 'en' => 'C' . $suffix]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$gradeId, $classroomId];
    }

    private function createStudentInSchool(?int $schoolId, string $firstName): array
    {
        if (!$schoolId) {
            [$schoolId] = $this->createSchoolWithClassroom($firstName);
        }

        [$gradeId, $classroomId] = $this->createGradeAndClassroom($schoolId, $firstName);

        $sectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S' . $firstName, 'ar' => 'S' . $firstName, 'en' => 'S' . $firstName]),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $parentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'Parent ' . $firstName, 'ar' => 'Parent ' . $firstName, 'en' => 'Parent ' . $firstName]),
            'email' => 'parent-' . strtolower($firstName) . '-' . uniqid() . '@example.test',
            'password' => bcrypt('Secret123!'),
            'school_id' => $schoolId,
            'must_change_password' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $parentId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'Parent', 'ar' => 'Parent', 'en' => 'Parent']),
            'nomwali' => json_encode(['fr' => $firstName, 'ar' => $firstName, 'en' => $firstName]),
            'relationetudiant' => json_encode(['fr' => 'Father', 'ar' => 'Father', 'en' => 'Father']),
            'adressewali' => json_encode(['fr' => 'Address', 'ar' => 'Address', 'en' => 'Address']),
            'wilayawali' => json_encode(['fr' => 'Wilaya', 'ar' => 'Wilaya', 'en' => 'Wilaya']),
            'dayrawali' => json_encode(['fr' => 'Dayra', 'ar' => 'Dayra', 'en' => 'Dayra']),
            'baladiawali' => json_encode(['fr' => 'Baladia', 'ar' => 'Baladia', 'en' => 'Baladia']),
            'numtelephonewali' => 551100000 + random_int(1, 99),
            'user_id' => $parentUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => $firstName . ' Student', 'ar' => $firstName . ' Student', 'en' => $firstName . ' Student']),
            'email' => 'student-' . strtolower($firstName) . '-' . uniqid() . '@example.test',
            'password' => bcrypt('Secret123!'),
            'school_id' => $schoolId,
            'must_change_password' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentId = DB::table('studentinfos')->insertGetId([
            'user_id' => $studentUserId,
            'section_id' => $sectionId,
            'parent_id' => $parentId,
            'prenom' => json_encode(['fr' => $firstName, 'ar' => $firstName, 'en' => $firstName]),
            'nom' => json_encode(['fr' => 'Student', 'ar' => 'Student', 'en' => 'Student']),
            'gender' => 1,
            'lieunaissance' => json_encode(['fr' => 'City', 'ar' => 'City', 'en' => 'City']),
            'wilaya' => json_encode(['fr' => 'Wilaya', 'ar' => 'Wilaya', 'en' => 'Wilaya']),
            'dayra' => json_encode(['fr' => 'Dayra', 'ar' => 'Dayra', 'en' => 'Dayra']),
            'baladia' => json_encode(['fr' => 'Baladia', 'ar' => 'Baladia', 'en' => 'Baladia']),
            'datenaissance' => '2012-01-01',
            'numtelephone' => 552200000 + random_int(1, 99),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$schoolId, $studentId];
    }
}
