<?php

namespace Tests\Feature\Inscription;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class StudentTemplateImportTest extends TestCase
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

        foreach (['admin', 'student', 'guardian'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }

    public function test_admin_can_download_blank_template(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get(route('students.import.template'));
        $response->assertStatus(200);
        $this->assertStringContainsString(
            'spreadsheetml',
            strtolower((string) $response->headers->get('content-type'))
        );
    }

    public function test_admin_can_import_filled_template_and_students_are_created(): void
    {
        $admin = $this->admin();

        $file = $this->buildTemplateUpload([
            ['1234567890123456', 'بن علي', 'أحمد', 'ذكر', '2012-09-15', 'الجزائر', 'السنة الأولى متوسط', 'جذع مشترك', 'القسم أ', 'خارجي', '2024-001', '2024-09-05'],
            ['1234567890123457', 'حسان', 'سارة', 'أنثى', '2013-03-10', 'وهران', 'السنة الأولى متوسط', 'جذع مشترك', 'القسم أ', 'خارجي', '2024-002', '2024-09-05'],
        ]);

        $response = $this->actingAs($admin)->post(route('students.import.template.upload'), ['file' => $file]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('studentinfos', ['national_id' => '1234567890123456']);
        $this->assertDatabaseHas('studentinfos', ['national_id' => '1234567890123457']);
    }

    public function test_invalid_national_id_row_is_skipped(): void
    {
        $admin = $this->admin();

        $file = $this->buildTemplateUpload([
            ['123', 'خطأ', 'صف', 'ذكر', '2012-09-15', 'الجزائر', 'السنة الأولى متوسط', '', 'القسم أ', 'خارجي', '', ''],
        ]);

        $this->actingAs($admin)->post(route('students.import.template.upload'), ['file' => $file])->assertStatus(302);
        $this->assertDatabaseMissing('studentinfos', ['national_id' => '123']);
    }

    public function test_template_lists_own_sections_and_copied_values_import_into_same_section(): void
    {
        $admin = $this->admin();
        [$sectionA] = $this->makeSection((int) $admin->school_id, 'أولى', 'عام', '1');
        $otherSchool = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'O', 'ar' => 'مدرسة أخرى', 'en' => 'O']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->makeSection($otherSchool, 'ثانية', 'عام', '2');

        $response = $this->actingAs($admin)->get(route('students.import.template'));
        $response->assertOk();
        $book = \PhpOffice\PhpSpreadsheet\IOFactory::load($response->getFile()->getPathname());

        $this->assertNotNull($book->getSheetByName('توضيحات'));
        $sections = $book->getSheetByName('الأقسام المتاحة')->toArray();
        // مدير الفرع يرى أقسام فرعه فقط، بالأسماء المخزّنة حرفياً (الشعبة «عام» تُترك فارغة).
        $this->assertCount(2, $sections);
        [$schoolId, , $grade, $stream, $sectionName, $sectionId] = $sections[1];
        $this->assertSame([(int) $admin->school_id, 'أولى', '', '1', $sectionA], [(int) $schoolId, $grade, (string) $stream, (string) $sectionName, (int) $sectionId]);

        // نسخ القيم كما هي من الورقة يضع التلميذ في نفس القسم دون إنشاء قسم مكرر.
        $sectionsBefore = DB::table('sections')->count();
        $file = $this->buildTemplateUpload([
            ['1234567890123458', 'بن علي', 'أحمد', 'ذكر', '2012-09-15', 'الوادي', $grade, $stream, $sectionName, '', '', ''],
        ]);
        $this->actingAs($admin)->post(route('students.import.template.upload'), ['file' => $file])
            ->assertSessionHasNoErrors();

        $this->assertSame($sectionsBefore, DB::table('sections')->count());
        $this->assertDatabaseHas('studentinfos', ['national_id' => '1234567890123458', 'section_id' => $sectionA]);
    }

    /** @return array{0:int} [sectionId] */
    private function makeSection(int $schoolId, string $grade, string $stream, string $section): array
    {
        $json = fn (string $v) => json_encode(['fr' => $v, 'ar' => $v, 'en' => $v]);
        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolId, 'name_grade' => $json($grade), 'notes' => $json(''),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolId, 'grade_id' => $gradeId, 'name_class' => $json($stream),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $sectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolId, 'grade_id' => $gradeId, 'classroom_id' => $classroomId,
            'name_section' => $json($section), 'Status' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return [$sectionId];
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        $admin->attachRole('admin');
        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'S', 'ar' => 'مدرستي', 'en' => 'S']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolId]);

        return $admin;
    }

    private function buildTemplateUpload(array $rows): UploadedFile
    {
        $headers = ['رقم التعريف', 'اللقب', 'الاسم', 'الجنس', 'تاريخ الازدياد', 'مكان الازدياد', 'السنة', 'الشعبة', 'القسم', 'نظام التمدرس', 'رقم القيد', 'تاريخ التسجيل'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('التلاميذ');
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($rows, null, 'A2');

        $path = tempnam(sys_get_temp_dir(), 'tpl-') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'filled.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }
}
