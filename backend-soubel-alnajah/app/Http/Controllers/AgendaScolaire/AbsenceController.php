<?php

namespace App\Http\Controllers\AgendaScolaire;
use App\Http\Controllers\Controller;

use App\Models\School\School;
use App\Models\Inscription\StudentInfo;

use App\Models\AgendaScolaire\Absence;
use Illuminate\Http\Request;


class AbsenceController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['Absence'] = Absence::orderBy('date', 'desc')->get(); // ترتيب بالتاريخ تنازلي
        $data['notify'] = $this->notifications();

        return view('admin.AbsenceStudent',$data);
     
    }




    public function storeOrUpdate(Request $request)
{
    $request->validate([
        'student_id' => 'required|integer',
        'hour' => 'required|string',
        'status' => 'required|boolean',
    ]);

    $absence = Absence::firstOrCreate(
        ['student_id' => $request->student_id, 'date' => now()->toDateString()],
        ['hour_1' => true, 'hour_2' => true, 'hour_3' => true, 'hour_4' => true,
         'hour_5' => true, 'hour_6' => true, 'hour_7' => true, 'hour_8' => true, 
         'hour_9' => true]
    );

    // تحديث العمود (مثلاً hour_1 أو hour_5)
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
