<?php

namespace App\Http\Controllers\School;
use App\Http\Controllers\Controller;
use App\Models\School\School;
use App\Models\School\Schoolgrade;
use App\Models\School\Classroom;
use App\Http\Requests\StoreSchool;


class SchoolController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin'])->except(['getclassrooms']);
    }


    public function index()
    {
        $this->authorize('viewAny', School::class);
        $schoolId = $this->currentSchoolId();
        $query = trim((string) request('q'));

        $data['notify'] = $this->notifications();
        $data['School'] = School::query()
            ->forSchool($schoolId)
            ->when($query !== '', function ($schoolsQuery) use ($query) {
                $schoolsQuery->where(function ($textQuery) use ($query) {
                    $textQuery->where('name_school->fr', 'like', '%' . $query . '%')
                        ->orWhere('name_school->ar', 'like', '%' . $query . '%')
                        ->orWhere('name_school->en', 'like', '%' . $query . '%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.schools',$data);
    }
    

    /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
    public function store(StoreSchool $request)
    {
        $this->authorize('create', School::class);
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
    $school = School::query()
        ->forSchool($this->currentSchoolId())
        ->findOrFail($id);
    $this->authorize('update', $school);

    $validated = $request->validated();

        try {

            $school->update([
                'name_school->fr'=> $request->name_schoolfr,
                'name_school->ar'=> $request->name_schoolar,
            ]);

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
    public function destroy($id)
    {
       $school = School::query()
           ->forSchool($this->currentSchoolId())
           ->findOrFail($id);
       $this->authorize('delete', $school);

       $MySchoolgrade_id = Schoolgrade::query()
           ->where('school_id', $school->id)
           ->pluck('school_id');
       if($MySchoolgrade_id->count() == 0){

          $school->delete();
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
        $schoolId = $this->currentSchoolId();
        $Schoolgrade = Schoolgrade::query()->forSchool($schoolId)->get();
        $School = School::query()->forSchool($schoolId)->get();
        
        return view('admin.test',compact('Schoolgrade','School'));
    }




















}
