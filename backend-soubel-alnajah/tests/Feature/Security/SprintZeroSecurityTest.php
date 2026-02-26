<?php

namespace Tests\Feature\Security;

use App\Models\Role;
use App\Models\Specialization\Specialization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SprintZeroSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_must_change_password_is_redirected_from_protected_routes(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPass123!'),
            'must_change_password' => true,
        ]);

        $response = $this->actingAs($user)->get('/chat-gpt');

        $response->assertStatus(302);
        $response->assertRedirectContains('/ar');
    }

    public function test_changing_password_clears_must_change_password_flag(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPass123!'),
            'must_change_password' => true,
        ]);

        $response = $this->actingAs($user)->post('/changePassword', [
            'password' => 'OldPass123!',
            'newPassword' => 'NewPass123!',
            'confirmNewPassword' => 'NewPass123!',
        ]);

        $response->assertStatus(302);
        $this->assertFalse($user->fresh()->must_change_password);
    }

    public function test_exam_upload_rejects_unsupported_file_types(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);

        Role::create(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'Ecole', 'ar' => 'مدرسة', 'en' => 'School']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolId,
            'name_grade' => json_encode(['fr' => 'Grade 1', 'ar' => 'المستوى 1', 'en' => 'Grade 1']),
            'notes' => json_encode(['fr' => 'Note', 'ar' => 'ملاحظة', 'en' => 'Note']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'Class A', 'ar' => 'قسم أ', 'en' => 'Class A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $specialization = Specialization::create([
            'name' => json_encode(['fr' => 'Math', 'ar' => 'رياضيات', 'en' => 'Math']),
        ]);

        $response = $this->actingAs($admin)->post('/Exames', [
            'name_ar' => 'اختبار',
            'specialization_id' => $specialization->id,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'Annscolaire' => 2026,
            'file_url' => UploadedFile::fake()->create('malware.exe', 10, 'application/octet-stream'),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('file_url');
    }

    public function test_guest_cannot_create_publication(): void
    {
        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'Ecole', 'ar' => 'مدرسة', 'en' => 'School']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $gradeId = DB::table('grades')->insertGetId([
            'name_grades' => json_encode(['fr' => 'Grade', 'ar' => 'الطور', 'en' => 'Grade']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $agendaId = DB::table('agenda')->insertGetId([
            'name_agenda' => json_encode(['fr' => 'Agenda', 'ar' => 'أجندة', 'en' => 'Agenda']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/Publications', [
            'school_id2' => $schoolId,
            'grade_id2' => $gradeId,
            'agenda_id' => $agendaId,
            'titlear' => 'منشور',
            'bodyar' => 'محتوى',
            'img_url' => [UploadedFile::fake()->image('cover.jpg')],
        ]);

        $response->assertStatus(302);
        $response->assertRedirectContains('/login');
    }

    public function test_admin_cannot_update_publication_from_another_school(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);

        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $adminSchoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'Admin School', 'ar' => 'مدرسة الأدمن', 'en' => 'Admin School']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $otherSchoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'Other School', 'ar' => 'مدرسة أخرى', 'en' => 'Other School']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admin->update(['school_id' => $adminSchoolId]);

        $gradeId = DB::table('grades')->insertGetId([
            'name_grades' => json_encode(['fr' => 'Grade', 'ar' => 'طور', 'en' => 'Grade']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $agendaId = DB::table('agenda')->insertGetId([
            'name_agenda' => json_encode(['fr' => 'Agenda', 'ar' => 'أجندة', 'en' => 'Agenda']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $publicationId = DB::table('publications')->insertGetId([
            'school_id' => $otherSchoolId,
            'grade_id' => $gradeId,
            'agenda_id' => $agendaId,
            'title' => json_encode(['ar' => 'قديم', 'fr' => 'Ancien', 'en' => 'Old']),
            'body' => json_encode(['ar' => 'قديم', 'fr' => 'Ancien', 'en' => 'Old']),
            'like' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->patch("/ar/Publications/{$publicationId}", [
            'school_id2' => $otherSchoolId,
            'grade_id2' => $gradeId,
            'agenda_id' => $agendaId,
            'titlear' => 'تحديث غير مسموح',
            'bodyar' => 'تحديث غير مسموح',
            'img_url' => [UploadedFile::fake()->image('x.jpg')],
        ]);

        $response->assertStatus(404);

        $updated = DB::table('publications')->where('id', $publicationId)->first();
        $decodedTitle = json_decode($updated->title, true);
        $this->assertSame('قديم', $decodedTitle['ar'] ?? null);
    }

    public function test_admin_cannot_delete_teacher_from_another_school(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);

        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolA = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $schoolB = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'B', 'ar' => 'ب', 'en' => 'B']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admin->update(['school_id' => $schoolA]);

        $specialization = Specialization::create([
            'name' => json_encode(['fr' => 'Math', 'ar' => 'رياضيات', 'en' => 'Math']),
        ]);

        $teacherUser = User::factory()->create([
            'school_id' => $schoolB,
            'must_change_password' => false,
        ]);

        $teacherId = DB::table('teachers')->insertGetId([
            'user_id' => $teacherUser->id,
            'specialization_id' => $specialization->id,
            'name' => json_encode(['fr' => 'Prof B', 'ar' => 'أستاذ ب', 'en' => 'Prof B']),
            'gender' => 1,
            'joining_date' => '2025-01-01',
            'address' => 'Address',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete("/ar/Teachers/{$teacherId}");

        $this->assertTrue(in_array($response->status(), [302, 403, 404], true));
        $this->assertDatabaseHas('teachers', ['id' => $teacherId]);
    }

    public function test_admin_cannot_approve_inscription_from_another_school(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolA = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $schoolB = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'B', 'ar' => 'ب', 'en' => 'B']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admin->update(['school_id' => $schoolA]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolB,
            'name_grade' => json_encode(['fr' => 'G1', 'ar' => 'م1', 'en' => 'G1']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C1', 'ar' => 'ق1', 'en' => 'C1']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S1', 'ar' => 'ف1', 'en' => 'S1']),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $inscriptionId = DB::table('inscriptions')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'inscriptionetat' => 'new',
            'nomecoleprecedente' => 'x',
            'dernieresection' => 'x',
            'moyensannuels' => 10.5,
            'numeronationaletudiant' => 123456789,
            'prenom' => json_encode(['fr' => 'Ali', 'ar' => 'علي', 'en' => 'Ali']),
            'nom' => json_encode(['fr' => 'Ben', 'ar' => 'بن', 'en' => 'Ben']),
            'email' => 'inscription@example.test',
            'gender' => 1,
            'numtelephone' => 550000001,
            'datenaissance' => '2012-01-01',
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'adresseactuelle' => 'Addr',
            'codepostal' => 16000,
            'residenceactuelle' => 'Res',
            'etatsante' => 'Good',
            'identificationmaladie' => 'No',
            'alfdlprsaldr' => 'None',
            'autresnotes' => null,
            'prenomwali' => json_encode(['fr' => 'Fatima', 'ar' => 'فاطمة', 'en' => 'Fatima']),
            'nomwali' => json_encode(['fr' => 'Ben', 'ar' => 'بن', 'en' => 'Ben']),
            'relationetudiant' => 'mother',
            'adressewali' => 'Addr',
            'numtelephonewali' => 550000002,
            'emailwali' => 'wali@example.test',
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'statu' => 'procec',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('Inscriptions.approve', ['id' => $inscriptionId]), [
            'section_id2' => $sectionId,
        ]);

        $this->assertTrue(in_array($response->status(), [302, 403, 404], true));
        $this->assertDatabaseHas('inscriptions', [
            'id' => $inscriptionId,
            'statu' => 'procec',
        ]);
    }

    public function test_absence_update_rejects_invalid_hour_field(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'S', 'ar' => 'س', 'en' => 'S']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admin->update(['school_id' => $schoolId]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolId,
            'name_grade' => json_encode(['fr' => 'G', 'ar' => 'م', 'en' => 'G']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C', 'ar' => 'ق', 'en' => 'C']),
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

        $studentUser = User::factory()->create([
            'school_id' => $schoolId,
            'must_change_password' => false,
        ]);

        $parentUser = User::factory()->create([
            'school_id' => $schoolId,
            'must_change_password' => false,
        ]);

        $parentId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'P', 'ar' => 'و', 'en' => 'P']),
            'nomwali' => json_encode(['fr' => 'L', 'ar' => 'ل', 'en' => 'L']),
            'relationetudiant' => 'father',
            'adressewali' => 'Addr',
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'numtelephonewali' => 550000111,
            'user_id' => $parentUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentId = DB::table('studentinfos')->insertGetId([
            'user_id' => $studentUser->id,
            'section_id' => $sectionId,
            'parent_id' => $parentId,
            'gender' => 1,
            'prenom' => json_encode(['fr' => 'Ali', 'ar' => 'علي', 'en' => 'Ali']),
            'nom' => json_encode(['fr' => 'Ben', 'ar' => 'بن', 'en' => 'Ben']),
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'datenaissance' => '2012-01-01',
            'numtelephone' => 550000112,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('absence.update'), [
            'student_id' => $studentId,
            'hour' => 'hour_99',
            'status' => true,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('hour');
    }

    public function test_state_changing_admin_routes_reject_get_method(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $getDeleteAll = $this->actingAs($admin)->get(route('delete_all'));
        $getMarkAsRead = $this->actingAs($admin)->get(route('markasread', ['id' => 'fake-id']));

        $this->assertTrue(in_array($getDeleteAll->getStatusCode(), [404, 405], true));
        $this->assertTrue(in_array($getMarkAsRead->getStatusCode(), [404, 405], true));
    }

    public function test_promotion_store_rejects_same_source_and_target_section(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School', 'ar' => 'مدرسة', 'en' => 'School']),
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

        $response = $this->actingAs($admin)->post(route('Promotions.store'), [
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'section_id' => $sectionId,
            'school_id_new' => $schoolId,
            'grade_id_new' => $gradeId,
            'classroom_id_new' => $classroomId,
            'section_id_new' => $sectionId,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('section_id_new');
    }

    public function test_students_import_rejects_non_excel_files(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $response = $this->actingAs($admin)->post(route('students.import'), [
            'file' => UploadedFile::fake()->create('students.csv', 20, 'text/csv'),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('file');
    }

    public function test_download_note_rejects_unsafe_filename_pattern(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $response = $this->actingAs($admin)->get('/ar/DownloadNoteFromAdmin/bad%20name.pdf');

        $response->assertStatus(404);
    }

    public function test_admin_cannot_download_note_from_another_school(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolA = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $schoolB = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'B', 'ar' => 'ب', 'en' => 'B']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admin->update(['school_id' => $schoolA]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolB,
            'name_grade' => json_encode(['fr' => 'G1', 'ar' => 'م1', 'en' => 'G1']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C1', 'ar' => 'ق1', 'en' => 'C1']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $sectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S1', 'ar' => 'ف1', 'en' => 'S1']),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentUser = User::factory()->create([
            'school_id' => $schoolB,
            'must_change_password' => false,
        ]);
        $parentUser = User::factory()->create([
            'school_id' => $schoolB,
            'must_change_password' => false,
        ]);

        $parentId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'P', 'ar' => 'و', 'en' => 'P']),
            'nomwali' => json_encode(['fr' => 'L', 'ar' => 'ل', 'en' => 'L']),
            'relationetudiant' => 'father',
            'adressewali' => 'Addr',
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'numtelephonewali' => 550000211,
            'user_id' => $parentUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentId = DB::table('studentinfos')->insertGetId([
            'user_id' => $studentUser->id,
            'section_id' => $sectionId,
            'parent_id' => $parentId,
            'gender' => 1,
            'prenom' => json_encode(['fr' => 'Ali', 'ar' => 'علي', 'en' => 'Ali']),
            'nom' => json_encode(['fr' => 'Ben', 'ar' => 'بن', 'en' => 'Ben']),
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'datenaissance' => '2012-01-01',
            'numtelephone' => 550000212,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $fileName = 'note_secure_test.pdf';
        DB::table('note_students')->insert([
            'student_id' => $studentId,
            'urlfile1' => $fileName,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get("/ar/DownloadNoteFromAdmin/{$fileName}");

        $this->assertTrue(in_array($response->status(), [403, 404], true));
    }

    public function test_admin_cannot_mark_notification_from_another_user_as_read(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $otherUser = User::factory()->create([
            'must_change_password' => false,
        ]);

        $notificationId = (string) \Illuminate\Support\Str::uuid();
        DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'App\\Notifications\\StudentSchoolCertificateNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $otherUser->id,
            'data' => json_encode(['namefr' => 'Other', 'namear' => 'اخر']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('markasread', ['id' => $notificationId]));

        $response->assertStatus(404);
    }

    public function test_admin_cannot_mark_notification_from_another_user_as_read_via_canonical_route(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $otherUser = User::factory()->create([
            'must_change_password' => false,
        ]);

        $notificationId = (string) \Illuminate\Support\Str::uuid();
        DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'App\\Notifications\\StudentSchoolCertificateNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $otherUser->id,
            'data' => json_encode(['namefr' => 'Other', 'namear' => 'اخر']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('markAsRead', ['id' => $notificationId]));

        $response->assertStatus(404);
    }

    public function test_admin_cannot_update_absence_for_student_from_another_school(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolA = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $schoolB = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'B', 'ar' => 'ب', 'en' => 'B']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolA]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolB,
            'name_grade' => json_encode(['fr' => 'G1', 'ar' => 'م1', 'en' => 'G1']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C1', 'ar' => 'ق1', 'en' => 'C1']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $sectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S1', 'ar' => 'ف1', 'en' => 'S1']),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentUser = User::factory()->create([
            'school_id' => $schoolB,
            'must_change_password' => false,
        ]);
        $parentUser = User::factory()->create([
            'school_id' => $schoolB,
            'must_change_password' => false,
        ]);
        $parentId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'P', 'ar' => 'و', 'en' => 'P']),
            'nomwali' => json_encode(['fr' => 'L', 'ar' => 'ل', 'en' => 'L']),
            'relationetudiant' => 'father',
            'adressewali' => 'Addr',
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'numtelephonewali' => 550000311,
            'user_id' => $parentUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $studentId = DB::table('studentinfos')->insertGetId([
            'user_id' => $studentUser->id,
            'section_id' => $sectionId,
            'parent_id' => $parentId,
            'gender' => 1,
            'prenom' => json_encode(['fr' => 'Ali', 'ar' => 'علي', 'en' => 'Ali']),
            'nom' => json_encode(['fr' => 'Ben', 'ar' => 'بن', 'en' => 'Ben']),
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'datenaissance' => '2012-01-01',
            'numtelephone' => 550000312,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('absence.update'), [
            'student_id' => $studentId,
            'hour' => 'hour_1',
            'status' => true,
        ]);

        $response->assertStatus(404);
        $this->assertDatabaseMissing('absences', [
            'student_id' => $studentId,
            'date' => now()->toDateString(),
        ]);
    }

    public function test_admin_cannot_delete_schoolgrade_from_another_school(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolA = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $schoolB = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'B', 'ar' => 'ب', 'en' => 'B']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolA]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolB,
            'name_grade' => json_encode(['fr' => 'G2', 'ar' => 'م2', 'en' => 'G2']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete("/ar/Schoolgrades/{$gradeId}");

        $this->assertTrue(in_array($response->status(), [403, 404], true));
        $this->assertDatabaseHas('schoolgrades', ['id' => $gradeId]);
    }

    public function test_admin_cannot_delete_school_from_another_school(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolA = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $schoolB = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'B', 'ar' => 'ب', 'en' => 'B']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolA]);

        $response = $this->actingAs($admin)->delete("/ar/Schools/{$schoolB}");

        $this->assertTrue(in_array($response->status(), [403, 404], true));
        $this->assertDatabaseHas('schools', ['id' => $schoolB]);
    }

    public function test_admin_cannot_delete_section_from_another_school(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolA = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $schoolB = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'B', 'ar' => 'ب', 'en' => 'B']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolA]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolB,
            'name_grade' => json_encode(['fr' => 'G3', 'ar' => 'م3', 'en' => 'G3']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C3', 'ar' => 'ق3', 'en' => 'C3']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $sectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S3', 'ar' => 'ف3', 'en' => 'S3']),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete("/ar/Sections/{$sectionId}");

        $this->assertTrue(in_array($response->status(), [403, 404], true));
        $this->assertDatabaseHas('sections', ['id' => $sectionId]);
    }

    public function test_admin_cannot_delete_classroom_from_another_school(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolA = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $schoolB = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'B', 'ar' => 'ب', 'en' => 'B']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolA]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolB,
            'name_grade' => json_encode(['fr' => 'G4', 'ar' => 'م4', 'en' => 'G4']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C4', 'ar' => 'ق4', 'en' => 'C4']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete("/ar/Classes/{$classroomId}");

        $this->assertTrue(in_array($response->status(), [403, 404], true));
        $this->assertDatabaseHas('classrooms', ['id' => $classroomId]);
    }

    public function test_non_admin_cannot_create_grade(): void
    {
        $user = User::factory()->create([
            'must_change_password' => false,
        ]);
        $beforeCount = DB::table('grades')->count();

        $response = $this->actingAs($user)->post('/ar/Grades', [
            'name_gradesar' => 'مستوى أمني',
        ]);

        $this->assertTrue(in_array($response->status(), [302, 403, 404], true));
        $this->assertSame($beforeCount, DB::table('grades')->count());
    }

    public function test_admin_cannot_restore_graduated_student_from_another_school(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolA = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $schoolB = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'B', 'ar' => 'ب', 'en' => 'B']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolA]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolB,
            'name_grade' => json_encode(['fr' => 'G5', 'ar' => 'م5', 'en' => 'G5']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C5', 'ar' => 'ق5', 'en' => 'C5']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $sectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S5', 'ar' => 'ف5', 'en' => 'S5']),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentUser = User::factory()->create([
            'school_id' => $schoolB,
            'must_change_password' => false,
        ]);
        $parentUser = User::factory()->create([
            'school_id' => $schoolB,
            'must_change_password' => false,
        ]);
        $parentId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'P', 'ar' => 'و', 'en' => 'P']),
            'nomwali' => json_encode(['fr' => 'L', 'ar' => 'ل', 'en' => 'L']),
            'relationetudiant' => 'father',
            'adressewali' => 'Addr',
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'numtelephonewali' => 550000511,
            'user_id' => $parentUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $studentId = DB::table('studentinfos')->insertGetId([
            'user_id' => $studentUser->id,
            'section_id' => $sectionId,
            'parent_id' => $parentId,
            'gender' => 1,
            'prenom' => json_encode(['fr' => 'Ali', 'ar' => 'علي', 'en' => 'Ali']),
            'nom' => json_encode(['fr' => 'Ben', 'ar' => 'بن', 'en' => 'Ben']),
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'datenaissance' => '2012-01-01',
            'numtelephone' => 550000512,
            'deleted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->put("/ar/graduated/{$studentId}");

        $this->assertTrue(in_array($response->status(), [403, 404], true));
        $this->assertDatabaseMissing('studentinfos', [
            'id' => $studentId,
            'deleted_at' => null,
        ]);
    }

    public function test_admin_cannot_approve_inscription_from_another_school_via_legacy_store_route(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolA = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $schoolB = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'B', 'ar' => 'ب', 'en' => 'B']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolA]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolB,
            'name_grade' => json_encode(['fr' => 'G6', 'ar' => 'م6', 'en' => 'G6']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C6', 'ar' => 'ق6', 'en' => 'C6']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $inscriptionId = DB::table('inscriptions')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'inscriptionetat' => 'new',
            'nomecoleprecedente' => 'x',
            'dernieresection' => 'x',
            'moyensannuels' => 10.5,
            'numeronationaletudiant' => 123456799,
            'prenom' => json_encode(['fr' => 'Ali', 'ar' => 'علي', 'en' => 'Ali']),
            'nom' => json_encode(['fr' => 'Ben', 'ar' => 'بن', 'en' => 'Ben']),
            'email' => 'legacy-store@example.test',
            'gender' => 1,
            'numtelephone' => 550000601,
            'datenaissance' => '2012-01-01',
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'adresseactuelle' => 'Addr',
            'codepostal' => 16000,
            'residenceactuelle' => 'Res',
            'etatsante' => 'Good',
            'identificationmaladie' => 'No',
            'alfdlprsaldr' => 'None',
            'autresnotes' => null,
            'prenomwali' => json_encode(['fr' => 'Fatima', 'ar' => 'فاطمة', 'en' => 'Fatima']),
            'nomwali' => json_encode(['fr' => 'Ben', 'ar' => 'بن', 'en' => 'Ben']),
            'relationetudiant' => 'mother',
            'adressewali' => 'Addr',
            'numtelephonewali' => 550000602,
            'emailwali' => 'wali-legacy@example.test',
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'statu' => 'procec',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post("/ar/store/{$inscriptionId}");

        $this->assertTrue(in_array($response->status(), [403, 404], true));
        $this->assertDatabaseHas('inscriptions', [
            'id' => $inscriptionId,
            'statu' => 'procec',
        ]);
    }

    public function test_admin_cannot_delete_inscription_from_another_school(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolA = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $schoolB = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'B', 'ar' => 'ب', 'en' => 'B']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolA]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolB,
            'name_grade' => json_encode(['fr' => 'G9', 'ar' => 'م9', 'en' => 'G9']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C9', 'ar' => 'ق9', 'en' => 'C9']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $inscriptionId = DB::table('inscriptions')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'inscriptionetat' => 'new',
            'nomecoleprecedente' => 'x',
            'dernieresection' => 'x',
            'moyensannuels' => 10.5,
            'numeronationaletudiant' => 123456992,
            'prenom' => json_encode(['fr' => 'Ali', 'ar' => 'علي', 'en' => 'Ali']),
            'nom' => json_encode(['fr' => 'Ben', 'ar' => 'بن', 'en' => 'Ben']),
            'email' => 'ins-del@example.test',
            'gender' => 1,
            'numtelephone' => 550001101,
            'datenaissance' => '2012-01-01',
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'adresseactuelle' => 'Addr',
            'codepostal' => 16000,
            'residenceactuelle' => 'Res',
            'etatsante' => 'Good',
            'identificationmaladie' => 'No',
            'alfdlprsaldr' => 'None',
            'autresnotes' => null,
            'prenomwali' => json_encode(['fr' => 'Fatima', 'ar' => 'فاطمة', 'en' => 'Fatima']),
            'nomwali' => json_encode(['fr' => 'Ben', 'ar' => 'بن', 'en' => 'Ben']),
            'relationetudiant' => 'mother',
            'adressewali' => 'Addr',
            'numtelephonewali' => 550001102,
            'emailwali' => 'wali-ins-del@example.test',
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'statu' => 'procec',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete("/ar/Inscriptions/{$inscriptionId}");

        $this->assertTrue(in_array($response->status(), [403, 404], true));
        $this->assertDatabaseHas('inscriptions', ['id' => $inscriptionId]);
    }

    public function test_get_edit_routes_do_not_change_inscription_or_section_status(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'SEC', 'ar' => 'س', 'en' => 'SEC']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolId]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolId,
            'name_grade' => json_encode(['fr' => 'G7', 'ar' => 'م7', 'en' => 'G7']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C7', 'ar' => 'ق7', 'en' => 'C7']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $sectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S7', 'ar' => 'ف7', 'en' => 'S7']),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $inscriptionId = DB::table('inscriptions')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'inscriptionetat' => 'new',
            'nomecoleprecedente' => 'x',
            'dernieresection' => 'x',
            'moyensannuels' => 11.1,
            'numeronationaletudiant' => 123456889,
            'prenom' => json_encode(['fr' => 'Ali', 'ar' => 'علي', 'en' => 'Ali']),
            'nom' => json_encode(['fr' => 'Ben', 'ar' => 'بن', 'en' => 'Ben']),
            'email' => 'edit-check@example.test',
            'gender' => 1,
            'numtelephone' => 550000701,
            'datenaissance' => '2012-01-01',
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'adresseactuelle' => 'Addr',
            'codepostal' => 16000,
            'residenceactuelle' => 'Res',
            'etatsante' => 'Good',
            'identificationmaladie' => 'No',
            'alfdlprsaldr' => 'None',
            'autresnotes' => null,
            'prenomwali' => json_encode(['fr' => 'Fatima', 'ar' => 'فاطمة', 'en' => 'Fatima']),
            'nomwali' => json_encode(['fr' => 'Ben', 'ar' => 'بن', 'en' => 'Ben']),
            'relationetudiant' => 'mother',
            'adressewali' => 'Addr',
            'numtelephonewali' => 550000702,
            'emailwali' => 'wali-edit@example.test',
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'statu' => 'procec',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $approveViaShowResponse = $this->actingAs($admin)->get("/ar/Inscriptions/{$inscriptionId}?section_id2={$sectionId}");
        $inscriptionResponse = $this->actingAs($admin)->get("/ar/Inscriptions/{$inscriptionId}/edit?statu=accept");
        $sectionResponse = $this->actingAs($admin)->get("/ar/Sections/{$sectionId}/edit?statu=0");

        $this->assertTrue(in_array($approveViaShowResponse->status(), [404, 405], true));
        $this->assertTrue(in_array($inscriptionResponse->status(), [404, 405], true));
        $this->assertTrue(in_array($sectionResponse->status(), [404, 405], true));
        $this->assertDatabaseHas('inscriptions', [
            'id' => $inscriptionId,
            'statu' => 'procec',
        ]);
        $this->assertDatabaseHas('sections', [
            'id' => $sectionId,
            'Status' => 1,
        ]);
    }

    public function test_guest_cannot_access_inscription_approve_endpoint(): void
    {
        $response = $this->post('/ar/Inscriptions/1/approve', [
            'section_id2' => 1,
        ]);

        $this->assertTrue(in_array($response->status(), [302, 401, 403, 404], true));
    }

    public function test_bulk_delete_inscriptions_is_scoped_to_admin_school_only(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolA = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $schoolB = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'B', 'ar' => 'ب', 'en' => 'B']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolA]);

        $gradeA = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolA,
            'name_grade' => json_encode(['fr' => 'GA', 'ar' => 'أ', 'en' => 'GA']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $gradeB = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolB,
            'name_grade' => json_encode(['fr' => 'GB', 'ar' => 'ب', 'en' => 'GB']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $classroomA = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolA,
            'grade_id' => $gradeA,
            'name_class' => json_encode(['fr' => 'CA', 'ar' => 'أ', 'en' => 'CA']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $classroomB = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeB,
            'name_class' => json_encode(['fr' => 'CB', 'ar' => 'ب', 'en' => 'CB']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $inscriptionA = DB::table('inscriptions')->insertGetId([
            'school_id' => $schoolA,
            'grade_id' => $gradeA,
            'classroom_id' => $classroomA,
            'inscriptionetat' => 'new',
            'nomecoleprecedente' => 'x',
            'dernieresection' => 'x',
            'moyensannuels' => 10.5,
            'numeronationaletudiant' => 123456990,
            'prenom' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']),
            'nom' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']),
            'email' => 'a-bulk@example.test',
            'gender' => 1,
            'numtelephone' => 550001001,
            'datenaissance' => '2012-01-01',
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'adresseactuelle' => 'Addr',
            'codepostal' => 16000,
            'residenceactuelle' => 'Res',
            'etatsante' => 'Good',
            'identificationmaladie' => 'No',
            'alfdlprsaldr' => 'None',
            'autresnotes' => null,
            'prenomwali' => json_encode(['fr' => 'WA', 'ar' => 'وأ', 'en' => 'WA']),
            'nomwali' => json_encode(['fr' => 'WA', 'ar' => 'وأ', 'en' => 'WA']),
            'relationetudiant' => 'father',
            'adressewali' => 'Addr',
            'numtelephonewali' => 550001002,
            'emailwali' => 'wa-bulk@example.test',
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'statu' => 'procec',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $inscriptionB = DB::table('inscriptions')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeB,
            'classroom_id' => $classroomB,
            'inscriptionetat' => 'new',
            'nomecoleprecedente' => 'x',
            'dernieresection' => 'x',
            'moyensannuels' => 10.5,
            'numeronationaletudiant' => 123456991,
            'prenom' => json_encode(['fr' => 'B', 'ar' => 'ب', 'en' => 'B']),
            'nom' => json_encode(['fr' => 'B', 'ar' => 'ب', 'en' => 'B']),
            'email' => 'b-bulk@example.test',
            'gender' => 1,
            'numtelephone' => 550001003,
            'datenaissance' => '2012-01-01',
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'adresseactuelle' => 'Addr',
            'codepostal' => 16000,
            'residenceactuelle' => 'Res',
            'etatsante' => 'Good',
            'identificationmaladie' => 'No',
            'alfdlprsaldr' => 'None',
            'autresnotes' => null,
            'prenomwali' => json_encode(['fr' => 'WB', 'ar' => 'وب', 'en' => 'WB']),
            'nomwali' => json_encode(['fr' => 'WB', 'ar' => 'وب', 'en' => 'WB']),
            'relationetudiant' => 'father',
            'adressewali' => 'Addr',
            'numtelephonewali' => 550001004,
            'emailwali' => 'wb-bulk@example.test',
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'statu' => 'procec',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('delete_all'), [
            'delete_all_id' => $inscriptionA . ',' . $inscriptionB,
        ]);

        $this->assertTrue(in_array($response->status(), [302, 303], true));
        $this->assertDatabaseMissing('inscriptions', ['id' => $inscriptionA]);
        $this->assertDatabaseHas('inscriptions', ['id' => $inscriptionB]);
    }

    public function test_admin_cannot_delete_exame_from_another_school(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolA = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $schoolB = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'B', 'ar' => 'ب', 'en' => 'B']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolA]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolB,
            'name_grade' => json_encode(['fr' => 'G8', 'ar' => 'م8', 'en' => 'G8']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C8', 'ar' => 'ق8', 'en' => 'C8']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $specializationId = DB::table('specializations')->insertGetId([
            'name' => json_encode(['fr' => 'Math', 'ar' => 'رياضيات', 'en' => 'Math']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $exameId = DB::table('exames')->insertGetId([
            'name' => json_encode(['fr' => 'Exam', 'ar' => 'اختبار', 'en' => 'Exam']),
            'specialization_id' => $specializationId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'Annscolaire' => 2026,
            'file' => 'private/exames/test.pdf',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete("/ar/Exames/{$exameId}");

        $this->assertTrue(in_array($response->status(), [403, 404], true));
        $this->assertDatabaseHas('exames', ['id' => $exameId]);
    }

    public function test_guest_cannot_access_exames_create_page(): void
    {
        $response = $this->get('/ar/Exames/create');
        $this->assertTrue(in_array($response->status(), [302, 401, 403, 404], true));
    }

    public function test_guest_cannot_access_publications_create_page(): void
    {
        $response = $this->get('/ar/Publications/create');
        $this->assertTrue(in_array($response->status(), [302, 401, 403, 404], true));
    }

    public function test_guest_cannot_access_note_students_show_canonical_route(): void
    {
        $response = $this->get('/ar/NoteStudents/1');
        $this->assertTrue(in_array($response->status(), [302, 401, 403, 404], true));
    }

    public function test_guest_cannot_access_change_password_canonical_route(): void
    {
        $response = $this->get('/ar/change-password');
        $this->assertTrue(in_array($response->status(), [302, 401, 403, 404], true));
    }

    public function test_guest_can_reach_public_agenda_grades_canonical_route(): void
    {
        $response = $this->get('/ar/agenda-grades/1');
        $this->assertTrue(in_array($response->status(), [200, 302, 404], true));
    }

    public function test_guest_can_reach_public_gallery_canonical_route(): void
    {
        $response = $this->get(route('public.gallery.index'));
        $this->assertTrue(in_array($response->status(), [200, 301, 302, 303], true));
    }

    public function test_guest_can_reach_public_agenda_show_canonical_route(): void
    {
        $response = $this->get(route('public.agenda.show', ['id' => 1]));
        $this->assertTrue(in_array($response->status(), [200, 301, 302, 303], true));
    }
}
