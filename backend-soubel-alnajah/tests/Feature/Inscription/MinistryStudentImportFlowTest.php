<?php

namespace Tests\Feature\Inscription;

use App\Models\Inscription\StudentInfo;
use App\Models\Role;
use App\Models\School\Classroom;
use App\Models\School\School;
use App\Models\School\Schoolgrade;
use App\Models\School\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MinistryStudentImportFlowTest extends TestCase
{
    use RefreshDatabase;

    private const SCHOOL_NAME = 'ثانوية سبل النجاح (الوادي)';

    private const HIGH_SCHOOL_HEADERS = [
        'رقم التعريف', 'اللقب', 'الاسم', 'الجنس', 'تاريخ الازدياد', 'مولود بحكم', 'عقد الميلاد',
        'سنة التسجيل في سجل الولادات', 'رقم عقد الميلاد', 'مكان الازدياد', 'السنة', 'الشعبة',
        'القسم', 'نظام التمدرس', 'رقم القيد', 'تاريخ التسجيل',
    ];

    private const MIDDLE_SCHOOL_HEADERS = [
        'رقم التعريف', 'اللقب', 'الاسم', 'الجنس', 'تاريخ الازدياد', 'مولود بحكم', 'عقد الميلاد',
        'سنة التسجيل في سجل الولادات', 'رقم عقد الميلاد', 'مكان الازدياد', 'السنة',
        'القسم', 'نظام التمدرس', 'رقم القيد', 'تاريخ التسجيل',
    ];

    public function test_import_creates_students_and_school_structure(): void
    {
        $admin = $this->bootstrapAdmin();

        $file = $this->ministryFile(self::SCHOOL_NAME, self::HIGH_SCHOOL_HEADERS, [
            ['1000000000000001', 'درويش', 'رائد', 'ذكر', '2009-08-06', 'لا', 'عادي', '2009', '04413', 'الوادي', 'أولى', 'جدع مشترك آداب', '1', 'خارجي', '443', '2024-09-30'],
            ['1000000000000002', 'مسعودي', 'محمد', 'ذكر', '2010-08-30', 'لا', 'عادي', '2010', '05243', 'الوادي', 'أولى', 'جدع مشترك آداب', '1', 'نصف داخلي', '700', '2026-01-05'],
            ['1000000000000003', 'بن عمر', 'سارة', 'أنثى', '2011-02-10', 'نعم', 'مكرر', '2011', '01234', 'قمار', 'ثانية', 'علوم تجريبية', '2', 'خاطئ', '15', '2025-09-15'],
        ]);

        $response = $this->importRequest($admin, $file, 'mn_token_create');

        $response->assertOk();
        $response->assertJsonPath('summary.created_rows', 3);
        $response->assertJsonPath('summary.failed_rows', 0);
        $response->assertJsonPath('summary.school_name', self::SCHOOL_NAME);

        // المدرسة والهيكل أنشئت تلقائياً
        $school = School::where('name_school->ar', self::SCHOOL_NAME)->first();
        $this->assertNotNull($school);
        $this->assertNotNull(Schoolgrade::where('school_id', $school->id)->where('name_grade->ar', 'أولى')->first());
        $this->assertNotNull(Classroom::where('school_id', $school->id)->where('name_class->ar', 'جدع مشترك آداب')->first());
        $this->assertNotNull(Section::where('school_id', $school->id)->where('name_section->ar', '1')->first());

        // بيانات الطالب
        $student = StudentInfo::where('national_id', '1000000000000001')->first();
        $this->assertNotNull($student);
        $this->assertSame(1, (int) $student->gender);
        $this->assertSame('خارجي', $student->schooling_system);
        $this->assertSame('443', $student->registration_number);
        $this->assertSame('2024-09-30', $student->enrolled_at?->format('Y-m-d') ?? $student->enrolled_at);
        $this->assertSame('رائد', $student->getTranslation('prenom', 'ar'));
        $this->assertSame('درويش', $student->getTranslation('nom', 'ar'));

        // أنثى → 0، "خاطئ" → null دون فشل، مولود بحكم → true
        $sara = StudentInfo::where('national_id', '1000000000000003')->first();
        $this->assertSame(0, (int) $sara->gender);
        $this->assertNull($sara->schooling_system);
        $this->assertTrue((bool) $sara->born_by_judgment);

        // ولي placeholder أنشئ لكل طالب
        $this->assertNotNull($student->parent_id);
    }

    public function test_reimporting_same_file_is_idempotent(): void
    {
        $admin = $this->bootstrapAdmin();

        $rows = [
            ['1000000000000001', 'درويش', 'رائد', 'ذكر', '2009-08-06', 'لا', 'عادي', '2009', '04413', 'الوادي', 'أولى', 'جدع مشترك آداب', '1', 'خارجي', '443', '2024-09-30'],
            ['1000000000000002', 'مسعودي', 'محمد', 'ذكر', '2010-08-30', 'لا', 'عادي', '2010', '05243', 'الوادي', 'أولى', 'جدع مشترك آداب', '1', 'نصف داخلي', '700', '2026-01-05'],
        ];

        $this->importRequest($admin, $this->ministryFile(self::SCHOOL_NAME, self::HIGH_SCHOOL_HEADERS, $rows), 'mn_token_first')->assertOk();

        $studentsBefore = StudentInfo::count();
        $parentsBefore = DB::table('my_parents')->count();
        $sectionsBefore = Section::count();

        $response = $this->importRequest($admin, $this->ministryFile(self::SCHOOL_NAME, self::HIGH_SCHOOL_HEADERS, $rows), 'mn_token_second');

        $response->assertOk();
        $response->assertJsonPath('summary.created_rows', 0);
        $response->assertJsonPath('summary.unchanged_rows', 2);
        $response->assertJsonPath('summary.updated_rows', 0);
        $response->assertJsonPath('summary.moved_rows', 0);

        $this->assertSame($studentsBefore, StudentInfo::count());
        $this->assertSame($parentsBefore, DB::table('my_parents')->count());
        $this->assertSame($sectionsBefore, Section::count());
    }

    public function test_reimport_moves_student_to_new_section_without_touching_guardian(): void
    {
        $admin = $this->bootstrapAdmin();

        $row = ['1000000000000001', 'درويش', 'رائد', 'ذكر', '2009-08-06', 'لا', 'عادي', '2009', '04413', 'الوادي', 'أولى', 'جدع مشترك آداب', '1', 'خارجي', '443', '2024-09-30'];
        $this->importRequest($admin, $this->ministryFile(self::SCHOOL_NAME, self::HIGH_SCHOOL_HEADERS, [$row]), 'mn_token_move_a')->assertOk();

        $student = StudentInfo::where('national_id', '1000000000000001')->first();
        $originalParentId = $student->parent_id;
        $originalSectionId = $student->section_id;
        $parentsBefore = DB::table('my_parents')->count();

        // نفس الطالب لكن في القسم 2
        $row[12] = '2';
        $response = $this->importRequest($admin, $this->ministryFile(self::SCHOOL_NAME, self::HIGH_SCHOOL_HEADERS, [$row]), 'mn_token_move_b');

        $response->assertOk();
        $response->assertJsonPath('summary.moved_rows', 1);
        $response->assertJsonPath('summary.created_rows', 0);

        $student->refresh();
        $this->assertNotSame((int) $originalSectionId, (int) $student->section_id);
        $this->assertSame((int) $originalParentId, (int) $student->parent_id);
        $this->assertSame($parentsBefore, DB::table('my_parents')->count());
    }

    public function test_reimport_updates_changed_fields(): void
    {
        $admin = $this->bootstrapAdmin();

        $row = ['1000000000000001', 'درويش', 'رائد', 'ذكر', '2009-08-06', 'لا', 'عادي', '2009', '04413', 'الوادي', 'أولى', 'جدع مشترك آداب', '1', 'خارجي', '443', '2024-09-30'];
        $this->importRequest($admin, $this->ministryFile(self::SCHOOL_NAME, self::HIGH_SCHOOL_HEADERS, [$row]), 'mn_token_upd_a')->assertOk();

        // تغيير نظام التمدرس
        $row[13] = 'نصف داخلي';
        $response = $this->importRequest($admin, $this->ministryFile(self::SCHOOL_NAME, self::HIGH_SCHOOL_HEADERS, [$row]), 'mn_token_upd_b');

        $response->assertOk();
        $response->assertJsonPath('summary.updated_rows', 1);

        $this->assertSame(
            'نصف داخلي',
            StudentInfo::where('national_id', '1000000000000001')->value('schooling_system')
        );
    }

    public function test_absent_students_are_reported_not_deleted(): void
    {
        $admin = $this->bootstrapAdmin();

        // استيراد أولي لطالبين
        $this->importRequest($admin, $this->ministryFile(self::SCHOOL_NAME, self::HIGH_SCHOOL_HEADERS, [
            ['1000000000000001', 'درويش', 'رائد', 'ذكر', '2009-08-06', 'لا', 'عادي', '2009', '04413', 'الوادي', 'أولى', 'جدع مشترك آداب', '1', 'خارجي', '443', '2024-09-30'],
            ['1000000000000002', 'مسعودي', 'محمد', 'ذكر', '2010-08-30', 'لا', 'عادي', '2010', '05243', 'الوادي', 'أولى', 'جدع مشترك آداب', '1', 'خارجي', '700', '2026-01-05'],
        ]), 'mn_token_abs_a')->assertOk();

        // إعادة رفع ملف يحتوي الطالب الأول فقط
        $response = $this->importRequest($admin, $this->ministryFile(self::SCHOOL_NAME, self::HIGH_SCHOOL_HEADERS, [
            ['1000000000000001', 'درويش', 'رائد', 'ذكر', '2009-08-06', 'لا', 'عادي', '2009', '04413', 'الوادي', 'أولى', 'جدع مشترك آداب', '1', 'خارجي', '443', '2024-09-30'],
        ]), 'mn_token_abs_b');

        $response->assertOk();
        $response->assertJsonPath('summary.absent_count', 1);

        $preview = $response->json('summary.absent_preview');
        $this->assertSame('1000000000000002', $preview[0]['national_id']);

        // الطالب الغائب لم يُحذف
        $this->assertNotNull(StudentInfo::where('national_id', '1000000000000002')->first());
    }

    public function test_middle_school_file_without_stream_column_uses_default_classroom(): void
    {
        $admin = $this->bootstrapAdmin();
        $schoolName = 'متوسطة سبل النجاح (الوادي)';

        $response = $this->importRequest($admin, $this->ministryFile($schoolName, self::MIDDLE_SCHOOL_HEADERS, [
            ['2000000000000001', 'غدير', 'أويس', 'ذكر', '2014-06-09', 'لا', 'عادي', '2014', '04272', 'الوادي', 'أولى', '1', 'خارجي', '17', '2025-09-15'],
        ]), 'mn_token_middle');

        $response->assertOk();
        $response->assertJsonPath('summary.created_rows', 1);
        $response->assertJsonPath('summary.school_name', $schoolName);

        $school = School::where('name_school->ar', $schoolName)->first();
        $this->assertNotNull($school);
        $this->assertNotNull(Classroom::where('school_id', $school->id)->where('name_class->ar', 'عام')->first());
    }

    public function test_invalid_national_id_fails_row_without_aborting_import(): void
    {
        $admin = $this->bootstrapAdmin();

        $response = $this->importRequest($admin, $this->ministryFile(self::SCHOOL_NAME, self::HIGH_SCHOOL_HEADERS, [
            ['123', 'خطأ', 'رقم', 'ذكر', '2009-08-06', 'لا', 'عادي', '2009', '04413', 'الوادي', 'أولى', 'جدع مشترك آداب', '1', 'خارجي', '443', '2024-09-30'],
            ['1000000000000009', 'صحيح', 'طالب', 'ذكر', '2010-01-01', 'لا', 'عادي', '2010', '01111', 'الوادي', 'أولى', 'جدع مشترك آداب', '1', 'خارجي', '10', '2025-09-15'],
        ]), 'mn_token_badid');

        $response->assertOk();
        $response->assertJsonPath('summary.failed_rows', 1);
        $response->assertJsonPath('summary.created_rows', 1);
    }

    public function test_status_endpoint_returns_completed_payload(): void
    {
        $admin = $this->bootstrapAdmin();
        $token = 'mn_token_status';

        $this->importRequest($admin, $this->ministryFile(self::SCHOOL_NAME, self::HIGH_SCHOOL_HEADERS, [
            ['1000000000000001', 'درويش', 'رائد', 'ذكر', '2009-08-06', 'لا', 'عادي', '2009', '04413', 'الوادي', 'أولى', 'جدع مشترك آداب', '1', 'خارجي', '443', '2024-09-30'],
        ]), $token)->assertOk();

        $statusResponse = $this->actingAs($admin)->post(
            route('students.import.status', ['token' => $token]),
            [],
            ['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest']
        );

        $statusResponse->assertOk();
        $statusResponse->assertJson([
            'token' => $token,
            'status' => 'completed',
        ]);
        $statusResponse->assertJsonPath('school_name', self::SCHOOL_NAME);
    }

    public function test_duplicate_national_id_within_file_is_processed_once(): void
    {
        $admin = $this->bootstrapAdmin();

        $row = ['1000000000000001', 'درويش', 'رائد', 'ذكر', '2009-08-06', 'لا', 'عادي', '2009', '04413', 'الوادي', 'أولى', 'جدع مشترك آداب', '1', 'خارجي', '443', '2024-09-30'];

        $response = $this->importRequest($admin, $this->ministryFile(self::SCHOOL_NAME, self::HIGH_SCHOOL_HEADERS, [$row, $row]), 'mn_token_dupfile');

        $response->assertOk();
        $response->assertJsonPath('summary.created_rows', 1);
        $response->assertJsonPath('summary.unchanged_rows', 1);

        $this->assertSame(1, StudentInfo::where('national_id', '1000000000000001')->count());
    }

    public function test_reimport_restores_soft_deleted_student(): void
    {
        $admin = $this->bootstrapAdmin();

        $row = ['1000000000000001', 'درويش', 'رائد', 'ذكر', '2009-08-06', 'لا', 'عادي', '2009', '04413', 'الوادي', 'أولى', 'جدع مشترك آداب', '1', 'خارجي', '443', '2024-09-30'];
        $this->importRequest($admin, $this->ministryFile(self::SCHOOL_NAME, self::HIGH_SCHOOL_HEADERS, [$row]), 'mn_token_trash_a')->assertOk();

        StudentInfo::where('national_id', '1000000000000001')->first()->delete();
        $this->assertNull(StudentInfo::where('national_id', '1000000000000001')->first());

        $response = $this->importRequest($admin, $this->ministryFile(self::SCHOOL_NAME, self::HIGH_SCHOOL_HEADERS, [$row]), 'mn_token_trash_b');

        $response->assertOk();
        $response->assertJsonPath('summary.created_rows', 0);
        $response->assertJsonPath('summary.updated_rows', 1);

        $restored = StudentInfo::where('national_id', '1000000000000001')->first();
        $this->assertNotNull($restored);
        $this->assertNull($restored->deleted_at);
    }

    public function test_reimport_updates_student_arabic_name(): void
    {
        $admin = $this->bootstrapAdmin();

        $row = ['1000000000000001', 'درويش', 'رائد', 'ذكر', '2009-08-06', 'لا', 'عادي', '2009', '04413', 'الوادي', 'أولى', 'جدع مشترك آداب', '1', 'خارجي', '443', '2024-09-30'];
        $this->importRequest($admin, $this->ministryFile(self::SCHOOL_NAME, self::HIGH_SCHOOL_HEADERS, [$row]), 'mn_token_name_a')->assertOk();

        // تصحيح اللقب والاسم
        $row[1] = 'درويش المصحح';
        $row[2] = 'رائد عبد الباري';
        $response = $this->importRequest($admin, $this->ministryFile(self::SCHOOL_NAME, self::HIGH_SCHOOL_HEADERS, [$row]), 'mn_token_name_b');

        $response->assertOk();
        $response->assertJsonPath('summary.updated_rows', 1);

        $student = StudentInfo::where('national_id', '1000000000000001')->first();
        $this->assertSame('درويش المصحح', $student->getTranslation('nom', 'ar'));
        $this->assertSame('رائد عبد الباري', $student->getTranslation('prenom', 'ar'));
    }

    public function test_legacy_students_without_national_id_are_counted_separately(): void
    {
        $admin = $this->bootstrapAdmin();

        // استيراد أولي ينشئ المدرسة والهيكل
        $this->importRequest($admin, $this->ministryFile(self::SCHOOL_NAME, self::HIGH_SCHOOL_HEADERS, [
            ['1000000000000001', 'درويش', 'رائد', 'ذكر', '2009-08-06', 'لا', 'عادي', '2009', '04413', 'الوادي', 'أولى', 'جدع مشترك آداب', '1', 'خارجي', '443', '2024-09-30'],
        ]), 'mn_token_leg_a')->assertOk();

        // طالب قديم بدون رقم تعريف في نفس المدرسة
        $section = Section::first();
        $legacyUser = User::factory()->create(['must_change_password' => false]);
        $legacyUser->attachRole('student');
        $parentId = DB::table('my_parents')->insertGetId([
            'user_id' => $legacyUser->id,
            'prenomwali' => json_encode(['ar' => 'ولي']),
            'nomwali' => json_encode(['ar' => 'قديم']),
            'relationetudiant' => 'ولي',
            'adressewali' => 'غير محدد',
            'wilayawali' => 'الوادي',
            'dayrawali' => 'الوادي',
            'baladiawali' => 'الوادي',
            'numtelephonewali' => '0666999999',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('studentinfos')->insert([
            'user_id' => $legacyUser->id,
            'section_id' => $section->id,
            'parent_id' => $parentId,
            'gender' => 1,
            'prenom' => json_encode(['ar' => 'قديم']),
            'nom' => json_encode(['ar' => 'طالب']),
            'lieunaissance' => 'الوادي',
            'wilaya' => 'الوادي',
            'dayra' => 'الوادي',
            'baladia' => 'الوادي',
            'datenaissance' => '2010-01-01',
            'numtelephone' => '0555999999',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->importRequest($admin, $this->ministryFile(self::SCHOOL_NAME, self::HIGH_SCHOOL_HEADERS, [
            ['1000000000000001', 'درويش', 'رائد', 'ذكر', '2009-08-06', 'لا', 'عادي', '2009', '04413', 'الوادي', 'أولى', 'جدع مشترك آداب', '1', 'خارجي', '443', '2024-09-30'],
        ]), 'mn_token_leg_b');

        $response->assertOk();
        // الطالب القديم لا يُحسب غائباً بل ضمن عدّاد منفصل
        $response->assertJsonPath('summary.absent_count', 0);
        $response->assertJsonPath('summary.legacy_count', 1);
    }

    public function test_unsupported_file_extension_is_rejected(): void
    {
        $admin = $this->bootstrapAdmin();

        $path = tempnam(sys_get_temp_dir(), 'bad-ext-') . '.txt';
        file_put_contents($path, '<html><table><tr><th>x</th></tr></table></html>');
        $file = new UploadedFile($path, 'Eleve.txt', 'text/plain', null, true);

        $this->importRequest($admin, $file, 'mn_token_ext')->assertStatus(422);
    }

    public function test_file_with_missing_columns_fails_gracefully(): void
    {
        $admin = $this->bootstrapAdmin();

        // ملف HTML بجدول لا يحتوي الأعمدة المطلوبة
        $path = tempnam(sys_get_temp_dir(), 'missing-cols-') . '.xls';
        file_put_contents($path, '<br/>مدرسة ما<html><body><table><tr><th>عمود غريب</th></tr><tr><td>قيمة</td></tr></table></body></html>');
        $file = new UploadedFile($path, 'Eleve.xls', 'application/octet-stream', null, true);

        $response = $this->importRequest($admin, $file, 'mn_token_cols');

        $response->assertStatus(422);
        $this->assertSame(0, StudentInfo::count());
        $this->assertSame(0, School::count());
    }

    public function test_non_ministry_file_is_rejected(): void
    {
        $admin = $this->bootstrapAdmin();

        $path = tempnam(sys_get_temp_dir(), 'bad-import-') . '.xls';
        file_put_contents($path, "just,a,csv\n1,2,3\n");
        $file = new UploadedFile($path, 'Eleve.xls', 'application/octet-stream', null, true);

        $response = $this->importRequest($admin, $file, 'mn_token_reject');

        $response->assertStatus(422);
    }

    private function bootstrapAdmin(): User
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'student']);
        Role::firstOrCreate(['name' => 'guardian']);
        $admin->attachRole('admin');

        return $admin;
    }

    private function importRequest(User $admin, UploadedFile $file, string $token)
    {
        return $this->actingAs($admin)->post(
            route('students.import'),
            [
                'import_token' => $token,
                'file' => $file,
            ],
            [
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ]
        );
    }

    /**
     * يبني ملف Eleve.xls كما تصدّره منظومة الوزارة: HTML بديباجة، ووسوم <tr> غير مغلقة عمداً.
     */
    private function ministryFile(string $schoolName, array $headers, array $rows): UploadedFile
    {
        $html = '<br> <p style="font-size:18; text-align:center"><strong><span>  الجمهورية الجزائرية الديمقراطية الشعبية </span></strong></p>';
        $html .= '<p style="font-size:16; text-align:center"><strong><span> وزارة التربية الوطنية </span></strong></p><br/>';
        $html .= $schoolName;
        $html .= "<html dir='RTL'><head><meta charset=\"UTF-8\"></head><body><table border=\"1\"><tr>";

        foreach ($headers as $header) {
            $html .= '<th style="background:#f9f9f9;">' . $header . '</th>';
        }
        $html .= '</tr>';

        foreach ($rows as $row) {
            // وسم <tr> غير مغلق عمداً كما في الملفات الحقيقية
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . $cell . '</td>';
            }
        }

        $html .= '</table></body></html>';

        $path = tempnam(sys_get_temp_dir(), 'ministry-import-') . '.xls';
        file_put_contents($path, $html);

        return new UploadedFile($path, 'Eleve.xls', 'application/octet-stream', null, true);
    }
}
