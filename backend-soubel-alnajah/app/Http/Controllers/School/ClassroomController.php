<?php

namespace App\Http\Controllers\School;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\School\School;
use App\Models\School\Schoolgrade;
use App\Models\School\Classroom;
use App\Models\Inscription\Inscription;
use App\Http\Requests\StoreClassroom;
use Illuminate\Support\Facades\DB;
use Throwable;







class ClassroomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $schoolId = $this->currentSchoolId();

        $data['School'] = School::query()
            ->when($schoolId, fn ($query) => $query->whereKey($schoolId))
            ->with('schoolgrades')
            ->orderBy('name_school')
            ->get();

        $data['Schoolgradee'] = Schoolgrade::query()
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->orderBy('name_grade')
            ->get();

        $data['Classroom'] = Classroom::query()
            ->forSchool($schoolId)
            ->with('schoolgrade')
            ->orderByDesc('created_at')
            ->get();

        $data['notify'] = $this->notifications();

        return view('admin.classes', $data);
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
    public function store(StoreClassroom $request)
    {
        $request->validated();

        $schoolId = $this->currentSchoolId();

        if ($schoolId && (int) $request->school_id !== $schoolId) {
            return back()->withErrors(['school_id' => trans('messages.error')]);
        }

        try {
            DB::transaction(function () use ($request) {
                Classroom::create([
                    'school_id' => $request->school_id,
                    'grade_id' => $request->grade_id,
                    'name_class' => [
                        'fr' => $request->name_classfr,
                        'ar' => $request->name_classar,
                        'en' => $request->name_classfr,
                    ],
                ]);
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        toastr()->success(trans('messages.success'));

        return redirect()->route('Classes.index');
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
    public function update(StoreClassroom $request, $id)
    {
        $request->validated();

        $schoolId = $this->currentSchoolId();

        $classroom = Classroom::query()
            ->forSchool($schoolId)
            ->findOrFail($id);

        if ($schoolId && (int) $request->school_id !== $classroom->school_id) {
            return back()->withErrors(['school_id' => trans('messages.error')]);
        }

        try {
            DB::transaction(function () use ($classroom, $request) {
                $classroom->update([
                    'name_class' => [
                        'fr' => $request->name_classfr,
                        'ar' => $request->name_classar,
                        'en' => $request->name_classfr,
                    ],
                    'grade_id' => $request->grade_id,
                ]);
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        toastr()->success(trans('messages.Update'));

        return redirect()->route('Classes.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $classroom = Classroom::query()
            ->forSchool($this->currentSchoolId())
            ->findOrFail($id);

        $hasInscriptions = Inscription::where('classroom_id', $classroom->id)->exists();

        if ($hasInscriptions) {
            toastr()->error(trans('messages.cantdelete'));
            return redirect()->route('Classes.index');
        }

        $classroom->delete();

        toastr()->error(trans('messages.delete'));

        return redirect()->route('Classes.index');
    }


    
    public function getClasse($id){
        $list_class = Classroom::query()
            ->forSchool($this->currentSchoolId())
            ->where("grade_id", $id)
            ->pluck("name_class", "id");

        return $list_class;
    }


    public function delete_all(Request $request)
    {
        $delete_all_id = explode(",", $request->delete_all_id);

        Inscription::whereIn('id', $delete_all_id)->Delete();
        toastr()->error(trans('messages.delete'));
        return redirect()->route('Inscriptions.index');
    }
    
   

}
