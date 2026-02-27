<?php

namespace App\Http\Controllers\AgendaScolaire;


use App\Http\Requests\DestroyNoteStudentRequest;
use App\Http\Requests\StoreNoteStudentRequest;
use App\Models\AgendaScolaire\NoteStudent;
use App\Models\Inscription\StudentInfo;
use App\Models\School\Section;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class NoteStudentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return 0;

    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->authorize('viewAny', NoteStudent::class);

        $schoolId = $this->currentSchoolId();
        $search = trim((string) request('q'));
        $hasNotes = request('has_notes');

        $section = Section::query()
            ->forSchool($schoolId)
            ->with('classroom.schoolgrade')
            ->findOrFail($id);

        $data['section'] = $section;
        $data['StudentInfo'] = StudentInfo::query()
            ->forSchool($schoolId)
            ->where("section_id", $id)
            ->with(['user', 'noteStudent'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($studentQuery) use ($search) {
                    $studentQuery->where('prenom->fr', 'like', '%' . $search . '%')
                        ->orWhere('prenom->ar', 'like', '%' . $search . '%')
                        ->orWhere('nom->fr', 'like', '%' . $search . '%')
                        ->orWhere('nom->ar', 'like', '%' . $search . '%')
                        ->orWhere('numtelephone', 'like', '%' . $search . '%')
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', '%' . $search . '%'));
                });
            })
            ->when($hasNotes === '1', fn ($query) => $query->whereHas('noteStudent'))
            ->when($hasNotes === '0', fn ($query) => $query->whereDoesntHave('noteStudent'))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();
        $data['UploadStudents'] = StudentInfo::query()
            ->forSchool($schoolId)
            ->where('section_id', $id)
            ->orderBy('id')
            ->get(['id', 'prenom', 'nom']);
        $data['notify'] = $this->notifications();
        return view('admin.addnotestudent',$data);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return 0;
    }



    //store file notestudent

    public function store(StoreNoteStudentRequest $request)
     {
        $this->authorize('create', NoteStudent::class);
        $notestudent = NoteStudent::where('student_id', $request->student_id)->first();
        $noteColumn = "urlfile{$request->Anneescolaire}";

        try {
            $file = $request->file('note_file');
            $safeName = time() . '_' . Str::random(16) . '.' . $file->getClientOriginalExtension();
            $notePath = $this->localNotePath($safeName);
            Storage::disk('local')->put($notePath, file_get_contents($file->getRealPath()));

            if (is_null($notestudent)) {
                $noteStudent = new NoteStudent();
                $noteStudent->student_id = $request->student_id;
                $noteStudent->{$noteColumn} = $safeName;
                $noteStudent->save();
            } else {
                $oldValue = $notestudent->{$noteColumn};
                $oldPath = $this->localNotePath($oldValue);
                if ($oldValue && Storage::disk('local')->exists($oldPath)) {
                    Storage::disk('local')->delete($oldPath);
                }
                if ($oldValue && file_exists(public_path('note/' . $oldValue))) {
                    @unlink(public_path('note/' . $oldValue));
                }

                NoteStudent::updateOrCreate(
                    ['student_id' => $request->student_id],
                    [
                        'student_id' => $request->student_id,
                        $noteColumn => $safeName,
                    ]
                );
            }

            toastr()->success(trans('messages.success'));
            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    



    public function destroy(DestroyNoteStudentRequest $request, $id)
    {
        $validated = $request->validated();
        $noteStudent = NoteStudent::findOrFail((int) $validated['id']);
        $this->authorize('delete', $noteStudent);

        foreach (['urlfile1', 'urlfile2', 'urlfile3'] as $column) {
            $path = $noteStudent->{$column};
            $localPath = $this->localNotePath($path);
            if ($path && Storage::disk('local')->exists($localPath)) {
                Storage::disk('local')->delete($localPath);
            }
            if ($path && file_exists(public_path('note/' . $path))) {
                @unlink(public_path('note/' . $path));
            }
        }

        $noteStudent->delete();
        toastr()->error(trans('messages.delete'));
        return redirect()->back();

        //return redirect()->route('Publications.index');

    }

  

    public function DownloadNoteFromAdmin($url)
    {
        $this->assertSafeNoteFilename((string) $url);

        $Notestudent = NoteStudent::where('urlfile1', $url)
            ->orWhere('urlfile2', $url)
            ->orWhere('urlfile3', $url)
            ->firstOrFail();
        $this->authorize('view', $Notestudent);
        
        $Student =  StudentInfo::where("id",$Notestudent->student_id)->first();
        $downloadName = ($Student?->prenom ?? 'student') . ' ' . ($Student?->nom ?? 'note') . '.pdf';

        $localPath = $this->localNotePath($url);
        if (Storage::disk('local')->exists($localPath)) {
            return Storage::disk('local')->download($localPath, $downloadName, [
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        $this->migrateLegacyNoteIfExists((string) $url);
        if (Storage::disk('local')->exists($localPath)) {
            return Storage::disk('local')->download($localPath, $downloadName, [
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        $legacyFile = public_path() . "/note/" . $url;
        if (!file_exists($legacyFile)) {
            abort(404, 'الملف غير موجود');
        }

        return \Response::download($legacyFile, $downloadName);
    }


    public function displayNoteFromAdmin($url)
    {
        try {
            $this->assertSafeNoteFilename((string) $url);

            $notestudent = NoteStudent::where('urlfile1', $url)
                ->orWhere('urlfile2', $url)
                ->orWhere('urlfile3', $url)
                ->firstOrFail();
            $this->authorize('view', $notestudent);

            $localPath = $this->localNotePath($url);
            if (Storage::disk('local')->exists($localPath)) {
                return Storage::disk('local')->response($localPath, null, [
                    'Content-Type' => 'application/pdf',
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            }

            $this->migrateLegacyNoteIfExists((string) $url);
            if (Storage::disk('local')->exists($localPath)) {
                return Storage::disk('local')->response($localPath, null, [
                    'Content-Type' => 'application/pdf',
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            }

            $legacyFile = public_path() . "/note/" . $url;
            if (!file_exists($legacyFile)) {
                abort(404, 'الملف غير موجود');
            }

            return response()->file($legacyFile, [
                'Content-Type' => 'application/pdf',
                'X-Content-Type-Options' => 'nosniff',
            ]);

            toastr()->success(trans('messages.success'));
            return redirect()->back();

            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['error' => $e->getMessage()]);
            }
    
        
    }

    // Backward-compatible alias for legacy route/method naming.
    public function DisplqyNoteFromAdmin($url)
    {
        return $this->displayNoteFromAdmin($url);
    }

    private function localNotePath(?string $value): string
    {
        if (!$value) {
            return '';
        }

        return 'private/notes/' . $value;
    }

    private function migrateLegacyNoteIfExists(string $filename): void
    {
        if ($filename === '') {
            return;
        }

        $legacyFile = public_path('note/' . $filename);
        if (!file_exists($legacyFile)) {
            return;
        }

        Storage::disk('local')->put($this->localNotePath($filename), file_get_contents($legacyFile));
        @unlink($legacyFile);
    }

    private function assertSafeNoteFilename(string $value): void
    {
        $isSafe = preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*$/', $value) === 1
            && !str_contains($value, '..')
            && !str_contains($value, '/')
            && !str_contains($value, '\\');

        if (!$isSafe) {
            abort(404);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\NoteStudentController  $request
     * @return \Illuminate\Http\Response
     */

     
    // public function store(NoteStudentController $request)
    // {
    //     $validated = $request->validated();

    //     try {
    //         $Publication = new Publication();
        
    //         $Publication->school_id = $request->school_id2;
    //         $Publication->grade_id = $request->grade_id2;
    //         $Publication->agenda_id = $request->agenda_id;
    //         $Publication->title = ['fr' => $request->titlefr, 'ar' => $request->titlear];
    //         $Publication->body = ['fr' => $request->bodyfr, 'ar' => $request->bodyar];
    //         //$Publication->gallery=json_encode($data);
    //         $Publication-> like = rand(90, 999);
    //         $Publication->save();
            
        
    //         if($request->hasfile('img_url'))
    //       {

        
    //          foreach($request->img_url as $image)
    //          {
               

          
    //             $randint = rand(4, 10);
    //             $randstr = Str::random($randint);
    //             $img_url = time().$randstr.'.'.$image->extension();

    //             $image -> move(public_path('agenda'), $img_url);

            
    //             $data[] = $img_url;  
                
    //         }
    //      }

    //     // User::orderBy('created_at', 'desc')->first();
        
    //     //$last = DB::table('items')->latest()->first();
    
    //     }
  
    //     catch (\Exception $e){
    //         return redirect()->back()->withErrors(['error' => $e->getMessage()]);
    //     }

    //     try {
            



    //     $lastid = Publication::latest()->first();
        
    //     $Gallery= new Gallery();

    //     $Gallery-> publication_id = $lastid->id ;
    //     $Gallery-> agenda_id = $request->agenda_id;
    //     $Gallery-> grade_id = $request->grade_id2;
    //     $Gallery->img_url=json_encode($data);
         
        
    //     $Gallery->save();

    


    //          toastr()->success(trans('messages.success'));
    //          return redirect()->route('Publications.index');
    //     } catch (\Exception $e) {
    //         return redirect()->back()->withErrors(['error' => $e->getMessage()]);
    //     }
    // }
}
