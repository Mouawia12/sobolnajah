<?php

namespace App\Http\Controllers\Functionn;

use App\Models\AgendaScolaire\Publication;
use App\Models\AgendaScolaire\Agenda;
use App\Models\AgendaScolaire\Gallery;
use App\Models\AgendaScolaire\Grade;

use App\Models\Inscription\Inscription;
use App\Models\Inscription\StudentInfo;
use App\Models\School\Section;
use App\Notifications\StudentSchoolCertificateNotification;
use App\Services\StudentEnrollmentService;

use Illuminate\Support\Facades\DB;




use App\Models\User;


use Illuminate\Support\Facades\Auth;



use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Throwable;

class FunctionController extends Controller
{
    public function __construct(private StudentEnrollmentService $enrollmentService)
    {
    }

    public function getAgenda($id){
        $Publications = Publication::all()->sortByDesc('created_at');
        //$PublicationHome = Publication::paginate(1)->sortByDesc('created_at');
        $Grade = Grade::all();
        $Agenda = Agenda::all();
        $Gallery = Gallery::all();
        if(Auth::user()){
            if(Auth::user()->hasRole('admin')){
                return view('admin.publications',compact('Publications','Grade','Agenda','Gallery'));
            }else {
                // if($Inscription[0]->ajax()){
                //     $view = view('layouts.posts',compact('PublicationHome'))->render();
                //     return response()->json(['html'=>$view]);
                // }
                return view('front-end.agenda',compact('Publications','Grade','Agenda'));
            }
            }else {
                // if($Inscription[0]->ajax()){
                //     $view = view('layouts.posts',compact('PublicationHome'))->render();
                //     return response()->json(['html'=>$view]);
                // }
                return view('front-end.agenda',compact('Publications','Grade','Agenda'));
            }
    }

    public function getAlbum(){
        $Gallery = Gallery::all();
        return view('front-end.gallrey',compact('Gallery'));
        //return view('admin.gallrey',compact('Gallery'));


    }

    public function store($id){
        if (!Auth::user() || !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        $schoolId = $this->currentSchoolId();

        try {
            DB::transaction(function () use ($id, $schoolId) {
                $inscription = Inscription::findOrFail($id);

                $section = Section::query()
                    ->forSchool($schoolId)
                    ->where('classroom_id', $inscription->classroom_id)
                    ->first();

                if (!$section) {
                    throw ValidationException::withMessages([
                        'section' => 'No section is available for the selected classroom.',
                    ]);
                }

                $inscription->update(['statu' => 'accept']);

                $studentPayload = [
                    'first_name' => [
                        'fr' => $inscription->getTranslation('prenom', 'fr'),
                        'ar' => $inscription->getTranslation('prenom', 'ar'),
                        'en' => $inscription->getTranslation('prenom', 'fr'),
                    ],
                    'last_name' => [
                        'fr' => $inscription->getTranslation('nom', 'fr'),
                        'ar' => $inscription->getTranslation('nom', 'ar'),
                        'en' => $inscription->getTranslation('nom', 'fr'),
                    ],
                    'email' => $inscription->email,
                    'gender' => $inscription->gender,
                    'phone' => $inscription->numtelephone,
                    'birth_date' => $inscription->datenaissance,
                    'birth_place' => $inscription->lieunaissance,
                    'wilaya' => $inscription->wilaya,
                    'dayra' => $inscription->dayra,
                    'baladia' => $inscription->baladia,
                ];

                $guardianPayload = [
                    'first_name' => [
                        'fr' => $inscription->getTranslation('prenomwali', 'fr'),
                        'ar' => $inscription->getTranslation('prenomwali', 'ar'),
                        'en' => $inscription->getTranslation('prenomwali', 'fr'),
                    ],
                    'last_name' => [
                        'fr' => $inscription->getTranslation('nomwali', 'fr'),
                        'ar' => $inscription->getTranslation('nomwali', 'ar'),
                        'en' => $inscription->getTranslation('nomwali', 'fr'),
                    ],
                    'relation' => $inscription->relationetudiant,
                    'address' => $inscription->adressewali,
                    'wilaya' => $inscription->wilayawali,
                    'dayra' => $inscription->dayrawali,
                    'baladia' => $inscription->baladiawali,
                    'phone' => $inscription->numtelephonewali,
                    'email' => $inscription->emailwali,
                ];

                $this->enrollmentService->createStudent($studentPayload, $guardianPayload, $section);
            });
        } catch (ValidationException $exception) {
            return redirect()->back()->withErrors($exception->errors());
        } catch (Throwable $exception) {
            return redirect()->back()->withErrors(['error' => $exception->getMessage()]);
        }

        toastr()->success(trans('messages.success'));

        return redirect()->route('Inscriptions.index');
    }


    public function notify(Request $request,$id){
        try {
            if(auth()->user()){
                $user = User::findorfail($id);
    
                auth()->user()->notify(new StudentSchoolCertificateNotification($user,$request->year,$request->namefr,$request->namear));
    
            }
            return back()->withSuccess('a');
        }
  
        catch (\Exception $e){
            return redirect()->back()->withErrors(['لقد ارسلت طلب شهادة مدرسية بالفعل !']);
        }
        
    }

    public function markasread($id){
        if($id){
            $notify = DB::table('notifications')->where('id', $id)->first();
            $user = User::find($notify->notifiable_id);
        


            $notification = $user->notifications()->where('id', $id)->first();

            if ($notification) {
                $notification->markAsRead();
            }
           
            $data['notify'] = $this->notifications();
            $data['StudentInfo'] = StudentInfo::where('user_id',$notify->notifiable_id)->first();
            $data['arryear'] = $notify->data;

            return view('admin.StudentSchoolCertificateNotification',$data);  
        }
         
    }



    public function changepass(){
        $data['notify'] = $this->notifications();
        return view('admin.changepassword',$data);
    }















}
