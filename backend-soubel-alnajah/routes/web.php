<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\School\SchoolController;
use App\Http\Controllers\School\SchoolgradeController;
use App\Http\Controllers\School\ClassroomController;
use App\Http\Controllers\School\SectionController;
use App\Http\Controllers\Inscription\InscriptionController;
use App\Http\Controllers\Inscription\StudentController;
use App\Http\Controllers\Inscription\ParentController;
use App\Http\Controllers\Inscription\TeacherController;
use App\Http\Controllers\AgendaScolaire\AgendaController;
use App\Http\Controllers\AgendaScolaire\GradeController;
use App\Http\Controllers\AgendaScolaire\PublicationController;
use App\Http\Controllers\AgendaScolaire\PublicationMediaController;
use App\Http\Controllers\AgendaScolaire\ExamesController;
use App\Http\Controllers\AgendaScolaire\NoteStudentController;
use App\Http\Controllers\AgendaScolaire\AbsenceController;

use App\Http\Controllers\Promotion\PromotionController;
use App\Http\Controllers\Promotion\GraduatedController;
use App\Http\Controllers\Function\FunctionController;
use App\Http\Controllers\Application\ChatController;
use App\Http\Controllers\OpenAIService\OpenAIServiceController;
use App\Http\Controllers\Recruitment\JobApplicationController;
use App\Http\Controllers\Recruitment\JobPostController;
use App\Http\Controllers\Recruitment\PublicJobController;
use App\Http\Controllers\Timetable\PublicTimetableController;
use App\Http\Controllers\Timetable\TimetableController;
use App\Http\Controllers\Timetable\TeacherScheduleController as AdminTeacherScheduleController;
use App\Http\Controllers\Timetable\PublicTeacherScheduleController;
use App\Http\Controllers\Teacher\TeacherScheduleController as TeacherTeacherScheduleController;
use App\Http\Controllers\Accounting\ContractController;
use App\Http\Controllers\Accounting\PaymentController;
use App\Http\Controllers\Accounting\AccountantDashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\AccountsController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Portal\StudentReportController;
use App\Http\Controllers\Portal\NotificationController;
use App\Http\Controllers\Profile\ProfilePhotoController;
use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\HR\StaffAttendanceController;
use App\Http\Controllers\Academic\AssessmentController;




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => [ 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath' ]
    ], function(){


        Route::get('/', [PublicationController::class, 'siteHome'])->name('landing');
        Route::get('/site-home', [PublicationController::class, 'siteHome'])->name('site.home');
        Route::get('/site/inscriptions', [InscriptionController::class, 'publicIndex'])->name('public.inscriptions.index');
        Route::get('/site/publications', [PublicationController::class, 'publicIndex'])->name('public.publications.index');
        Route::get('/site/exams', [ExamesController::class, 'publicIndex'])->name('public.exams.index');



        Route::get('/login', function () {
            return view('auth.login');
        });
        Route::middleware('guest')->group(function () {
            Route::get('/accountant/login', [LoginController::class, 'showAccountantLoginForm'])->name('accountant.login');
            Route::post('/accountant/login', [LoginController::class, 'loginAccountant'])->name('accountant.login.submit');
        });

        Route::get('/contact', function () {
            return view('front-end.contact');
        });


        Route::get('/about', function () {
            return view('front-end.about');
        });

        Route::post('/logout/{lang}',[App\Http\Controllers\Auth\LoginController::class, 'logout']);



        Route::resources([
            'Inscriptions'=>InscriptionController::class,
            'Publications'=>PublicationController::class,
            'Exames'=>ExamesController::class,


        ]);
        Route::get('/lookup/schools/{id}/grades',[SchoolgradeController::class,'listBySchool'])->name('lookup.schoolGrades');
        Route::get('/lookup/grades/{id}/classes',[ClassroomController::class,'listByGrade'])->name('lookup.gradeClasses');
        Route::get('/lookup/classes/{id}/sections',[SectionController::class,'listByClassroom'])->name('lookup.classSections');
        Route::get('/lookup/sections/{id}',[SectionController::class,'getSectionById'])->name('lookup.sectionById');
        Route::get('/getgrade/{id}',[SchoolgradeController::class,'getGrade'])->name('legacy.lookup.schoolGrades');
        Route::get('/getclasse/{id}',[ClassroomController::class,'getClasse'])->name('legacy.lookup.gradeClasses');
        Route::get('/getsection/{id}',[SectionController::class,'getSection'])->name('legacy.lookup.classSections');
        Route::get('/getsection2/{id}',[SectionController::class,'getSection2'])->name('legacy.lookup.sectionById');



        Route::get('/school-agenda/{id}',[FunctionController::class,'showAgenda'])->name('public.agenda.show');
        Route::get('/agenda/{id}',[FunctionController::class,'getAgenda'])->name('legacy.public.agenda.show');
        Route::get('/media/publications/{filename}', [PublicationMediaController::class, 'show'])
            ->middleware('signed')
            ->where('filename', '[A-Za-z0-9][A-Za-z0-9._-]*')
            ->name('publications.media');
        Route::get('/agenda-grades/{id?}',[FunctionController::class,'listAgendaGrades'])->name('public.agenda.grades');
        Route::get('/getgrades/{id}',[FunctionController::class,'getGrade'])->name('legacy.public.agenda.grades');
        Route::get('/gallery',[FunctionController::class,'showGallery'])->name('public.gallery.index');
        Route::get('/album',[FunctionController::class,'getAlbum'])->name('legacy.public.gallery.index');
        Route::get('/jobs', [PublicJobController::class, 'index'])->name('public.jobs.index');
        Route::get('/jobs/{jobPost:slug}', [PublicJobController::class, 'show'])->name('public.jobs.show');
        Route::post('/jobs/{jobPost:slug}/apply', [PublicJobController::class, 'apply'])
            ->middleware('throttle:6,1')
            ->name('public.jobs.apply');
        Route::get('/school-timetables', [PublicTimetableController::class, 'index'])->name('public.timetables.index');
        Route::get('/school-timetables/{timetable}', [PublicTimetableController::class, 'show'])->name('public.timetables.show');
        Route::get('/school-teacher-schedules', [PublicTeacherScheduleController::class, 'index'])->name('public.teacher_schedules.index');
        Route::get('/school-teacher-schedules/{teacherSchedule}/print', [PublicTeacherScheduleController::class, 'print'])->name('public.teacher_schedules.print');
        Route::get('/school-teacher-schedules/{teacherSchedule}/pdf', [PublicTeacherScheduleController::class, 'pdf'])->name('public.teacher_schedules.pdf');
        Route::get('/school-teacher-schedules/{teacherSchedule}', [PublicTeacherScheduleController::class, 'show'])->name('public.teacher_schedules.show');

        Route::middleware(['auth', 'force.password.change'])->group(function () {
            Route::get('/change-password', [FunctionController::class, 'showChangePasswordPage'])->name('password.change.page');
            Route::get('/changepass', [FunctionController::class, 'changepass'])->name('changepass');
            Route::post('/profile/photo', [ProfilePhotoController::class, 'update'])->name('profile.photo.update');
            Route::get('/reports', [StudentReportController::class, 'index'])
                ->middleware('role:student|guardian')
                ->name('reports.index');
            Route::get('/reports/bulletin/{student}', [StudentReportController::class, 'bulletin'])
                ->middleware('role:student|guardian|admin')
                ->name('reports.bulletin');
            Route::get('/my-notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::post('/my-notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read_all');
            Route::post('/my-notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
            Route::get('/DownloadNoteFromAdmin/{url}', [NoteStudentController::class, 'DownloadNoteFromAdmin'])
                ->middleware('role:admin|student|guardian')
                ->name('DownloadNoteFromAdmin');
            Route::get('/DisplayNoteFromAdmin/{url}', [NoteStudentController::class, 'displayNoteFromAdmin'])
                ->middleware('role:admin|student|guardian')
                ->name('DisplayNoteFromAdmin');
            Route::get('/DisplqyNoteFromAdmin/{url}', [NoteStudentController::class, 'DisplqyNoteFromAdmin'])
                ->middleware('role:admin|student|guardian')
                ->name('DisplqyNoteFromAdmin');

            Route::get('/chat-gpt', [OpenAIServiceController::class, 'index'])->name('chat.ai');
            Route::post('/chat-gpt', [OpenAIServiceController::class, 'send'])->name('chat.send');

            Route::get('/chat', [ChatController::class, 'index'])->name('Chats.index');
            Route::get('/chat/rooms', [ChatController::class, 'listRooms'])->name('chat.rooms.list');
            Route::get('/chat/rooms/{room}/messages', [ChatController::class, 'messages'])->name('chat.rooms.messages');
            Route::post('/chat/rooms/{room}/messages', [ChatController::class, 'sendMessage'])->name('chat.rooms.messages.send');
            Route::post('/chat/rooms/{room}/read', [ChatController::class, 'markRoomAsRead'])->name('chat.rooms.read');
            Route::post('/chat/direct', [ChatController::class, 'startDirect'])->name('chat.direct.start');
            Route::post('/chat/groups', [ChatController::class, 'createGroup'])->name('chat.groups.create');
            Route::get('/chat/users/search', [ChatController::class, 'searchUsers'])->name('chat.users.search');
        });


        // Route::post('/Publications/load_more', [PublicationController::class,'load_more'])->name('Publications.load_more');



        Auth::routes(['register' => false]);

        Route::get('/home', [HomeController::class, 'index'])->name('home');
        Route::post('/notify/{id}', [FunctionController::class, 'notify'])->name('notify');

        Route::Post('changePassword', [ConfirmPasswordController::class, 'studentChangePassword'])->name('changePassword');


        Route::group(['middleware' => ['role:admin','auth','force.password.change','localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function() {
            Route::get('/admin', [HomeController::class, 'index'])->name('admin.dashboard');
            Route::get('/admin/users/create', [UserManagementController::class, 'create'])->name('admin.users.create');
            Route::post('/admin/users', [UserManagementController::class, 'store'])->name('admin.users.store');
            Route::get('/admin/roles', [RolePermissionController::class, 'index'])->name('roles.index');
            Route::post('/admin/roles', [RolePermissionController::class, 'storeRole'])->name('roles.store');
            Route::delete('/admin/roles/{role}', [RolePermissionController::class, 'destroyRole'])->name('roles.destroy');
            Route::post('/admin/roles/permissions', [RolePermissionController::class, 'savePermissions'])->name('roles.permissions.save');
            Route::get('/admin/roles/users-data', [RolePermissionController::class, 'usersData'])->name('roles.users.data');
            Route::post('/admin/roles/users', [RolePermissionController::class, 'storeUser'])->name('roles.users.store');
            Route::post('/admin/roles/users/{user}/roles', [RolePermissionController::class, 'updateUserRoles'])->name('roles.users.roles');
            Route::post('/admin/roles/users/{user}/reset-password', [RolePermissionController::class, 'resetUserPassword'])->name('roles.users.reset');
            Route::delete('/admin/roles/users/{user}', [RolePermissionController::class, 'destroyUser'])->name('roles.users.destroy');
            Route::get('/admin/accounts', [AccountsController::class, 'index'])->name('accounts.index');
            Route::post('/admin/accounts/{user}/reset-password', [AccountsController::class, 'resetPassword'])->name('accounts.reset');
            Route::post('/admin/accounts/{user}/roles', [AccountsController::class, 'updateRoles'])->name('accounts.roles');
            Route::delete('/admin/accounts/{user}', [AccountsController::class, 'destroy'])->name('accounts.destroy');
            Route::post('/mark-as-read/{id}', [FunctionController::class, 'markAsRead'])->name('markAsRead');
            Route::post('/markasread/{id}', [FunctionController::class, 'markAsRead'])->name('markasread');
            Route::post('/delete_all',[ClassroomController::class,'delete_all'])->name('delete_all');
            Route::post('/store/{id}',[FunctionController::class,'store']);
            Route::get('/admin/change-password', [FunctionController::class, 'showChangePasswordPage'])->name('admin.password.change.page');
            Route::get('/Parents/students-search', [ParentController::class, 'searchStudents'])->name('Parents.students.search');
            Route::post('/Parents/merge', [ParentController::class, 'merge'])->name('Parents.merge');
            Route::post('/Parents/{parent}/link-student', [ParentController::class, 'linkStudent'])->name('Parents.link');
            Route::resource('Parents', ParentController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::get('/students/print', [StudentController::class, 'printList'])->name('students.print');
            Route::post('/students/delete-all', [StudentController::class, 'deleteAll'])->name('students.delete_all');
            Route::post('/students/import', [StudentController::class, 'importExcel'])->name('students.import');
            Route::get('/teachers/print', [TeacherController::class, 'printList'])->name('teachers.print');
            Route::get('/admin/inscriptions/print', [InscriptionController::class, 'printList'])->name('Inscriptions.print');
            Route::get('/admin/parents/print', [ParentController::class, 'printList'])->name('Parents.print');
            Route::get('/Schools/print', [SchoolController::class, 'printList'])->name('Schools.print');
            Route::get('/Schoolgrades/print', [SchoolgradeController::class, 'printList'])->name('Schoolgrades.print');
            Route::get('/Classes/print', [ClassroomController::class, 'printList'])->name('Classes.print');
            Route::get('/Sections/print', [SectionController::class, 'printList'])->name('Sections.print');

            // الموارد البشرية: إدارة الموظفين + حضور الموظفين والأساتذة اليومي
            Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
            Route::get('/employees/print', [EmployeeController::class, 'printList'])->name('employees.print');
            Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
            Route::patch('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
            Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
            Route::post('/students/import/status/{token}', [StudentController::class, 'importStatus'])->name('students.import.status');
            Route::get('/absences/print', [AbsenceController::class, 'printList'])->name('absences.print');
            Route::get('/absences/student/{student}/report', [AbsenceController::class, 'studentReport'])->name('absences.student.report');
            Route::get('/absences/student/{student}/report/pdf', [AbsenceController::class, 'studentReportPdf'])->name('absences.student.report.pdf');
            Route::post('/absence/update', [AbsenceController::class, 'storeOrUpdate'])->name('absence.update');
            Route::get('/absences/today', [AbsenceController::class, 'getToday'])->name('absence.today');
            Route::get('/attendance', [AbsenceController::class, 'recordPage'])->name('attendance.record');
            Route::get('/attendance/data', [AbsenceController::class, 'recordData'])->name('attendance.data');
            Route::post('/attendance/bulk', [AbsenceController::class, 'bulkUpdate'])->name('attendance.bulk');
            Route::post('/Inscriptions/{id}/approve', [InscriptionController::class, 'approve'])->name('Inscriptions.approve');
            Route::post('/Inscriptions/{id}/status', [InscriptionController::class, 'updateStatus'])->name('Inscriptions.status');
            Route::post('/Sections/{id}/status', [SectionController::class, 'updateStatus'])->name('Sections.status');
            Route::post('/Sections/{id}/teachers', [SectionController::class, 'syncTeachers'])->name('Sections.teachers');
            Route::get('/recruitment/applications', [JobApplicationController::class, 'index'])->name('recruitment.applications.index');
            Route::patch('/recruitment/applications/{jobApplication}/status', [JobApplicationController::class, 'updateStatus'])->name('recruitment.applications.status');
            Route::get('/recruitment/applications/{jobApplication}/cv', [JobApplicationController::class, 'downloadCv'])->name('recruitment.applications.cv');
            Route::get('/timetable-conflicts', [TimetableController::class, 'conflicts'])->name('timetables.conflicts');
            Route::get('/timetables/{timetable}/print', [TimetableController::class, 'print'])->name('timetables.print');
            Route::get('/teacher-schedules/{teacherSchedule}/print', [AdminTeacherScheduleController::class, 'print'])->name('teacher-schedules.print');
            Route::get('/teacher-schedules/{teacherSchedule}/pdf', [AdminTeacherScheduleController::class, 'pdf'])->name('teacher-schedules.pdf');

            Route::resources([
                'Schools'=>SchoolController::class,
                'Schoolgrades'=>SchoolgradeController::class,
                'Classes'=>ClassroomController::class,
                'Agendas'=>AgendaController::class,
                'Grades'=>GradeController::class,
                'Sections'=>SectionController::class,
                'Students'=>StudentController::class,
                'Teachers'=>TeacherController::class,
                'graduated'=>GraduatedController::class,
                'NoteStudents'=>NoteStudentController::class,
                'Addnotestudents'=>NoteStudentController::class,
                'Absences'=>AbsenceController::class,
                'JobPosts'=>JobPostController::class,
                'timetables'=>TimetableController::class,


            ]);
            Route::resource('teacher-schedules', AdminTeacherScheduleController::class)
                ->parameters(['teacher-schedules' => 'teacherSchedule']);
            Route::resource('Promotions', PromotionController::class)->only(['index', 'store', 'destroy']);



        });

        Route::group(['middleware' => ['role:teacher','auth','force.password.change','localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function() {
            Route::get('/teacher/dashboard', [HomeController::class, 'teacherDashboard'])->name('teacher.dashboard');
            Route::get('/teacher/schedules', [TeacherTeacherScheduleController::class, 'index'])->name('teacher.schedules.index');
            Route::get('/teacher/schedules/{teacherSchedule}/print', [TeacherTeacherScheduleController::class, 'print'])->name('teacher.schedules.print');
            Route::get('/teacher/schedules/{teacherSchedule}/pdf', [TeacherTeacherScheduleController::class, 'pdf'])->name('teacher.schedules.pdf');
            Route::get('/teacher/schedules/{teacherSchedule}', [TeacherTeacherScheduleController::class, 'show'])->name('teacher.schedules.show');
        });

        Route::group(['middleware' => ['role:accountant','auth','force.password.change','localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function() {
            Route::get('/accountant/dashboard', [AccountantDashboardController::class, 'index'])->name('accountant.dashboard');
        });

        // حضور الأساتذة والموظفين (منفصلان) — للمسؤول والناظر
        Route::group(['middleware' => ['role:admin|supervisor','auth','force.password.change','localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function() {
            Route::get('/staff-attendance/{kind}', [StaffAttendanceController::class, 'recordPage'])->where('kind', 'teachers|employees')->name('staff-attendance.record');
            Route::get('/staff-attendance/{kind}/data', [StaffAttendanceController::class, 'recordData'])->where('kind', 'teachers|employees')->name('staff-attendance.data');
            Route::get('/staff-attendance/{kind}/report', [StaffAttendanceController::class, 'report'])->where('kind', 'teachers|employees')->name('staff-attendance.report');
            Route::get('/staff-attendance/{kind}/report/print', [StaffAttendanceController::class, 'reportPrint'])->where('kind', 'teachers|employees')->name('staff-attendance.report.print');
            Route::post('/staff-attendance/update', [StaffAttendanceController::class, 'update'])->name('staff-attendance.update');
            Route::post('/staff-attendance/{kind}/bulk', [StaffAttendanceController::class, 'bulkUpdate'])->where('kind', 'teachers|employees')->name('staff-attendance.bulk');
        });

        // نظام نقاط الفروض والامتحانات — للأستاذ (أقسامه) والمسؤول (الكل)
        Route::group(['middleware' => ['role:admin|teacher','auth','force.password.change','localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function() {
            Route::get('/assessments', [AssessmentController::class, 'index'])->name('assessments.index');
            Route::post('/assessments', [AssessmentController::class, 'store'])->name('assessments.store');
            Route::get('/assessments/results/{section}', [AssessmentController::class, 'results'])->name('assessments.results');
            Route::get('/assessments/{assessment}/marks', [AssessmentController::class, 'marks'])->name('assessments.marks');
            Route::post('/assessments/{assessment}/marks', [AssessmentController::class, 'storeMarks'])->name('assessments.marks.store');
            Route::delete('/assessments/{assessment}', [AssessmentController::class, 'destroy'])->name('assessments.destroy');
        });

        Route::group(['middleware' => ['auth','force.password.change','localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function() {
            Route::get('/accounting', fn () => redirect()->route('accounting.contracts.index'))->name('accounting.dashboard');
            Route::get('/accounting/contracts', [ContractController::class, 'index'])->name('accounting.contracts.index');
            Route::get('/accounting/contracts/student-search', [ContractController::class, 'studentSearch'])->name('accounting.contracts.student-search');
            Route::post('/accounting/contracts', [ContractController::class, 'store'])->name('accounting.contracts.store');
            Route::post('/accounting/contracts/import', [ContractController::class, 'import'])->name('accounting.contracts.import');
            Route::get('/accounting/contracts/print-range', [ContractController::class, 'printRange'])->name('accounting.contracts.print-range');
            Route::get('/accounting/contracts/export-range', [ContractController::class, 'exportRange'])->name('accounting.contracts.export-range');
            Route::get('/accounting/contracts/import-reports/{filename}', [ContractController::class, 'downloadImportReport'])
                ->middleware('signed')
                ->where('filename', '[A-Za-z0-9._-]+\\.csv')
                ->name('accounting.contracts.import.report');
            Route::patch('/accounting/contracts/{contract}', [ContractController::class, 'update'])->name('accounting.contracts.update');
            Route::get('/accounting/contracts/{contract}/print', [ContractController::class, 'print'])->name('accounting.contracts.print');
            Route::get('/accounting/contracts/{contract}/download', [ContractController::class, 'download'])->name('accounting.contracts.download');
            Route::delete('/accounting/contracts/{contract}', [ContractController::class, 'destroy'])->name('accounting.contracts.destroy');

            Route::get('/accounting/payments', [PaymentController::class, 'index'])->name('accounting.payments.index');
            Route::get('/accounting/payments/print', [PaymentController::class, 'printList'])->name('accounting.payments.print');
            Route::get('/accounting/payments/contract-search', [PaymentController::class, 'contractSearch'])->name('accounting.payments.contract-search');
            Route::post('/accounting/payments', [PaymentController::class, 'store'])->name('accounting.payments.store');
            Route::get('/accounting/payments/family-search', [PaymentController::class, 'familySearch'])->name('accounting.payments.family.search');
            Route::get('/accounting/payments/family-data', [PaymentController::class, 'familyData'])->name('accounting.payments.family.data');
            Route::post('/accounting/payments/family', [PaymentController::class, 'storeFamily'])->name('accounting.payments.family.store');
            Route::get('/accounting/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('accounting.payments.receipt');
        });
    });
