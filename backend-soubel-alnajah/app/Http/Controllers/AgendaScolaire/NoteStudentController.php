<?php

namespace App\Http\Controllers\AgendaScolaire;


use Illuminate\Http\Request;

use App\Models\AgendaScolaire\NoteStudent;
use App\Models\Inscription\StudentInfo;



use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

use File;



class NoteStudentController extends Controller
{

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
        $data['NoteStudents'] = NoteStudent::all();
        $data['StudentInfo']  = StudentInfo::where("section_id",$id)->get();
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

    public function store(Request $request)
     {

    //$validated = $request->validated();
    
    $notestudent = NoteStudent::where('student_id', $request->student_id)->first();
    $note ="urlfile{$request->Anneescolaire}";
    
    // $notepdf = $request->note_file;
    // return $notepdf->extension();

    try {

        $randint = rand(4, 10);
        $randstr = Str::random($randint);
        $notepdf = $request->note_file;
        $note_url = time().$randstr.'.'.$notepdf->extension();

        if(is_null($notestudent)){
         $NoteStudent = new NoteStudent();

         $NoteStudent-> student_id = $request->student_id ;
         $NoteStudent-> $note = $note_url;
         $NoteStudent->save();

        }
        else{
            File::delete(public_path('note/'.$notestudent->$note));

            NoteStudent::updateOrCreate([
                'student_id'=>$request->student_id ,
            ],[
                'student_id'=>$request->student_id ,
                "urlfile{$request->Anneescolaire}"=>$note_url,     
            ]);
        }
        if($request->hasfile('note_file'))
        {
            $notepdf -> move(public_path('note'), $note_url);
        }
       
             toastr()->success(trans('messages.success'));
             return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }


}
    



    public function destroy(Request $request,$id)
    {
    
        NoteStudent::where('id',$id)->delete();
        toastr()->error(trans('messages.delete'));
        return redirect()->back();

        //return redirect()->route('Publications.index');

    }

  

    public function DownloadNoteFromAdmin($url)
    {
    
        $Notestudent =  NoteStudent::where('urlfile1',$url)->first();
        
        $Student =  StudentInfo::where("id",$Notestudent->student_id)->first();
        $headers = array(
            'Content-Type: application/pdf',
          );

        $file = public_path()."/note/".$url;
        return \Response::download($file,$Student->prenom.' '.$Student->nom.'.pdf');
    }


    public function DisplqyNoteFromAdmin($url)
    {
        try {
            $headers = array(
                'Content-Type: application/pdf',
              );
    
            $file = public_path()."/note/".$url;
            return response()->file($file);

            toastr()->success(trans('messages.success'));
            return redirect()->back();

            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['error' => $e->getMessage()]);
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
