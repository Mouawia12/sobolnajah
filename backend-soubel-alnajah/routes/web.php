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


        Route::get('/', [PublicationController::class, 'welcome']);



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



        Auth::routes();

        Route::get('/home', [HomeController::class, 'index'])->name('home');
        Route::post('/notify/{id}', [FunctionController::class, 'notify'])->name('notify');

        Route::Post('changePassword', [ConfirmPasswordController::class, 'studentChangePassword'])->name('changePassword');


        Route::group(['middleware' => ['role:admin','auth','force.password.change','localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function() {
            Route::get('/admin', [HomeController::class, 'index'])->name('admin.dashboard');
            Route::get('/admin/users/create', [UserManagementController::class, 'create'])->name('admin.users.create');
            Route::post('/admin/users', [UserManagementController::class, 'store'])->name('admin.users.store');
            Route::post('/mark-as-read/{id}', [FunctionController::class, 'markAsRead'])->name('markAsRead');
            Route::post('/markasread/{id}', [FunctionController::class, 'markAsRead'])->name('markasread');
            Route::post('/delete_all',[ClassroomController::class,'delete_all'])->name('delete_all');
            Route::post('/store/{id}',[FunctionController::class,'store']);
            Route::get('/admin/change-password', [FunctionController::class, 'showChangePasswordPage'])->name('admin.password.change.page');
            Route::get('/DownloadNoteFromAdmin/{url}', [NoteStudentController::class, 'DownloadNoteFromAdmin'])->name('DownloadNoteFromAdmin');
            Route::get('/DisplayNoteFromAdmin/{url}', [NoteStudentController::class, 'displayNoteFromAdmin'])->name('DisplayNoteFromAdmin');
            Route::get('/DisplqyNoteFromAdmin/{url}', [NoteStudentController::class, 'DisplqyNoteFromAdmin'])->name('DisplqyNoteFromAdmin');
            Route::post('/students/import', [StudentController::class, 'importExcel'])->name('students.import');
            Route::post('/absence/update', [AbsenceController::class, 'storeOrUpdate'])->name('absence.update');
            Route::get('/absences/today', [AbsenceController::class, 'getToday'])->name('absence.today');
            Route::post('/Inscriptions/{id}/approve', [InscriptionController::class, 'approve'])->name('Inscriptions.approve');
            Route::post('/Inscriptions/{id}/status', [InscriptionController::class, 'updateStatus'])->name('Inscriptions.status');
            Route::post('/Sections/{id}/status', [SectionController::class, 'updateStatus'])->name('Sections.status');
            Route::post('/Sections/{id}/teachers', [SectionController::class, 'syncTeachers'])->name('Sections.teachers');
            Route::get('/recruitment/applications', [JobApplicationController::class, 'index'])->name('recruitment.applications.index');
            Route::patch('/recruitment/applications/{jobApplication}/status', [JobApplicationController::class, 'updateStatus'])->name('recruitment.applications.status');
            Route::get('/recruitment/applications/{jobApplication}/cv', [JobApplicationController::class, 'downloadCv'])->name('recruitment.applications.cv');
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

        Route::group(['middleware' => ['auth','force.password.change','localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function() {
            Route::get('/accounting', fn () => redirect()->route('accounting.contracts.index'))->name('accounting.dashboard');
            Route::get('/accounting/contracts', [ContractController::class, 'index'])->name('accounting.contracts.index');
            Route::post('/accounting/contracts', [ContractController::class, 'store'])->name('accounting.contracts.store');
            Route::post('/accounting/contracts/import', [ContractController::class, 'import'])->name('accounting.contracts.import');
            Route::get('/accounting/contracts/import-reports/{filename}', [ContractController::class, 'downloadImportReport'])
                ->middleware('signed')
                ->where('filename', '[A-Za-z0-9._-]+\\.csv')
                ->name('accounting.contracts.import.report');
            Route::patch('/accounting/contracts/{contract}', [ContractController::class, 'update'])->name('accounting.contracts.update');

            Route::get('/accounting/payments', [PaymentController::class, 'index'])->name('accounting.payments.index');
            Route::post('/accounting/payments', [PaymentController::class, 'store'])->name('accounting.payments.store');
            Route::get('/accounting/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('accounting.payments.receipt');
        });
    });
