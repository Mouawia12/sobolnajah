<?php

namespace App\Http\Controllers\AgendaScolaire;

use Illuminate\Support\Facades\Auth;
use App\Models\AgendaScolaire\Exames;
use Illuminate\Http\Request;
use App\Models\School\Schoolgrade;
use App\Http\Controllers\Controller;
use App\Models\Specialization\Specialization;
use App\Services\OpenAiTranslationService;
use Illuminate\Support\Str;

class ExamesController extends Controller
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
        $data['Exames'] = Exames::all();
        
        if(Auth::user()){
            if(Auth::user()->hasRole('admin')){
                $data['notify'] = $this->notifications();
                $data['Schoolgrade'] = Schoolgrade::all();
                $data['Specializations'] = Specialization::all();
                return view('admin.exam',$data);
            } else {
                return view('front-end.exam',$data);
            }
        } else {
            return view('front-end.exam',$data);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $translations = $this->translator->translateToFrenchAndEnglish($request->name_ar);

            // رفع الملف إن وجد
            if($request->hasfile('file_url')) {
                $randint = rand(4, 10);
                $randstr = Str::random($randint);
                $filepath_url = time().$randstr.'.'.$request->file_url->extension();
                $request->file_url->move(public_path('exames'), $filepath_url);
            }

            Exames::create([
                'file' => isset($filepath_url) ? (string)$filepath_url : null,
                'name' => [
                    'ar' => $request->name_ar,
                    'fr' => $translations['fr'] ?? '',
                    'en' => $translations['en'] ?? '',
                ],
                'specialization_id' => $request->specialization_id,
                'grade_id' => $request->grade_id,
                'classroom_id' => $request->classroom_id,
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
    public function update(Request $request, $id)
{
    try {
        $exam = Exames::findOrFail($id);

        $translations = $this->translator->translateToFrenchAndEnglish($request->name_ar);

        // إذا فيه ملف جديد يتم رفعه
        if($request->hasFile('file_url')) {
            // حذف الملف القديم إذا موجود
            if ($exam->file && file_exists(public_path('exames/'.$exam->file))) {
                unlink(public_path('exames/'.$exam->file));
            }

            $randint = rand(4, 10);
            $randstr = Str::random($randint);
            $filepath_url = time().$randstr.'.'.$request->file_url->extension();
            $request->file_url->move(public_path('exames'), $filepath_url);

            $exam->file = (string)$filepath_url;
        }

        // تحديث باقي البيانات
        $exam->name = [
            'ar' => $request->name_ar,
            'fr' => $translations['fr'] ?? '',
            'en' => $translations['en'] ?? '',
        ];
        $exam->specialization_id = $request->specialization_id;
        $exam->grade_id = $request->grade_id;
        $exam->classroom_id = $request->classroom_id;
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
    $file = public_path("exames/" . $Exames->file);

    if (!file_exists($file)) {
        abort(404, 'الملف غير موجود');
    }

    // جلب الامتداد من اسم الملف الأصلي
    $extension = pathinfo($Exames->file, PATHINFO_EXTENSION);

    // تحديد الاسم للتحميل (من الترجمة أو الاسم العربي)
    $filename = ($Exames->name['ar'] ?? 'exam') . '.' . $extension;

    return response()->download($file, $filename);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exames $exames, $id)
    {
        Exames::where('id',$id)->delete();
        toastr()->error(trans('messages.delete'));
        return redirect()->route('Exames.index');
    }
}
