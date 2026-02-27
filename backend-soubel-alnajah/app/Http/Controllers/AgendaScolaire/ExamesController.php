<?php

namespace App\Http\Controllers\AgendaScolaire;

use Illuminate\Support\Facades\Auth;
use App\Models\AgendaScolaire\Exames;
use App\Http\Requests\DestroyExameRequest;
use App\Http\Requests\StoreExameRequest;
use App\Http\Requests\UpdateExameRequest;
use App\Models\School\Classroom;
use App\Models\School\Schoolgrade;
use App\Http\Controllers\Controller;
use App\Models\Specialization\Specialization;
use App\Services\OpenAiTranslationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExamesController extends Controller
{
    public function __construct(private OpenAiTranslationService $translator)
    {
        $this->middleware(['auth', 'role:admin'])->only(['create', 'edit', 'store', 'update', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $schoolId = $this->currentSchoolId();
        $search = trim((string) request('q'));
        $gradeId = request('grade_id');
        $classroomId = request('classroom_id');
        $specializationId = request('specialization_id');
        $year = request('Annscolaire');

        $query = Exames::query()
            ->select([
                'id',
                'name',
                'file',
                'specialization_id',
                'grade_id',
                'classroom_id',
                'Annscolaire',
                'created_at',
            ])
            ->with([
                'classroom:id,name_class',
                'schoolgrade:id,name_grade',
                'specialization:id,name',
            ])
            ->when(Auth::check() && Auth::user()->hasRole('admin') && $schoolId, function ($query) use ($schoolId) {
                $query->whereHas('classroom', fn ($classroomQuery) => $classroomQuery->where('school_id', $schoolId));
            })
            ->when($gradeId, fn ($q) => $q->where('grade_id', (int) $gradeId))
            ->when($classroomId, fn ($q) => $q->where('classroom_id', (int) $classroomId))
            ->when($specializationId, fn ($q) => $q->where('specialization_id', (int) $specializationId))
            ->when($year, fn ($q) => $q->where('Annscolaire', $year))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($textQuery) use ($search) {
                    $textQuery->where('name->ar', 'like', '%' . $search . '%')
                        ->orWhere('name->fr', 'like', '%' . $search . '%')
                        ->orWhere('name->en', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('created_at');
        
        if(Auth::user()){
            if(Auth::user()->hasRole('admin')){
                $data['notify'] = $this->notifications();
                $data['Exames'] = $query->paginate(20)->withQueryString();
                $cacheSchoolId = (int) ($schoolId ?? 0);
                $data['Schoolgrade'] = Cache::remember(
                    sprintf('exam:school:%d:grades', $cacheSchoolId),
                    now()->addMinutes(15),
                    fn () => Schoolgrade::query()
                        ->forSchool($schoolId)
                        ->select(['id', 'name_grade'])
                        ->orderBy('name_grade')
                        ->get()
                );
                $data['Classrooms'] = Cache::remember(
                    sprintf('exam:school:%d:classrooms', $cacheSchoolId),
                    now()->addMinutes(15),
                    fn () => Classroom::query()
                        ->forSchool($schoolId)
                        ->select(['id', 'grade_id', 'name_class'])
                        ->orderBy('name_class')
                        ->get()
                );
                $data['Specializations'] = Cache::remember(
                    'exam:lookups:specializations',
                    now()->addMinutes(15),
                    fn () => Specialization::query()
                        ->select(['id', 'name'])
                        ->orderBy('name')
                        ->get()
                );
                return view('admin.exam',$data);
            } else {
                $data['Exames'] = $query->paginate(20)->withQueryString();
                return view('front-end.exam',$data);
            }
        } else {
            $data['Exames'] = $query->paginate(20)->withQueryString();
            return view('front-end.exam',$data);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExameRequest $request)
    {
        try {
            $this->authorize('create', Exames::class);
            $schoolId = $this->currentSchoolId();
            $classroom = Classroom::query()
                ->forSchool($schoolId)
                ->findOrFail((int) $request->classroom_id);

            $translations = $this->translator->translateToFrenchAndEnglish($request->name_ar);
            $file = $request->file('file_url');
            $filePath = 'private/exames/' . now()->format('Y/m') . '/' . time() . '_' . Str::random(16) . '.' . $file->getClientOriginalExtension();
            Storage::disk('local')->put($filePath, file_get_contents($file->getRealPath()));

            Exames::create([
                'file' => $filePath,
                'name' => [
                    'ar' => $request->name_ar,
                    'fr' => $translations['fr'] ?? '',
                    'en' => $translations['en'] ?? '',
                ],
                'specialization_id' => $request->specialization_id,
                'grade_id' => $classroom->grade_id,
                'classroom_id' => $classroom->id,
                'Annscolaire' => $request->Annscolaire,
            ]);

            toastr()->success(trans('messages.success'));
            return redirect()->route('Exames.index');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExameRequest $request, $id)
    {
        try {
            $exam = Exames::findOrFail($id);
            $this->authorize('update', $exam);
            $schoolId = $this->currentSchoolId();
            $classroom = Classroom::query()
                ->forSchool($schoolId)
                ->findOrFail((int) $request->classroom_id);

            $translations = $this->translator->translateToFrenchAndEnglish($request->name_ar);

            if ($request->hasFile('file_url')) {
                if ($exam->file && Storage::disk('local')->exists($exam->file)) {
                    Storage::disk('local')->delete($exam->file);
                }

                if ($exam->file && file_exists(public_path('exames/' . $exam->file))) {
                    @unlink(public_path('exames/' . $exam->file));
                }

                $file = $request->file('file_url');
                $filePath = 'private/exames/' . now()->format('Y/m') . '/' . time() . '_' . Str::random(16) . '.' . $file->getClientOriginalExtension();
                Storage::disk('local')->put($filePath, file_get_contents($file->getRealPath()));
                $exam->file = $filePath;
            }
            $exam->name = [
                'ar' => $request->name_ar,
                'fr' => $translations['fr'] ?? '',
                'en' => $translations['en'] ?? '',
            ];
            $exam->specialization_id = $request->specialization_id;
            $exam->grade_id = $classroom->grade_id;
            $exam->classroom_id = $classroom->id;
            $exam->Annscolaire = $request->Annscolaire;
            $exam->save();

            toastr()->success(trans('messages.Update'));
            return redirect()->route('Exames.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Exames $exames, $id)
{
    $Exames = Exames::findOrFail($id);
    $this->authorize('view', $Exames);

    $extension = pathinfo($Exames->file, PATHINFO_EXTENSION) ?: 'pdf';
    $filename = ($Exames->name['ar'] ?? 'exam') . '.' . $extension;

    if ($Exames->file && Storage::disk('local')->exists($Exames->file)) {
        return Storage::disk('local')->download($Exames->file, $filename);
    }

    $this->migrateLegacyExamIfExists($Exames);
    if ($Exames->file && Storage::disk('local')->exists($Exames->file)) {
        return Storage::disk('local')->download($Exames->file, $filename);
    }

    $legacyFile = public_path("exames/" . $Exames->file);
    if (!file_exists($legacyFile)) {
        abort(404, 'الملف غير موجود');
    }

    return response()->download($legacyFile, $filename);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyExameRequest $request, $id)
    {
        $validated = $request->validated();
        $exam = Exames::findOrFail((int) $validated['id']);
        $this->authorize('delete', $exam);

        if ($exam->file && Storage::disk('local')->exists($exam->file)) {
            Storage::disk('local')->delete($exam->file);
        }
        if ($exam->file && file_exists(public_path('exames/' . $exam->file))) {
            @unlink(public_path('exames/' . $exam->file));
        }

        $exam->delete();
        toastr()->error(trans('messages.delete'));
        return redirect()->route('Exames.index');
    }

    private function migrateLegacyExamIfExists(Exames $exam): void
    {
        if (!$exam->file) {
            return;
        }

        $legacyFile = public_path('exames/' . $exam->file);
        if (!file_exists($legacyFile)) {
            return;
        }

        $targetPath = 'private/exames/legacy/' . basename($exam->file);
        Storage::disk('local')->put($targetPath, file_get_contents($legacyFile));
        @unlink($legacyFile);

        $exam->file = $targetPath;
        $exam->save();
    }
}
