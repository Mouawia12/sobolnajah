<?php

namespace App\Providers;

use App\Models\AgendaScolaire\Exames;
use App\Models\AgendaScolaire\NoteStudent;
use App\Models\AgendaScolaire\Publication;
use App\Models\AgendaScolaire\Absence;
use App\Models\AgendaScolaire\Agenda;
use App\Models\AgendaScolaire\Grade;
use App\Models\Inscription\StudentInfo;
use App\Models\Inscription\Teacher;
use App\Models\Inscription\Inscription;
use App\Models\Promotion\Promotion;
use App\Models\School\Schoolgrade;
use App\Models\School\School;
use App\Models\School\Section;
use App\Models\School\Classroom;
use App\Models\Recruitment\JobPost;
use App\Models\Recruitment\JobApplication;
use App\Models\Timetable\Timetable;
use App\Models\TeacherSchedule\TeacherSchedule;
use App\Models\Chat\ChatRoom;
use App\Models\Accounting\StudentContract;
use App\Models\Accounting\Payment;
use App\Policies\ExamePolicy;
use App\Policies\AbsencePolicy;
use App\Policies\AgendaPolicy;
use App\Policies\InscriptionPolicy;
use App\Policies\GradePolicy;
use App\Policies\NoteStudentPolicy;
use App\Policies\PublicationPolicy;
use App\Policies\PromotionPolicy;
use App\Policies\SchoolgradePolicy;
use App\Policies\SchoolPolicy;
use App\Policies\SectionPolicy;
use App\Policies\ClassroomPolicy;
use App\Policies\StudentInfoPolicy;
use App\Policies\TeacherPolicy;
use App\Policies\JobPostPolicy;
use App\Policies\JobApplicationPolicy;
use App\Policies\TimetablePolicy;
use App\Policies\TeacherSchedulePolicy;
use App\Policies\ChatRoomPolicy;
use App\Policies\StudentContractPolicy;
use App\Policies\PaymentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Exames::class => ExamePolicy::class,
        Absence::class => AbsencePolicy::class,
        Agenda::class => AgendaPolicy::class,
        Grade::class => GradePolicy::class,
        NoteStudent::class => NoteStudentPolicy::class,
        Publication::class => PublicationPolicy::class,
        StudentInfo::class => StudentInfoPolicy::class,
        Teacher::class => TeacherPolicy::class,
        Inscription::class => InscriptionPolicy::class,
        Promotion::class => PromotionPolicy::class,
        Schoolgrade::class => SchoolgradePolicy::class,
        School::class => SchoolPolicy::class,
        Section::class => SectionPolicy::class,
        Classroom::class => ClassroomPolicy::class,
        JobPost::class => JobPostPolicy::class,
        JobApplication::class => JobApplicationPolicy::class,
        Timetable::class => TimetablePolicy::class,
        TeacherSchedule::class => TeacherSchedulePolicy::class,
        ChatRoom::class => ChatRoomPolicy::class,
        StudentContract::class => StudentContractPolicy::class,
        Payment::class => PaymentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
