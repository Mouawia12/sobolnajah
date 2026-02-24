<?php

namespace App\Http\Controllers\AgendaScolaire;

use App\Http\Requests\StoreGrade;
use App\Models\AgendaScolaire\Grade;
use App\Models\AgendaScolaire\Publication;
use App\Services\OpenAiTranslationService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GradeController extends Controller
{
    public function __construct(private OpenAiTranslationService $translator)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['Grade']  = Grade::all();
        $data['notify'] = $this->notifications();
        return view('admin.grade',$data);
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
     * @param  \Illuminate\Http\StoreGrade  $request
     * @return \Illuminate\Http\Response
     */
public function store(StoreGrade $request)
{
    $validated = $request->validated();

    try {
        $translations = $this->translator->translateToFrenchAndEnglish($request->name_gradesar);

        $Grade = new Grade();
        $Grade->name_grades = [
            'ar' => $request->name_gradesar,
            'fr' => $translations['fr'] ?? '',
            'en' => $translations['en'] ?? '',
        ];
        $Grade->save();

        toastr()->success(trans('messages.success'));
        return redirect()->route('Grades.index');

    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['error' => $e->getMessage()]);
    }
}

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    public function show(Grade $grade)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    public function edit(Grade $grade)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\StoreGrade  $request
     * @param  \App\Models\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    
public function update(StoreGrade $request, $id)
{
    $validated = $request->validated();

    try {
        $translations = $this->translator->translateToFrenchAndEnglish($request->name_gradesar);

        Grade::where('id', $id)->update([
            'name_grades->ar' => $request->name_gradesar,
            'name_grades->fr' => $translations['fr'] ?? '',
            'name_grades->en' => $translations['en'] ?? '',
        ]);

        toastr()->success(trans('messages.Update'));
        return redirect()->route('Grades.index');

    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['error' => $e->getMessage()]);
    }
}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
        $MyGrade_id = Publication::where('grade_id',$id)->pluck('grade_id');
        
        if($MyGrade_id->count() == 0){

        Grade::where('id',$id)->delete();
        toastr()->error(trans('messages.delete'));
        return redirect()->route('Grades.index');
    }

    else{

        toastr()->error(trans('messages.cantdelete'));
        return redirect()->route('Grades.index');

    }
    }
}
