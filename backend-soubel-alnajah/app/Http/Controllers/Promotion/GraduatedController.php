<?php

namespace App\Http\Controllers\Promotion;
use App\Http\Controllers\Controller;

use App\Models\School\School;
use App\Models\Inscription\StudentInfo;
use App\Models\Promotion\Promotion;
use App\Models\User;

use Illuminate\Http\Request;

class GraduatedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['School'] = School::all();
        $data['StudentInfo'] = StudentInfo::onlyTrashed()->get();
        $data['notify'] = $this->notifications();


        return view('admin.graduated',$data);
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
        try {

            $students = StudentInfo::where('section_id',$request->section_id)->get();

            if($students->count() < 1){
                return redirect()->back()->withErrors(['error' => $e->getMessage()]);
            }

            // update in table student
            foreach ($students as $student){

                $ids = explode(',',$student->id);
                StudentInfo::whereIn('id', $ids)->Delete();
                Promotion::whereIn('id',$ids)->delete();
            } 

            toastr()->success(trans('messages.success'));
            return redirect()->route('graduated.index');

        } catch (\Exception $e) {
            
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        StudentInfo::onlyTrashed()->where('id', $id)->first()->restore();
        toastr()->success(trans('messages.success'));
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if($request->delete_id == 1){
          
            $StudentInfo = StudentInfo::onlyTrashed()->where('id',$request->student_id)->get();
            $user_id = $StudentInfo[0]->user_id;
            $parent_id = $user_id+1;

            $ids = array($user_id,$parent_id);
            User::whereIn('id',$ids)->delete();

            toastr()->error(trans('messages.delete'));
            return redirect()->route('graduated.index');

        }else {
            StudentInfo::where('id',$request->student_id)->delete();
            Promotion::where('id',$request->promotion_id)->delete();

            toastr()->error(trans('messages.delete'));
            return redirect()->route('Promotions.index');
        }
        
        
     
    }
}
