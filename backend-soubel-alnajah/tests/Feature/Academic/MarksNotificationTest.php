<?php

namespace Tests\Feature\Academic;

use App\Models\Academic\Assessment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class MarksNotificationTest extends TestCase
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

        foreach (['admin', 'teacher', 'student', 'guardian'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }

    public function test_saving_marks_notifies_student_and_guardian_without_duplicates(): void
    {
        $ctx = $this->bootstrap();

        $assessment = Assessment::query()->create([
            'school_id' => $ctx['school_id'], 'teacher_id' => $ctx['teacher_id'], 'section_id' => $ctx['section_id'],
            'specialization_id' => $ctx['spec_id'], 'title' => 'الفرض الأول', 'type' => 'devoir',
            'max_mark' => 20, 'coefficient' => 1,
        ]);

        $teacherUser = User::query()->find($ctx['teacher_user_id']);

        // حفظ النقاط => إشعار التلميذ + الولي
        $this->actingAs($teacherUser)->post(route('assessments.marks.store', $assessment->id), [
            'mark' => [$ctx['student_id'] => 12],
        ])->assertStatus(302);

        $this->assertSame(1, DB::table('notifications')->where('notifiable_id', $ctx['student_user_id'])->count());
        $this->assertSame(1, DB::table('notifications')->where('notifiable_id', $ctx['guardian_user_id'])->count());

        // إعادة الحفظ لا تُنشئ إشعاراً مكرراً (لا يفشل رغم إزالة قيد unique)
        $this->actingAs($teacherUser)->post(route('assessments.marks.store', $assessment->id), [
            'mark' => [$ctx['student_id'] => 15],
        ])->assertStatus(302);

        $this->assertSame(1, DB::table('notifications')->where('notifiable_id', $ctx['student_user_id'])->count());
    }

    public function test_guardian_sees_notification_in_inbox_and_can_mark_read(): void
    {
        $ctx = $this->bootstrap();
        $assessment = Assessment::query()->create([
            'school_id' => $ctx['school_id'], 'teacher_id' => $ctx['teacher_id'], 'section_id' => $ctx['section_id'],
            'specialization_id' => $ctx['spec_id'], 'title' => 'امتحان', 'type' => 'exam', 'max_mark' => 20, 'coefficient' => 1,
        ]);
        $teacherUser = User::query()->find($ctx['teacher_user_id']);
        $this->actingAs($teacherUser)->post(route('assessments.marks.store', $assessment->id), [
            'mark' => [$ctx['student_id'] => 10],
        ]);

        $guardianUser = User::query()->find($ctx['guardian_user_id']);
        $guardianUser->attachRole('guardian');

        $inbox = $this->actingAs($guardianUser)->get(route('notifications.index'));
        $inbox->assertStatus(200);
        $inbox->assertSee('تم إدخال نقاط جديدة');

        $notifId = DB::table('notifications')->where('notifiable_id', $guardianUser->id)->value('id');
        $this->actingAs($guardianUser)->post(route('notifications.read', $notifId))->assertStatus(302);

        $this->assertNotNull(DB::table('notifications')->where('id', $notifId)->value('read_at'));
    }

    private function bootstrap(): array
    {
        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'S', 'ar' => 'مدرسة', 'en' => 'S']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolId, 'name_grade' => json_encode(['fr' => 'G', 'ar' => 'م', 'en' => 'G']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']), 'created_at' => now(), 'updated_at' => now(),
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
        $specId = DB::table('specializations')->insertGetId([
            'name' => json_encode(['fr' => 'Math', 'ar' => 'رياضيات', 'en' => 'Math']), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $teacherUser = User::factory()->create(['must_change_password' => false, 'school_id' => $schoolId]);
        $teacherUser->attachRole('teacher');
        $teacherId = DB::table('teachers')->insertGetId([
            'user_id' => $teacherUser->id, 'specialization_id' => $specId,
            'name' => json_encode(['fr' => 'T', 'ar' => 'أستاذ', 'en' => 'T']), 'gender' => 1,
            'joining_date' => '2024-09-01', 'address' => 'A', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('teacher_section')->insert([
            'teacher_id' => $teacherId, 'section_id' => $sectionId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $studentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'S', 'ar' => 'تلميذ', 'en' => 'S']),
            'email' => 'st-' . uniqid() . '@example.test', 'password' => bcrypt('x'),
            'school_id' => $schoolId, 'must_change_password' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $guardianUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'P', 'ar' => 'ولي', 'en' => 'P']),
            'email' => 'gu-' . uniqid() . '@example.test', 'password' => bcrypt('x'),
            'school_id' => $schoolId, 'must_change_password' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $parentId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'P', 'ar' => 'ولي', 'en' => 'P']),
            'nomwali' => json_encode(['fr' => 'L', 'ar' => 'ل', 'en' => 'L']),
            'relationetudiant' => 'father', 'adressewali' => 'A', 'wilayawali' => 'W',
            'dayrawali' => 'D', 'baladiawali' => 'B', 'numtelephonewali' => 550000001,
            'user_id' => $guardianUserId, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $studentId = DB::table('studentinfos')->insertGetId([
            'user_id' => $studentUserId, 'section_id' => $sectionId, 'parent_id' => $parentId, 'gender' => 1,
            'prenom' => json_encode(['fr' => 'Ali', 'ar' => 'علي', 'en' => 'Ali']),
            'nom' => json_encode(['fr' => 'Ben', 'ar' => 'بن', 'en' => 'Ben']),
            'lieunaissance' => 'C', 'wilaya' => 'W', 'dayra' => 'D', 'baladia' => 'B',
            'datenaissance' => '2012-01-01', 'numtelephone' => 550000002, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return compact('schoolId', 'sectionId', 'studentId', 'studentUserId', 'guardianUserId', 'teacherId')
            + ['spec_id' => $specId, 'school_id' => $schoolId, 'section_id' => $sectionId,
               'student_id' => $studentId, 'student_user_id' => $studentUserId,
               'guardian_user_id' => $guardianUserId, 'teacher_id' => $teacherId,
               'teacher_user_id' => $teacherUser->id];
    }
}
