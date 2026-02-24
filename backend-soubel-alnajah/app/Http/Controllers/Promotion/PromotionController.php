<?php

namespace App\Http\Controllers\Promotion;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Models\Inscription\StudentInfo;
use App\Models\Promotion\Promotion;

use Illuminate\Support\Facades\DB;




use App\Models\School\School;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['promotions'] =  Promotion::all();
        $data['StudentInfo'] = StudentInfo::all();
        $data['notify'] = $this->notifications();

        return view('admin.promotion',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $students = StudentInfo::where('section_id',$request->section_id)->get();

            if($students->count() < 1 || $request->section_id == $request->section_id_new){
                return redirect()->back()->withErrors(['error' => $e->getMessage()]);
            }

            // update in table student
            foreach ($students as $student){

                $ids = explode(',',$student->id);
                StudentInfo::whereIn('id', $ids)
                    ->update([
                        'section_id'=>$request->section_id_new,
                    ]);

                // insert in to promotions
                Promotion::updateOrCreate([
                    'student_id'=>$student->id,
                    'from_school'=>$request->school_id,
                    'from_grade'=>$request->grade_id,
                    'from_Classroom'=>$request->classroom_id,
                    'from_section'=>$request->section_id,
                    'to_school'=>$request->school_id_new,
                    'to_grade'=>$request->grade_id_new,
                    'to_Classroom'=>$request->classroom_id_new,
                    'to_section'=>$request->section_id_new,
                ]);

            }
            DB::commit();
            toastr()->success(trans('messages.success'));
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function show(Promotion $promotion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function edit(Promotion $promotion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Promotion $promotion)
    {
        //
    }

    
     
    public function destroy(Request $request)
    {
        DB::beginTransaction();

        try {

            // التراجع عن الكل
            if($request->page_id ==1){

             $Promotions = Promotion::all();
             foreach ($Promotions as $Promotion){

                 //التحديث في جدول الطلاب
                 $ids = explode(',',$Promotion->student_id);
                 StudentInfo::whereIn('id', $ids)
                 ->update([
                 'section_id'=> $Promotion->from_section,
               ]);   
             }
                //حذف جدول الترقيات
                Promotion::truncate();

                DB::commit();
                toastr()->error(trans('messages.Delete'));
                return redirect()->back();

            }

            else{

                $Promotion = Promotion::findorfail($request->id);
                StudentInfo::where('id', $Promotion->student_id)
                    ->update([
                        'section_id'=> $Promotion->from_section,
                    ]);


                Promotion::destroy($request->id);
                DB::commit();
                toastr()->error(trans('messages.Delete'));
                return redirect()->back();

            }

        }

        catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
