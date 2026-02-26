<?php

namespace App\Http\Controllers\AgendaScolaire;

use App\Http\Requests\StoreGrade;
use App\Models\AgendaScolaire\Grade;
use App\Models\AgendaScolaire\Publication;
use App\Services\OpenAiTranslationService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class GradeController extends Controller
{
    public function __construct(private OpenAiTranslationService $translator)
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
        $this->authorize('viewAny', Grade::class);
        $search = trim((string) request('q'));
        $data['Grade']  = Grade::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($textQuery) use ($search) {
                    $textQuery->where('name_grades->ar', 'like', '%' . $search . '%')
                        ->orWhere('name_grades->fr', 'like', '%' . $search . '%')
                        ->orWhere('name_grades->en', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();
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
    $this->authorize('create', Grade::class);
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
        $this->forgetPublicPublicationReferenceCache();

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
    $grade = Grade::findOrFail($id);
    $this->authorize('update', $grade);
    $validated = $request->validated();

    try {
        $translations = $this->translator->translateToFrenchAndEnglish($request->name_gradesar);

        Grade::where('id', $id)->update([
            'name_grades->ar' => $request->name_gradesar,
            'name_grades->fr' => $translations['fr'] ?? '',
            'name_grades->en' => $translations['en'] ?? '',
        ]);
        $this->forgetPublicPublicationReferenceCache();

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
    public function destroy($id)
    {
        $grade = Grade::findOrFail($id);
        $this->authorize('delete', $grade);

        $MyGrade_id = Publication::where('grade_id',$id)->pluck('grade_id');
        
        if($MyGrade_id->count() == 0){

        $grade->delete();
        $this->forgetPublicPublicationReferenceCache();
        toastr()->error(trans('messages.delete'));
        return redirect()->route('Grades.index');
    }

    else{

        toastr()->error(trans('messages.cantdelete'));
        return redirect()->route('Grades.index');

    }
    }

    private function forgetPublicPublicationReferenceCache(): void
    {
        Cache::forget('public:publication:grades');
        Cache::forget('public:publication:agendas');
    }
}
