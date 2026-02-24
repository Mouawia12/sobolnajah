<?php

namespace App\Http\Controllers\School;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\School\School;
use App\Models\School\Schoolgrade;
use App\Models\School\Classroom;
use App\Http\Requests\StoreSchoolgrade;
use App\Http\Requests\UpdateScoolgrade;
use Illuminate\Support\Facades\Auth;







class SchoolgradeController extends Controller
{




   

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['School'] = School::all();
        $data['Schoolgrade'] = Schoolgrade::all();
        $data['notify'] = $this->notifications();

        return view('admin.schoolgrades',$data);
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
    public function store(StoreSchoolgrade $request)
    {
       
        $validated = $request->validated();

            try {
                
                $Schoolgrade = new Schoolgrade();     
                $Schoolgrade->school_id = $request->school_id;
                $Schoolgrade->name_grade = ['fr' => $request->name_gradefr, 
                                            'ar' => $request->name_gradear];
                $Schoolgrade-> notes = ['fr' => $request->notesfr, 
                                        'ar' => $request->notesar];
                $Schoolgrade->save();
                toastr()->success(trans('messages.success'));
                return redirect()->route('Schoolgrades.index');
            }
            catch
            (\Exception $e) {
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
        return $id;
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
    public function update(UpdateScoolgrade $request, $id)
    {
        $validated = $request->validated();

            try {
                Schoolgrade::where('id', $id)->
                             update(['name_grade->fr'=> $request->name_gradefr,
                                     'name_grade->ar'=> $request->name_gradear,
                                     'notes->fr'=> $request->notesfr,
                                     'notes->ar'=> $request->notesar,
                                    ]);

                toastr()->success(trans('messages.Update'));
                return redirect()->route('Schoolgrades.index');
            }
            catch
            (\Exception $e) {
                return redirect()->back()->withErrors(['error' => $e->getMessage()]);
            }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {

       $MyClassroom_id = Classroom::where('grade_id',$id)->pluck('grade_id');
       if($MyClassroom_id->count() == 0){

          Schoolgrade::where('id',$id)->delete();
          toastr()->error(trans('messages.delete'));
          return redirect()->route('Schoolgrades.index');
      }

      else{
        toastr()->error(trans('messages.cantdelete'));
        return redirect()->route('Schoolgrades.index');

      }
    }



    public function getGrade($id){
        $list_grade = Schoolgrade::where("school_id", $id)->pluck("name_grade", "id");

        return $list_grade;
    }

    

}
