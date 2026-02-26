<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\School;
use App\Models\School\Schoolgrade;
use App\Models\School\Classroom;
use App\Http\Requests\StoreSchoolgrade;
use App\Http\Requests\UpdateScoolgrade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;







class SchoolgradeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin'])->except(['getGrade', 'listBySchool']);
    }




   

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', Schoolgrade::class);
        $schoolId = $this->currentSchoolId();
        $query = trim((string) request('q'));
        $schoolFilter = request('school_id');
        if ($schoolId) {
            $schoolFilter = $schoolId;
        }

        $data['School'] = School::query()
            ->when($schoolId, fn ($query) => $query->whereKey($schoolId))
            ->orderBy('name_school')
            ->get();
        $data['Schoolgrade'] = Schoolgrade::query()
            ->forSchool($schoolId)
            ->with('school')
            ->when($schoolFilter, fn ($gradesQuery) => $gradesQuery->where('school_id', (int) $schoolFilter))
            ->when($query !== '', function ($gradesQuery) use ($query) {
                $gradesQuery->where(function ($textQuery) use ($query) {
                    $textQuery->where('name_grade->fr', 'like', '%' . $query . '%')
                        ->orWhere('name_grade->ar', 'like', '%' . $query . '%')
                        ->orWhere('name_grade->en', 'like', '%' . $query . '%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();
        $data['currentSchoolId'] = $schoolId;
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
        $this->authorize('create', Schoolgrade::class);
        $validated = $request->validated();
        $schoolId = $this->currentSchoolId();

        if ($schoolId && (int) $request->school_id !== $schoolId) {
            abort(403);
        }

            try {
                
                $Schoolgrade = new Schoolgrade();     
                $Schoolgrade->school_id = $request->school_id;
                $Schoolgrade->name_grade = ['fr' => $request->name_gradefr, 
                                            'ar' => $request->name_gradear];
                $Schoolgrade-> notes = ['fr' => $request->notesfr, 
                                        'ar' => $request->notesar];
                $Schoolgrade->save();
                $this->forgetSchoolGradesLookupCache((int) $Schoolgrade->school_id);
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
        $schoolgrade = Schoolgrade::query()
            ->forSchool($this->currentSchoolId())
            ->findOrFail($id);
        $this->authorize('update', $schoolgrade);

            try {
            $schoolgrade->update([
                'name_grade->fr'=> $request->name_gradefr,
                'name_grade->ar'=> $request->name_gradear,
                'notes->fr'=> $request->notesfr,
                'notes->ar'=> $request->notesar,
            ]);
            $this->forgetSchoolGradesLookupCache((int) $schoolgrade->school_id);

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
    public function destroy($id)
    {
       $schoolgrade = Schoolgrade::query()
           ->forSchool($this->currentSchoolId())
           ->findOrFail($id);
       $this->authorize('delete', $schoolgrade);

       $MyClassroom_id = Classroom::query()
           ->forSchool($this->currentSchoolId())
           ->where('grade_id', $id)
           ->pluck('grade_id');
      if($MyClassroom_id->count() == 0){
          $this->forgetSchoolGradesLookupCache((int) $schoolgrade->school_id);
          $schoolgrade->delete();
          toastr()->error(trans('messages.delete'));
          return redirect()->route('Schoolgrades.index');
      }

      else{
        toastr()->error(trans('messages.cantdelete'));
        return redirect()->route('Schoolgrades.index');

      }
    }



    public function listBySchool($id)
    {
        if (Auth::check() && Auth::user()->school_id && (int) Auth::user()->school_id !== (int) $id) {
            return collect();
        }

        $list_grade = Schoolgrade::query()
            ->where("school_id", $id);

        $cacheKey = sprintf('lookup:school:%d:grades', (int) $id);

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($list_grade) {
            return $list_grade->pluck("name_grade", "id");
        });
    }

    private function forgetSchoolGradesLookupCache(int $schoolId): void
    {
        Cache::forget(sprintf('lookup:school:%d:grades', $schoolId));
        Cache::forget(sprintf('exam:school:%d:grades', $schoolId));
    }

    // Backward-compatible alias for legacy naming.
    public function getGrade($id){
        return $this->listBySchool($id);
    }

    

}
