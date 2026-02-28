<?php

namespace App\Http\Controllers\AgendaScolaire;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsenceStatusRequest;

use App\Models\School\School;
use App\Models\School\Section;
use App\Models\Inscription\StudentInfo;

use App\Models\AgendaScolaire\Absence;
use Illuminate\Http\Request;


class AbsenceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', Absence::class);
        $schoolId = $this->currentSchoolId();
        $search = trim((string) request('q'));
        $from = request('date_from');
        $to = request('date_to');
        $sectionId = request('section_id');

        $data['Absence'] = Absence::query()
            ->select([
                'id',
                'student_id',
                'date',
                'hour_1',
                'hour_2',
                'hour_3',
                'hour_4',
                'hour_5',
                'hour_6',
                'hour_7',
                'hour_8',
                'hour_9',
            ])
            ->whereHas('student.section', function ($query) use ($schoolId) {
                if ($schoolId) {
                    $query->where('school_id', $schoolId);
                }
            })
            ->when($sectionId, function ($query) use ($sectionId) {
                $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('section_id', $sectionId));
            })
            ->when($from, fn ($query) => $query->whereDate('date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('date', '<=', $to))
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery->where('prenom', 'like', '%' . $search . '%')
                        ->orWhere('nom', 'like', '%' . $search . '%')
                        ->orWhere('numtelephone', 'like', '%' . $search . '%')
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', '%' . $search . '%'));
                });
            })
            ->with([
                'student:id,section_id,prenom,nom,numtelephone',
                'student.section:id,name_section',
            ])
            ->orderBy('date', 'desc')
            ->paginate(20)
            ->withQueryString();
        $data['Sections'] = Section::query()
            ->forSchool($schoolId)
            ->select(['id', 'classroom_id', 'name_section'])
            ->with([
                'classroom:id,grade_id,name_class',
                'classroom.schoolgrade:id,name_grade',
            ])
            ->orderBy('id')
            ->get();
        $data['notify'] = $this->notifications();
        $data['breadcrumbs'] = [
            ['label' => 'لوحة التحكم', 'url' => url('/admin')],
            ['label' => trans('student.absence')],
        ];

        return view('admin.AbsenceStudent',$data);
     
    }




public function storeOrUpdate(StoreAbsenceStatusRequest $request)
{
    $this->authorize('create', Absence::class);
    $schoolId = $this->currentSchoolId();

    $student = StudentInfo::query()
        ->forSchool($schoolId)
        ->findOrFail((int) $request->student_id);

    $absence = Absence::firstOrCreate(
        ['student_id' => $student->id, 'date' => now()->toDateString()],
        ['hour_1' => true, 'hour_2' => true, 'hour_3' => true, 'hour_4' => true,
         'hour_5' => true, 'hour_6' => true, 'hour_7' => true, 'hour_8' => true, 
         'hour_9' => true]
    );

    $this->authorize('update', $absence);

    // تحديث عمود مسموح فقط عبر whitelist من FormRequest.
    $absence->{$request->hour} = $request->status;
    $absence->save();

    return response()->json(['success' => true, 'absence' => $absence]);
}


    public function getToday(Request $request)
        {
            $studentId = $request->query('student_id');

            if (!$studentId) {
                return response()->json([], 200);
            }

            $schoolId = $this->currentSchoolId();
            StudentInfo::query()
                ->forSchool($schoolId)
                ->findOrFail((int) $studentId);

            $absence = Absence::where('student_id', $studentId)
                ->where('date', date('Y-m-d'))
                ->first();

            if (!$absence) {
                // لا توجد بيانات لليوم — رجع مصفوفة فارغة حتى يعرف الـ JS أنه لا يوجد سجل
                return response()->json([]);
            }

            // طوّع الاستجابة لتكون خريطة hour_1..hour_10 => boolean
            $data = [];
            for ($i = 1; $i <= 10; $i++) {
                $key = "hour_{$i}";
                $data[$key] = (bool) ($absence->{$key} ?? false);
            }

            return response()->json($data);
        }

}
