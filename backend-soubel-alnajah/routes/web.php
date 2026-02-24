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
use App\Http\Controllers\AgendaScolaire\ExamesController;
use App\Http\Controllers\AgendaScolaire\NoteStudentController;
use App\Http\Controllers\AgendaScolaire\AbsenceController;

use App\Http\Controllers\Promotion\PromotionController;
use App\Http\Controllers\Promotion\GraduatedController;
use App\Http\Controllers\Functionn\FunctionController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Application\ChatController;
use App\Http\Controllers\OpenAIService\OpenAIServiceController;




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
        Route::get('/getgrade/{id}',[SchoolgradeController::class,'getGrade']);
        Route::get('/getclasse/{id}',[ClassroomController::class,'getClasse']);
        Route::get('/getsection/{id}',[SectionController::class,'getSection']);
        Route::get('/getsection2/{id}',[SectionController::class,'getSection2']);
        Route::get('delete_all',[ClassroomController::class,'delete_all'])->name('delete_all');



        Route::get('/agenda/{id}',[FunctionController::class,'getAgenda']);
        Route::get('/getgrades/{id}',[FunctionController::class,'getGrade']);
        Route::get('/album',[FunctionController::class,'getAlbum']);

        Route::middleware('auth')->group(function () {
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
        Route::get('/notify/{id}', [FunctionController::class, 'notify'])->name('notify');

        Route::Post('changePassword', [ConfirmPasswordController::class, 'studentChangePassword'])->name('changePassword');


        Route::group(['middleware' => ['role:admin','auth','localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function() {
            Route::get('/admin', [AdminController::class, 'indexadmin']);
            Route::get('/markasread/{id}', [FunctionController::class, 'markasread'])->name('markasread');
            Route::get('/store/{id}',[FunctionController::class,'store']);
            Route::get('/changepass', [FunctionController::class, 'changepass'])->name('changepass');
            Route::get('/DownloadNoteFromAdmin/{url}', [NoteStudentController::class, 'DownloadNoteFromAdmin'])->name('DownloadNoteFromAdmin');
            Route::get('/DisplqyNoteFromAdmin/{url}', [NoteStudentController::class, 'DisplqyNoteFromAdmin'])->name('DisplqyNoteFromAdmin');
            Route::post('/students/import', [StudentController::class, 'importExcel'])->name('students.import');
            Route::post('/absence/update', [AbsenceController::class, 'storeOrUpdate'])->name('absence.update');
            Route::get('/absences/today', [AbsenceController::class, 'getToday'])->name('absence.today');

            Route::resources([
                'Schools'=>SchoolController::class,
                'Schoolgrades'=>SchoolgradeController::class,
                'Classes'=>ClassroomController::class,
                'Agendas'=>AgendaController::class,
                'Grades'=>GradeController::class,
                'Sections'=>SectionController::class,
                'Students'=>StudentController::class,
                'Teachers'=>TeacherController::class,
                'Promotions'=>PromotionController::class,
                'graduated'=>GraduatedController::class,
                'Addnotestudents'=>NoteStudentController::class,
                'Absences'=>AbsenceController::class,


            ]);



        });
    });
