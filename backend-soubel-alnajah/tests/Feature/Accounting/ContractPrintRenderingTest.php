<?php

namespace Tests\Feature\Accounting;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class ContractPrintRenderingTest extends TestCase
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
    }

    public function test_contract_print_page_renders_arabic_labels_and_values(): void
    {
        [$user, $schoolId, $studentId] = $this->bootstrapAccountantWithStudent();

        $contractId = DB::table('student_contracts')->insertGetId([
            'school_id' => $schoolId,
            'student_id' => $studentId,
            'external_contract_no' => '1001',
            'academic_year' => '2026-2027',
            'total_amount' => 150000,
            'plan_type' => 'monthly',
            'installments_count' => 3,
            'starts_on' => '2026-09-09',
            'ends_on' => '2027-04-01',
            'status' => 'partial',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('contract_installments')->insert([
            [
                'contract_id' => $contractId,
                'installment_no' => 1,
                'due_date' => '2026-10-01',
                'amount' => 50000,
                'paid_amount' => 0,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($user)->get(route('accounting.contracts.print', ['contract' => $contractId]));

        $response->assertStatus(200);
        $response->assertSee('عقد طالب');
        $response->assertSee('نوع الخطة');
        $response->assertSee('شهري');
        $response->assertSee('الحالة');
        $response->assertSee('جزئي');
        $response->assertSee('قيد الانتظار');
        $response->assertSee('dir="rtl"', false);
    }

    public function test_contract_download_returns_pdf_file(): void
    {
        [$user, $schoolId, $studentId] = $this->bootstrapAccountantWithStudent('B');

        $contractId = DB::table('student_contracts')->insertGetId([
            'school_id' => $schoolId,
            'student_id' => $studentId,
            'external_contract_no' => '2002',
            'academic_year' => '2026-2027',
            'total_amount' => 90000,
            'plan_type' => 'yearly',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('accounting.contracts.download', ['contract' => $contractId]));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));

        $pdfBinary = '';
        if (method_exists($response, 'streamedContent')) {
            $pdfBinary = (string) $response->streamedContent();
        }
        if ($pdfBinary === '') {
            $pdfBinary = (string) $response->getContent();
        }

        $this->assertStringStartsWith('%PDF', $pdfBinary);
    }

    public function test_contracts_can_be_printed_by_date_range(): void
    {
        [$user, $schoolId, $studentId] = $this->bootstrapAccountantWithStudent('C');

        $inRangeId = DB::table('student_contracts')->insertGetId([
            'school_id' => $schoolId,
            'student_id' => $studentId,
            'external_contract_no' => '3001',
            'academic_year' => '2026-2027',
            'total_amount' => 120000,
            'plan_type' => 'monthly',
            'status' => 'active',
            'created_at' => '2026-03-03 10:00:00',
            'updated_at' => '2026-03-03 10:00:00',
        ]);

        $outRangeId = DB::table('student_contracts')->insertGetId([
            'school_id' => $schoolId,
            'student_id' => $studentId,
            'external_contract_no' => '3002',
            'academic_year' => '2027-2028',
            'total_amount' => 130000,
            'plan_type' => 'monthly',
            'status' => 'active',
            'created_at' => '2026-01-01 10:00:00',
            'updated_at' => '2026-01-01 10:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('accounting.contracts.print-range', [
            'from_date' => '2026-03-01',
            'to_date' => '2026-03-31',
        ]));

        $response->assertStatus(200);
        $response->assertSee('تقرير العقود حسب التاريخ');
        $response->assertSee('2026-03-03');
        $response->assertDontSee('2026-01-01');
    }

    private function bootstrapAccountantWithStudent(string $suffix = 'A'): array
    {
        $user = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => 'accountant']);
        $user->attachRole('accountant');

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School ' . $suffix, 'ar' => 'مدرسة ' . $suffix, 'en' => 'School ' . $suffix]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user->update(['school_id' => $schoolId]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolId,
            'name_grade' => json_encode(['fr' => 'G' . $suffix, 'ar' => 'م' . $suffix, 'en' => 'G' . $suffix]),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C' . $suffix, 'ar' => 'ق' . $suffix, 'en' => 'C' . $suffix]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S' . $suffix, 'ar' => 'ف' . $suffix, 'en' => 'S' . $suffix]),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'Ibrar', 'ar' => 'إبرار', 'en' => 'Ibrar']),
            'email' => 'student-contract-' . strtolower($suffix) . '-' . uniqid() . '@example.test',
            'password' => bcrypt('Secret123!'),
            'school_id' => $schoolId,
            'must_change_password' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $parentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'Parent', 'ar' => 'ولي', 'en' => 'Parent']),
            'email' => 'parent-contract-' . strtolower($suffix) . '-' . uniqid() . '@example.test',
            'password' => bcrypt('Secret123!'),
            'school_id' => $schoolId,
            'must_change_password' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $parentId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'Parent', 'ar' => 'ولي', 'en' => 'Parent']),
            'nomwali' => json_encode(['fr' => $suffix, 'ar' => $suffix, 'en' => $suffix]),
            'relationetudiant' => json_encode(['fr' => 'Father', 'ar' => 'الأب', 'en' => 'Father']),
            'adressewali' => json_encode(['fr' => 'Address', 'ar' => 'عنوان', 'en' => 'Address']),
            'wilayawali' => json_encode(['fr' => 'Wilaya', 'ar' => 'ولاية', 'en' => 'Wilaya']),
            'dayrawali' => json_encode(['fr' => 'Dayra', 'ar' => 'دائرة', 'en' => 'Dayra']),
            'baladiawali' => json_encode(['fr' => 'Baladia', 'ar' => 'بلدية', 'en' => 'Baladia']),
            'numtelephonewali' => 550002000 + random_int(1, 200),
            'user_id' => $parentUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentId = DB::table('studentinfos')->insertGetId([
            'user_id' => $studentUserId,
            'section_id' => $sectionId,
            'parent_id' => $parentId,
            'prenom' => json_encode(['fr' => 'Ibrar', 'ar' => 'إبرار', 'en' => 'Ibrar']),
            'nom' => json_encode(['fr' => 'Benali', 'ar' => 'بن علي', 'en' => 'Benali']),
            'gender' => 1,
            'lieunaissance' => json_encode(['fr' => 'City', 'ar' => 'مدينة', 'en' => 'City']),
            'wilaya' => json_encode(['fr' => 'Wilaya', 'ar' => 'ولاية', 'en' => 'Wilaya']),
            'dayra' => json_encode(['fr' => 'Dayra', 'ar' => 'دائرة', 'en' => 'Dayra']),
            'baladia' => json_encode(['fr' => 'Baladia', 'ar' => 'بلدية', 'en' => 'Baladia']),
            'datenaissance' => '2012-01-01',
            'numtelephone' => 550003000 + random_int(1, 200),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$user, $schoolId, $studentId];
    }
}
