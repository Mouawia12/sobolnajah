<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\AgendaScolaire\Publication;
use App\Models\Inscription\Inscription;
use App\Models\Inscription\StudentInfo;
use App\Models\Inscription\Teacher;
use App\Models\Inscription\MyParent;
use App\Models\Chat\ChatMessage;
use App\Models\Timetable\TimetableEntry;
use App\Models\TeacherSchedule\TeacherSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{


    


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(Auth::user()->hasRole('admin')){
            $schoolId = $this->currentSchoolId();
            $locale = app()->getLocale();

            $notifications = $this->notifications();
            $data['notify'] = $notifications;
            $studentsAggregate = StudentInfo::query()
                ->forSchool($schoolId)
                ->selectRaw('COUNT(*) as total_students')
                ->selectRaw('SUM(CASE WHEN gender = 1 THEN 1 ELSE 0 END) as male_students')
                ->selectRaw('SUM(CASE WHEN gender = 0 THEN 1 ELSE 0 END) as female_students')
                ->first();

            $totalStudents = (int) ($studentsAggregate?->total_students ?? 0);
            $numbermen = (int) ($studentsAggregate?->male_students ?? 0);
            $numberwommen = (int) ($studentsAggregate?->female_students ?? 0);

            $notifyUnreadCount = $notifications->whereNull('read_at')->count();
            $data['notifyunread'] = $notifyUnreadCount;
            $data['notifyread'] = $notifications->count() - $notifyUnreadCount;
            $data['numbermen'] = $numbermen;
            $data['numberwommen'] = $numberwommen;

            $totalGuardians = MyParent::query()->forSchool($schoolId)->count();
            $pendingInscriptions = Inscription::query()
                ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
                ->where(function ($query) {
                    $query->whereNull('statu')->orWhere('statu', '!=', 'accept');
                })
                ->count();

            $schoolCacheKey = $schoolId ?: 'all';
            $studentsByGrade = Cache::remember(
                "home:school:{$schoolCacheKey}:locale:{$locale}:students-by-grade",
                now()->addMinutes(5),
                function () use ($schoolId, $locale) {
                    return StudentInfo::query()
                        ->forSchool($schoolId)
                        ->selectRaw('sections.grade_id as grade_id, schoolgrades.name_grade as grade_name, COUNT(*) as total')
                        ->join('sections', 'studentinfos.section_id', '=', 'sections.id')
                        ->join('schoolgrades', 'sections.grade_id', '=', 'schoolgrades.id')
                        ->groupBy('sections.grade_id', 'schoolgrades.name_grade')
                        ->orderByDesc('total')
                        ->get()
                        ->map(function ($row) use ($locale) {
                            $name = $row->grade_name;
                            $decoded = json_decode($name, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $name = $decoded[$locale] ?? reset($decoded) ?? $name;
                            }

                            return [
                                'label' => $name,
                                'value' => (int) $row->total,
                            ];
                        })
                        ->values();
                }
            );

            $studentsMonthly = Cache::remember(
                "home:school:{$schoolCacheKey}:locale:{$locale}:students-monthly",
                now()->addMinutes(5),
                function () use ($schoolId, $locale) {
                    return StudentInfo::query()
                        ->forSchool($schoolId)
                        ->whereNotNull('created_at')
                        ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, COUNT(*) as total')
                        ->groupBy('ym')
                        ->orderByDesc('ym')
                        ->take(12)
                        ->get()
                        ->reverse()
                        ->map(function ($row) use ($locale) {
                            $date = Carbon::createFromFormat('Y-m', $row->ym)->locale($locale);
                            return [
                                'label' => $date->translatedFormat('M Y'),
                                'value' => (int) $row->total,
                            ];
                        })
                        ->values();
                }
            );

            $recentStudents = StudentInfo::query()
                ->forSchool($schoolId)
                ->select(['id', 'user_id', 'section_id', 'prenom', 'nom', 'created_at'])
                ->with([
                    'user:id,name',
                    'section:id,classroom_id,name_section',
                    'section.classroom:id,grade_id,name_class',
                    'section.classroom.schoolgrade:id,name_grade',
                ])
                ->latest()
                ->take(5)
                ->get();

            $todayMessageCount = ChatMessage::query()
                ->selectRaw('COUNT(chat_messages.id) as aggregate')
                ->join('users', 'users.id', '=', 'chat_messages.user_id')
                ->when($schoolId, fn ($query) => $query->where('users.school_id', $schoolId))
                ->whereDate('chat_messages.created_at', Carbon::today())
                ->value('aggregate');
            $todayMessageCount = (int) ($todayMessageCount ?? 0);

            $data['stats'] = [
                'total_students' => $totalStudents,
                'male_students' => $numbermen,
                'female_students' => $numberwommen,
                'total_guardians' => $totalGuardians,
                'pending_inscriptions' => $pendingInscriptions,
                'messages_today' => $todayMessageCount,
            ];

            $data['studentsByGrade'] = $studentsByGrade;
            $data['studentsMonthly'] = $studentsMonthly;
            $data['recentStudents'] = $recentStudents;

            return view('admin.home', $data);
        } else if (Auth::user()->hasRole('teacher')) {
            return redirect()->route('teacher.dashboard');
        } else if (Auth::user()->hasRole('accountant')) {
            return redirect()->route('accountant.dashboard');
        } else if(Auth::user()->hasRole('student')){

            $data['StudentInfo'] = StudentInfo::where('user_id',Auth::user()->id)->first();
            return view('front-end.studentprofile',$data);
            
        }else if(Auth::user()->hasRole('guardian')){
            $data['ParentInfo'] = MyParent::where('user_id',Auth::user()->id)->first();
            return view('front-end.parentprofile',$data);
            //return Auth::user()->id;
        }

    }

    public function teacherDashboard()
    {
        abort_unless(Auth::check() && Auth::user()->hasRole('teacher'), 403);

        $user = Auth::user();
        $locale = app()->getLocale();
        $teacher = Teacher::query()
            ->with([
                'specialization:id,name',
                'sections:id,classroom_id,name_section',
                'sections.classroom:id,grade_id,name_class',
                'sections.classroom.schoolgrade:id,name_grade',
            ])
            ->where('user_id', $user->id)
            ->first();

        $teacherId = $teacher?->id;

        $assignedSections = $teacher?->sections ?? collect();
        $sectionsCount = $assignedSections->count();
        $totalPeriods = $teacherId
            ? TimetableEntry::query()->where('teacher_id', $teacherId)->count()
            : 0;
        $todayPeriods = $teacherId
            ? TimetableEntry::query()
                ->where('teacher_id', $teacherId)
                ->where('day_of_week', Carbon::now()->dayOfWeekIso)
                ->count()
            : 0;
        $teacherSchedulesCount = $teacherId
            ? TeacherSchedule::query()->where('teacher_id', $teacherId)->count()
            : 0;

        $sectionCards = $assignedSections->map(function ($section) use ($locale) {
            $classroom = $section->classroom;
            $grade = $classroom?->schoolgrade;

            return [
                'section' => $section->getTranslation('name_section', $locale),
                'classroom' => $classroom ? $classroom->getTranslation('name_class', $locale) : '—',
                'grade' => $grade ? $grade->getTranslation('name_grade', $locale) : '—',
            ];
        })->values();

        return view('teacher.dashboard', [
            'notify' => $this->notifications(),
            'teacher' => $teacher,
            'stats' => [
                'sections_count' => $sectionsCount,
                'today_periods' => $todayPeriods,
                'total_periods' => $totalPeriods,
                'teacher_schedules_count' => $teacherSchedulesCount,
            ],
            'sections' => $sectionCards,
            'breadcrumbs' => [
                ['label' => __('لوحة المعلم')],
            ],
        ]);
    }

}
