<?php

namespace Tests\Feature\Refactor;

use App\Models\Inscription\StudentInfo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentUpdateActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_update_flow_updates_student_and_guardian_entities(): void
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

        $oldSectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S1', 'ar' => 'ف1', 'en' => 'S1']),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $newSectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S2', 'ar' => 'ف2', 'en' => 'S2']),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentUser = User::factory()->create([
            'school_id' => $schoolId,
            'must_change_password' => false,
        ]);
        $parentUser = User::factory()->create([
            'school_id' => $schoolId,
            'must_change_password' => false,
        ]);

        $parentId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'OldParent', 'ar' => 'ولي قديم', 'en' => 'OldParent']),
            'nomwali' => json_encode(['fr' => 'OldLast', 'ar' => 'لقب قديم', 'en' => 'OldLast']),
            'relationetudiant' => 'father',
            'adressewali' => 'Old Address',
            'wilayawali' => 'Old Wilaya',
            'dayrawali' => 'Old Dayra',
            'baladiawali' => 'Old Baladia',
            'numtelephonewali' => 550000011,
            'user_id' => $parentUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentId = DB::table('studentinfos')->insertGetId([
            'user_id' => $studentUser->id,
            'section_id' => $oldSectionId,
            'parent_id' => $parentId,
            'gender' => 1,
            'prenom' => json_encode(['fr' => 'OldStudent', 'ar' => 'تلميذ قديم', 'en' => 'OldStudent']),
            'nom' => json_encode(['fr' => 'OldStudentLast', 'ar' => 'لقب قديم', 'en' => 'OldStudentLast']),
            'lieunaissance' => 'Old City',
            'wilaya' => 'Old Wilaya',
            'dayra' => 'Old Dayra',
            'baladia' => 'Old Baladia',
            'datenaissance' => '2012-01-01',
            'numtelephone' => 550000010,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->put(route('Students.update', ['Student' => $studentId]), [
            'section_id' => $newSectionId,
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'prenomfr' => 'NewStudent',
            'prenomar' => 'تلميذ جديد',
            'nomfr' => 'NewLast',
            'nomar' => 'لقب جديد',
            'email' => 'student.new@example.test',
            'gender' => 0,
            'numtelephone' => '0550000123',
            'datenaissance' => '2013-02-02',
            'lieunaissance' => 'New City',
            'wilaya' => 'New Wilaya',
            'dayra' => 'New Dayra',
            'baladia' => 'New Baladia',
            'relationetudiant' => 'mother',
            'adressewali' => 'New Address',
            'numtelephonewali' => '0550000456',
            'emailwali' => 'parent.new@example.test',
            'wilayawali' => 'New Wilaya',
            'dayrawali' => 'New Dayra',
            'baladiawali' => 'New Baladia',
            'prenomfrwali' => 'NewParent',
            'prenomarwali' => 'ولي جديد',
            'nomfrwali' => 'NewParentLast',
            'nomarwali' => 'لقب ولي جديد',
        ]);

        $response->assertStatus(302);

        $student = StudentInfo::query()->with(['user', 'parent.user'])->findOrFail($studentId);

        $this->assertSame($newSectionId, $student->section_id);
        $this->assertSame('NewStudent', $student->getTranslation('prenom', 'fr'));
        $this->assertSame('تلميذ جديد', $student->getTranslation('prenom', 'ar'));
        $this->assertSame('NewLast', $student->getTranslation('nom', 'fr'));
        $this->assertSame(0, (int) $student->gender);
        $this->assertSame('550000123', (string) $student->numtelephone);

        $this->assertNotNull($student->user);
        $this->assertSame('student.new@example.test', $student->user->email);
        $this->assertSame('NewStudent', $student->user->getTranslation('name', 'fr'));
        $this->assertSame($schoolId, (int) $student->user->school_id);

        $this->assertNotNull($student->parent);
        $this->assertSame('NewParent', $student->parent->getTranslation('prenomwali', 'fr'));
        $this->assertSame('mother', $student->parent->relationetudiant);
        $this->assertSame('550000456', (string) $student->parent->numtelephonewali);

        $this->assertNotNull($student->parent->user);
        $this->assertSame('parent.new@example.test', $student->parent->user->email);
        $this->assertSame('NewParent', $student->parent->user->getTranslation('name', 'fr'));
        $this->assertSame($schoolId, (int) $student->parent->user->school_id);
    }
}
