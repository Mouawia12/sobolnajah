<?php

namespace App\Http\Controllers\School;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\School\School;
use App\Models\School\Schoolgrade;
use App\Models\School\Classroom;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreSchool;


class SchoolController extends Controller
{
    


    public function index()
    {
        $data['notify'] = $this->notifications();
        $data['School'] = School::all();

        return view('admin.schools',$data);
    }
    

    /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
    public function store(StoreSchool $request)
    {
        $validated = $request->validated();

        try {
            $School = new School();
        
            $School->name_school = ['fr' => $request->name_schoolfr, 'ar' => $request->name_schoolar];
            $School->save();
            toastr()->success(trans('messages.success'));
            return redirect()->route('Schools.index');

        }
  
        catch (\Exception $e){
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }


   public function update(StoreSchool $request,$id)
   {
   
    $validated = $request->validated();

        try {

            School::where('id', $id)->
            update(['name_school->fr'=> $request->name_schoolfr,
                    'name_school->ar'=> $request->name_schoolar]);

            toastr()->success(trans('messages.Update'));
            return redirect()->route('Schools.index');
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

       $MySchoolgrade_id = Schoolgrade::where('school_id',$id)->pluck('school_id');
       if($MySchoolgrade_id->count() == 0){

          $School = School::where('id',$id)->delete();
          toastr()->error(trans('messages.delete'));
          return redirect()->route('Schools.index');
      }

      else{

        toastr()->error(trans('messages.cantdelete'));
        return redirect()->route('Schools.index');

      }
    }



    public function getclassrooms($schoolgrade_id)
    {
        $list_classrooms = Classroom::where("grade_id", $schoolgrade_id)->pluck("name_class","id_classroom");

        return $list_classrooms;
    }

  
    


    public function test()
    {
        $Schoolgrade = Schoolgrade::all();
        $School = School::all();
        
        return view('admin.test',compact('Schoolgrade','School'));
    }




















}
