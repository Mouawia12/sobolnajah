<?php

namespace Tests\Feature\Accounting;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_user_without_accounting_role_cannot_access_accounting_pages(): void
    {
        $user = User::factory()->create([
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($user)->get(route('accounting.payments.index'));
        $this->assertTrue(in_array($response->status(), [302, 403, 404], true));
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
}
