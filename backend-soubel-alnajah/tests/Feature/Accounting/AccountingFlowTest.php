<?php

namespace Tests\Feature\Accounting;

use App\Models\Accounting\StudentContract;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class AccountingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_accountant_can_create_contract_and_register_payment(): void
    {
        [$accountant, $schoolId, $studentId] = $this->bootstrapUserWithStudent('accountant');

        $contractResponse = $this->actingAs($accountant)->post(route('accounting.contracts.store'), [
            'student_id' => $studentId,
            'academic_year' => '2026-2027',
            'total_amount' => 1000,
            'plan_type' => 'installments',
            'installments_count' => 4,
            'starts_on' => now()->toDateString(),
            'status' => 'active',
        ]);

        $contractResponse->assertStatus(302);

        $contract = DB::table('student_contracts')->where([
            'school_id' => $schoolId,
            'student_id' => $studentId,
            'academic_year' => '2026-2027',
        ])->first();

        $this->assertNotNull($contract);
        $this->assertSame(4, DB::table('contract_installments')->where('contract_id', $contract->id)->count());

        $paymentResponse = $this->actingAs($accountant)->post(route('accounting.payments.store'), [
            'contract_id' => $contract->id,
            'receipt_number' => 'ACC-2026-001',
            'paid_on' => now()->toDateString(),
            'amount' => 200,
            'payment_method' => 'cash',
        ]);

        $paymentResponse->assertStatus(302);

        $payment = DB::table('payments')->where([
            'school_id' => $schoolId,
            'contract_id' => $contract->id,
            'receipt_number' => 'ACC-2026-001',
        ])->first();

        $this->assertNotNull($payment);
        $this->assertDatabaseHas('payment_receipts', [
            'school_id' => $schoolId,
            'payment_id' => $payment->id,
        ]);
        $this->assertDatabaseHas('student_contracts', [
            'id' => $contract->id,
            'status' => 'partial',
        ]);

        $receiptResponse = $this->actingAs($accountant)->get(route('accounting.payments.receipt', ['payment' => $payment->id]));
        $this->assertTrue(in_array($receiptResponse->status(), [200, 302], true));
    }

    public function test_accountant_cannot_create_contract_for_student_from_another_school(): void
    {
        [$accountant] = $this->bootstrapUserWithStudent('accountant');
        [, $schoolB, $studentB] = $this->bootstrapUserWithStudent('admin', 'B');

        $response = $this->actingAs($accountant)->post(route('accounting.contracts.store'), [
            'student_id' => $studentB,
            'academic_year' => '2026-2027',
            'total_amount' => 900,
            'plan_type' => 'yearly',
            'status' => 'active',
        ]);

        $this->assertTrue(in_array($response->status(), [403, 404], true));
        $this->assertDatabaseMissing('student_contracts', [
            'student_id' => $studentB,
            'school_id' => $schoolB,
            'academic_year' => '2026-2027',
        ]);
    }

    public function test_accountant_cannot_update_contract_from_another_school(): void
    {
        [$accountant] = $this->bootstrapUserWithStudent('accountant');
        [, $schoolB, $studentB] = $this->bootstrapUserWithStudent('admin', 'B');

        $contractId = DB::table('student_contracts')->insertGetId([
            'school_id' => $schoolB,
            'student_id' => $studentB,
            'academic_year' => '2026-2027',
            'total_amount' => 1200,
            'plan_type' => 'yearly',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($accountant)->patch(route('accounting.contracts.update', ['contract' => $contractId]), [
            'academic_year' => '2026-2027',
            'total_amount' => 1500,
            'plan_type' => 'yearly',
            'status' => 'active',
        ]);

        $this->assertTrue(in_array($response->status(), [403, 404], true));
        $this->assertDatabaseHas('student_contracts', [
            'id' => $contractId,
            'school_id' => $schoolB,
            'total_amount' => 1200,
        ]);
    }

    public function test_accountant_cannot_view_payment_receipt_from_another_school(): void
    {
        [$accountant] = $this->bootstrapUserWithStudent('accountant');
        [, $schoolB, $studentB] = $this->bootstrapUserWithStudent('admin', 'B');

        $contractId = DB::table('student_contracts')->insertGetId([
            'school_id' => $schoolB,
            'student_id' => $studentB,
            'academic_year' => '2026-2027',
            'total_amount' => 1000,
            'plan_type' => 'yearly',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $paymentId = DB::table('payments')->insertGetId([
            'school_id' => $schoolB,
            'contract_id' => $contractId,
            'receipt_number' => 'ACC-B-2026-001',
            'paid_on' => now()->toDateString(),
            'amount' => 150,
            'payment_method' => 'cash',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('payment_receipts')->insert([
            'school_id' => $schoolB,
            'payment_id' => $paymentId,
            'receipt_code' => 'RCPT-B-2026-001',
            'issued_at' => now(),
            'payload' => json_encode(['amount' => 150]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($accountant)
            ->get(route('accounting.payments.receipt', ['payment' => $paymentId]));

        $this->assertNotSame(200, $response->status(), 'Receipt page should not be accessible across schools.');
        $this->assertTrue(in_array($response->status(), [302, 403, 404], true));
    }

    public function test_user_without_accounting_role_cannot_access_accounting_pages(): void
    {
        $user = User::factory()->create([
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($user)->get(route('accounting.payments.index'));
        $this->assertTrue(in_array($response->status(), [302, 403, 404], true));
    }

    public function test_accountant_can_search_contracts_by_external_contract_number(): void
    {
        [$accountant, $schoolId, $studentId] = $this->bootstrapUserWithStudent('accountant');

        DB::table('student_contracts')->insert([
            'school_id' => $schoolId,
            'student_id' => $studentId,
            'academic_year' => '2026-2027',
            'total_amount' => 1100,
            'plan_type' => 'yearly',
            'status' => 'active',
            'external_contract_no' => 'CN-SEARCH-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $matchedContracts = StudentContract::query()
            ->forSchool($schoolId)
            ->when('CN-SEARCH-001', function ($query) {
                $search = 'CN-SEARCH-001';
                $query->where(function ($wrappedQuery) use ($search) {
                    $wrappedQuery->where('external_contract_no', 'like', '%' . $search . '%')
                        ->orWhereHas('student.user', function ($userQuery) use ($search) {
                            $userQuery->where('name->fr', 'like', '%' . $search . '%')
                                ->orWhere('name->ar', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        });
                });
            })
            ->pluck('external_contract_no')
            ->all();

        $this->assertSame(['CN-SEARCH-001'], $matchedContracts);
    }

    public function test_accountant_can_import_accounting_workbook(): void
    {
        [$accountant, $schoolId] = $this->bootstrapUserWithStudent('accountant');
        $student = $this->createNamedStudentInSchool($schoolId, 'اخوة ابرار');

        $file = $this->buildAccountingWorkbookUpload('اخوة ابرار');

        $response = $this->actingAs($accountant)->post(route('accounting.contracts.import'), [
            'file' => $file,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $contract = DB::table('student_contracts')
            ->where('school_id', $schoolId)
            ->where('external_contract_no', '001')
            ->where('academic_year', '2025-2026')
            ->first();

        $this->assertNotNull($contract);
        $this->assertSame('001', $contract->external_contract_no);
        $this->assertSame('اخوة لطفي', $contract->guardian_name);

        $this->assertDatabaseHas('payments', [
            'school_id' => $schoolId,
            'contract_id' => $contract->id,
            'receipt_number' => 'SUB-320',
            'amount' => 50,
        ]);

        $this->assertDatabaseHas('payments', [
            'school_id' => $schoolId,
            'contract_id' => $contract->id,
            'receipt_number' => '532',
            'amount' => 100,
        ]);
    }

    public function test_accountant_can_preview_import_without_persisting_data(): void
    {
        [$accountant, $schoolId] = $this->bootstrapUserWithStudent('accountant');
        $this->createNamedStudentInSchool($schoolId, 'اخوة ابرار');

        $file = $this->buildAccountingWorkbookUpload('اخوة ابرار');

        $response = $this->actingAs($accountant)->post(route('accounting.contracts.import'), [
            'file' => $file,
            'preview' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('import_preview');
        $response->assertSessionHas('import_preview.summary');
        $response->assertSessionHas('import_preview_csv_url');

        $this->assertDatabaseMissing('student_contracts', [
            'school_id' => $schoolId,
            'external_contract_no' => '001',
            'academic_year' => '2025-2026',
        ]);
    }

    public function test_preview_collects_validation_warnings_when_totals_mismatch(): void
    {
        [$accountant, $schoolId] = $this->bootstrapUserWithStudent('accountant');
        $this->createNamedStudentInSchool($schoolId, 'اخوة ابرار');

        $file = $this->buildAccountingWorkbookUpload('اخوة ابرار', true);

        $response = $this->actingAs($accountant)->post(route('accounting.contracts.import'), [
            'file' => $file,
            'preview' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('import_preview.validation_warnings');
        $response->assertSessionHas('import_preview.summary.warnings_count');
    }

    public function test_import_sets_skipped_rows_report_when_student_not_found(): void
    {
        [$accountant] = $this->bootstrapUserWithStudent('accountant');

        $file = $this->buildAccountingWorkbookUpload('طالب غير موجود');

        $response = $this->actingAs($accountant)->post(route('accounting.contracts.import'), [
            'file' => $file,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('import_report_url');
    }

    private function bootstrapUserWithStudent(string $role, string $suffix = 'A'): array
    {
        $user = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => $role]);
        $user->attachRole($role);

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

        $parentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'Parent ' . $suffix, 'ar' => 'ولي ' . $suffix, 'en' => 'Parent ' . $suffix]),
            'email' => 'parent' . strtolower($suffix) . '-' . uniqid() . '@example.test',
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
            'numtelephonewali' => 550000100 + random_int(1, 99),
            'user_id' => $parentUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'Student ' . $suffix, 'ar' => 'تلميذ ' . $suffix, 'en' => 'Student ' . $suffix]),
            'email' => 'student' . strtolower($suffix) . '-' . uniqid() . '@example.test',
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
            'prenom' => json_encode(['fr' => 'Etudiant', 'ar' => 'تلميذ', 'en' => 'Student']),
            'nom' => json_encode(['fr' => $suffix, 'ar' => $suffix, 'en' => $suffix]),
            'gender' => 1,
            'lieunaissance' => json_encode(['fr' => 'City', 'ar' => 'مدينة', 'en' => 'City']),
            'wilaya' => json_encode(['fr' => 'Wilaya', 'ar' => 'ولاية', 'en' => 'Wilaya']),
            'dayra' => json_encode(['fr' => 'Dayra', 'ar' => 'دائرة', 'en' => 'Dayra']),
            'baladia' => json_encode(['fr' => 'Baladia', 'ar' => 'بلدية', 'en' => 'Baladia']),
            'datenaissance' => '2012-01-01',
            'numtelephone' => 550000200 + random_int(1, 99),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$user, $schoolId, $studentId];
    }

    private function createNamedStudentInSchool(int $schoolId, string $fullName): int
    {
        $sectionId = (int) DB::table('sections')
            ->where('school_id', $schoolId)
            ->value('id');

        $parentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'Parent Import', 'ar' => 'ولي استيراد', 'en' => 'Parent Import']),
            'email' => 'parent-import-' . uniqid() . '@example.test',
            'password' => bcrypt('Secret123!'),
            'school_id' => $schoolId,
            'must_change_password' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $parentId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'Parent', 'ar' => 'ولي', 'en' => 'Parent']),
            'nomwali' => json_encode(['fr' => 'Import', 'ar' => 'استيراد', 'en' => 'Import']),
            'relationetudiant' => 'father',
            'adressewali' => 'Address',
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'numtelephonewali' => 550009999,
            'user_id' => $parentUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => $fullName, 'ar' => $fullName, 'en' => $fullName]),
            'email' => 'student-import-' . uniqid() . '@example.test',
            'password' => bcrypt('Secret123!'),
            'school_id' => $schoolId,
            'must_change_password' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('studentinfos')->insertGetId([
            'user_id' => $studentUserId,
            'section_id' => $sectionId,
            'parent_id' => $parentId,
            'prenom' => json_encode(['fr' => 'Test', 'ar' => 'اختبار', 'en' => 'Test']),
            'nom' => json_encode(['fr' => 'Import', 'ar' => 'استيراد', 'en' => 'Import']),
            'gender' => 1,
            'lieunaissance' => json_encode(['fr' => 'City', 'ar' => 'مدينة', 'en' => 'City']),
            'wilaya' => json_encode(['fr' => 'Wilaya', 'ar' => 'ولاية', 'en' => 'Wilaya']),
            'dayra' => json_encode(['fr' => 'Dayra', 'ar' => 'دائرة', 'en' => 'Dayra']),
            'baladia' => json_encode(['fr' => 'Baladia', 'ar' => 'بلدية', 'en' => 'Baladia']),
            'datenaissance' => '2012-01-01',
            'numtelephone' => 550008888,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function buildAccountingWorkbookUpload(string $studentName, bool $withMismatchedTotals = false): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $contracts = $spreadsheet->getActiveSheet();
        $contracts->setTitle('عقود التلاميذ');

        $contracts->setCellValue('C3', 'تاريخ امضاء العقد');
        $contracts->setCellValue('D3', 'رقم العقد');
        $contracts->setCellValue('F3', 'السنة الدراسية');
        $contracts->setCellValue('G3', 'اسم ولقب التلاميذ');
        $contracts->setCellValue('H3', 'اسم والي');
        $contracts->setCellValue('I3', 'تاريخ ميلاد');
        $contracts->setCellValue('J3', 'رقم الهاتف');
        $contracts->setCellValue('K3', 'سبتمبر');
        $contracts->setCellValue('L3', 'أكتوبر');
        $contracts->setCellValue('T3', 'مجموع');

        $contracts->setCellValue('C4', '18/09/2025');
        $contracts->setCellValue('D4', '001');
        $contracts->setCellValue('F4', '2025/2026');
        $contracts->setCellValue('G4', $studentName);
        $contracts->setCellValue('H4', 'اخوة لطفي');
        $contracts->setCellValue('I4', '01/01/2010');
        $contracts->setCellValue('J4', '0664906565');
        $contracts->setCellValue('K4', 100);
        $contracts->setCellValue('L4', 50);
        $contracts->setCellValue('T4', $withMismatchedTotals ? 170 : 150);

        $money = $spreadsheet->createSheet();
        $money->setTitle('دراهم');
        $money->setCellValue('B2', 'رقم العقد');
        $money->setCellValue('D2', 'السنة الدراسية');
        $money->setCellValue('E2', 'اسم ولقب التلاميذ');
        $money->setCellValue('F2', 'رقم وصل اشتراك');
        $money->setCellValue('G2', 'حقوق الاشتراك');
        $money->setCellValue('H2', 'رقم الوصل 09');
        $money->setCellValue('I2', 'دفعة سبتمبر');
        $money->setCellValue('Z2', 'المجموع الإجمالي');
        $money->setCellValue('AA2', 'مجموع حقوق الاشتراك');
        $money->setCellValue('AB2', 'مجموع دفعات');

        $money->setCellValue('B3', '001');
        $money->setCellValue('D3', '2025/2026');
        $money->setCellValue('E3', $studentName);
        $money->setCellValue('F3', '320');
        $money->setCellValue('G3', 50);
        $money->setCellValue('H3', '532');
        $money->setCellValue('I3', 100);
        $money->setCellValue('Z3', $withMismatchedTotals ? 130 : 150);
        $money->setCellValue('AA3', $withMismatchedTotals ? 40 : 50);
        $money->setCellValue('AB3', $withMismatchedTotals ? 90 : 100);

        $path = tempnam(sys_get_temp_dir(), 'acc-import-') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'accounting-import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }
}
