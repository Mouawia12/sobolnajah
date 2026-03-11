<?php

namespace Tests\Feature\Inscription;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class StudentImportFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_import_returns_progress_summary_and_detects_duplicates(): void
    {
        [$admin, $schoolId, $sectionId] = $this->bootstrapAdminWithSection();
        $this->seedExistingStudent($schoolId, $sectionId);

        $token = 'stimport_test_token_001';
        $file = $this->buildStudentsImportFile($sectionId);

        $response = $this->actingAs($admin)->post(
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

        $response->assertOk();
        $response->assertJson([
            'ok' => true,
            'token' => $token,
        ]);

        $summary = $response->json('summary');
        $this->assertNotNull($summary);
        $this->assertSame(3, (int) ($summary['total_rows'] ?? 0));
        $this->assertSame(3, (int) ($summary['processed_rows'] ?? 0));
        $this->assertSame(1, (int) ($summary['imported_rows'] ?? 0));
        $this->assertGreaterThanOrEqual(2, (int) ($summary['duplicate_rows'] ?? 0));
        $this->assertSame(
            (int) ($summary['duplicate_rows'] ?? 0) + (int) ($summary['skipped_rows'] ?? 0),
            (int) ($summary['not_added_rows'] ?? 0)
        );
    }

    public function test_students_import_status_endpoint_returns_completed_payload(): void
    {
        [$admin, , $sectionId] = $this->bootstrapAdminWithSection();

        $token = 'stimport_status_token_001';
        $file = $this->buildStudentsImportFile($sectionId);

        $this->actingAs($admin)->post(
            route('students.import'),
            [
                'import_token' => $token,
                'file' => $file,
            ],
            [
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ]
        )->assertOk();

        $statusResponse = $this->actingAs($admin)->post(
            route('students.import.status', ['token' => $token]),
            [],
            [
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $statusResponse->assertOk();
        $statusResponse->assertJson([
            'token' => $token,
            'status' => 'completed',
        ]);
    }

    public function test_import_updates_section_when_duplicate_student_is_reuploaded_with_new_section(): void
    {
        [$admin, $schoolId, $sectionA, $sectionB] = $this->bootstrapAdminWithSection();
        $studentId = $this->seedExistingStudent($schoolId, $sectionA);

        $token = 'stimport_section_update_001';
        $file = $this->buildStudentsImportFileWithRows([
            [
                $sectionB, 'Ali', 'Dupont', 'علي', 'دوبون', 'ali.move@example.test', 1, '0555000001', '2012-01-01',
                'الوادي', 'الوادي', 'الوادي', 'الوادي',
                'Wali', 'Dup', 'ولي', 'دوب', 'ولي', 'Adresse', 'الوادي', 'الوادي', 'الوادي', '0666000000', 'wali-move@example.test',
            ],
        ]);

        $response = $this->actingAs($admin)->post(
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

        $response->assertOk();
        $response->assertJsonPath('summary.section_updated_rows', 1);
        $response->assertJsonPath('summary.duplicate_rows', 0);
        $response->assertJsonPath('summary.imported_rows', 0);

        $this->assertDatabaseHas('studentinfos', [
            'id' => $studentId,
            'section_id' => $sectionB,
        ]);
    }

    private function bootstrapAdminWithSection(): array
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School X', 'ar' => 'مدرسة X', 'en' => 'School X']),
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

        $sectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S1', 'ar' => 'ف1', 'en' => 'S1']),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sectionId2 = DB::table('sections')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S2', 'ar' => 'ف2', 'en' => 'S2']),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$admin, $schoolId, $sectionId, $sectionId2];
    }

    private function seedExistingStudent(int $schoolId, int $sectionId): int
    {
        $guardianUser = User::query()->create([
            'name' => ['fr' => 'Guardian Existing', 'ar' => 'ولي موجود', 'en' => 'Guardian Existing'],
            'email' => 'guardian-existing@example.test',
            'password' => bcrypt('password'),
            'must_change_password' => false,
            'school_id' => $schoolId,
        ]);

        Role::firstOrCreate(['name' => 'guardian']);
        $guardianUser->attachRole('guardian');

        $parentId = DB::table('my_parents')->insertGetId([
            'user_id' => $guardianUser->id,
            'prenomwali' => json_encode(['fr' => 'Wali', 'ar' => 'ولي', 'en' => 'Wali']),
            'nomwali' => json_encode(['fr' => 'Existing', 'ar' => 'موجود', 'en' => 'Existing']),
            'relationetudiant' => 'ولي',
            'adressewali' => 'Adresse',
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'numtelephonewali' => '0666000000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentUser = User::query()->create([
            'name' => ['fr' => 'Ali Dup', 'ar' => 'علي مكرر', 'en' => 'Ali Dup'],
            'email' => 'student-existing@example.test',
            'password' => bcrypt('password'),
            'must_change_password' => false,
            'school_id' => $schoolId,
        ]);

        Role::firstOrCreate(['name' => 'student']);
        $studentUser->attachRole('student');

        return DB::table('studentinfos')->insertGetId([
            'user_id' => $studentUser->id,
            'section_id' => $sectionId,
            'parent_id' => $parentId,
            'gender' => 1,
            'prenom' => json_encode(['fr' => 'Ali', 'ar' => 'علي', 'en' => 'Ali']),
            'nom' => json_encode(['fr' => 'Dupont', 'ar' => 'دوبون', 'en' => 'Dupont']),
            'lieunaissance' => 'الوادي',
            'wilaya' => 'الوادي',
            'dayra' => 'الوادي',
            'baladia' => 'الوادي',
            'datenaissance' => '2012-01-01',
            'numtelephone' => '0555000001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function buildStudentsImportFile(int $sectionId): UploadedFile
    {
        return $this->buildStudentsImportFileWithRows([
            [
                $sectionId, 'Ali', 'Dupont', 'علي', 'دوبون', 'ali.new1@example.test', 1, '0555000001', '2012-01-01',
                'الوادي', 'الوادي', 'الوادي', 'الوادي',
                'Wali', 'Dup', 'ولي', 'دوب', 'ولي', 'Adresse', 'الوادي', 'الوادي', 'الوادي', '0666000000', 'wali1@example.test',
            ],
            [
                $sectionId, 'Ali', 'Dupont', 'علي', 'دوبون', 'ali.new2@example.test', 1, '0555000001', '2012-01-01',
                'الوادي', 'الوادي', 'الوادي', 'الوادي',
                'Wali', 'Dup', 'ولي', 'دوب', 'ولي', 'Adresse', 'الوادي', 'الوادي', 'الوادي', '0666000000', 'wali2@example.test',
            ],
            [
                $sectionId, 'Sara', 'Unique', 'سارة', 'فريدة', 'sara.unique@example.test', 0, '0555000009', '2013-02-03',
                'الوادي', 'الوادي', 'الوادي', 'الوادي',
                'Wali', 'Sara', 'ولي', 'سارة', 'ولي', 'Adresse', 'الوادي', 'الوادي', 'الوادي', '0666000009', 'wali3@example.test',
            ],
        ]);
    }

    private function buildStudentsImportFileWithRows(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'section_id',
            'prenom_fr',
            'nom_fr',
            'prenom_ar',
            'nom_ar',
            'email',
            'gender',
            'numtelephone',
            'datenaissance',
            'lieunaissance',
            'wilaya',
            'dayra',
            'baladia',
            'prenom_wali_fr',
            'nom_wali_fr',
            'prenom_wali_ar',
            'nom_wali_ar',
            'relationetudiant',
            'adressewali',
            'wilayawali',
            'dayrawali',
            'baladiawali',
            'numtelephonewali',
            'emailwali',
        ];

        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
        }

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                $sheet->setCellValueByColumnAndRow($columnIndex + 1, $rowIndex + 2, $value);
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'students-import-') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile(
            $path,
            'students-import.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }
}
