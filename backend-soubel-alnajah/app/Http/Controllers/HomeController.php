<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AgendaScolaire\Publication;
use App\Models\Inscription\Inscription;
use App\Models\Inscription\StudentInfo;
use App\Models\Inscription\MyParent;
use App\Models\Chat\ChatMessage;
use Carbon\Carbon;
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
            $numbermen = StudentInfo::query()->forSchool($schoolId)->where('gender', 1)->count();
            $numberwommen = StudentInfo::query()->forSchool($schoolId)->where('gender', 0)->count();
            $notifyUnreadCount = $notifications->whereNull('read_at')->count();
            $data['notifyunread'] = $notifyUnreadCount;
            $data['notifyread'] = $notifications->count() - $notifyUnreadCount;
            $data['numbermen'] = $numbermen;
            $data['numberwommen'] = $numberwommen;

            $totalStudents = StudentInfo::query()->forSchool($schoolId)->count();
            $totalGuardians = MyParent::query()->forSchool($schoolId)->count();
            $pendingInscriptions = Inscription::query()
                ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
                ->where(function ($query) {
                    $query->whereNull('statu')->orWhere('statu', '!=', 'accept');
                })
                ->count();

            $studentsByGrade = StudentInfo::query()
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
                });

            $studentsMonthly = StudentInfo::query()
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
                });

            $recentStudents = StudentInfo::query()
                ->forSchool($schoolId)
                ->with(['user:id,name', 'section.classroom.schoolgrade'])
                ->latest()
                ->take(5)
                ->get();

            $todayMessageCount = ChatMessage::query()
                ->whereDate('created_at', Carbon::today())
                ->count();

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
        }else if(Auth::user()->hasRole('student')){

            $data['StudentInfo'] = StudentInfo::where('user_id',Auth::user()->id)->first();
            return view('front-end.studentprofile',$data);
            
        }else if(Auth::user()->hasRole('guardian')){
            $data['ParentInfo'] = MyParent::where('user_id',Auth::user()->id)->first();
            return view('front-end.parentprofile',$data);
            //return Auth::user()->id;
        }
        
    }

}
