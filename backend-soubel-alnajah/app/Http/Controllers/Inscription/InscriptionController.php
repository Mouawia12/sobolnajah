<?php

namespace App\Http\Controllers\Inscription;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInscription;
use App\Models\Inscription\Inscription;
use App\Models\School\School;
use App\Models\School\Section;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class InscriptionController extends Controller
{
    public function __construct(private StudentEnrollmentService $enrollmentService)
    {
    }

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

        $data['Inscription'] = Inscription::query()
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->with(['Classroom.Schoolgrade.School', 'Classroom.Sections'])
            ->orderByDesc('created_at')
            ->get();

        $data['notify'] = $this->notifications();

        if(Auth::user()){
        if(Auth::user()->hasRole('admin')){
            return view('admin.studentsinscription',$data);
        }else {
            return view('front-end.inscription',$data);
        }
        }else {
        return view('front-end.inscription',$data);
        }
    }


    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInscription $request)
    {
        $validated = $request->validated();

        try {
            $Inscription = new Inscription();

            $Inscription->school_id = $request->school_id;
            $Inscription->grade_id = $request->grade_id;
            $Inscription->classroom_id = $request->classroom_id;
            $Inscription->inscriptionetat  = $request->inscriptionetat;
            $Inscription->nomecoleprecedente  = $request->nomecoleprecedente;
            $Inscription->dernieresection  = $request->dernieresection;
            $Inscription->moyensannuels  = $request->moyensannuels;
            $Inscription->numeronationaletudiant  = $request->numeronationaletudiant;

            // ✅ تعديل الحقول القابلة للترجمة
            $Inscription->prenom = [
                'fr' => $request->prenomfr,
                'ar' => $request->prenomar,
                'en' => $request->prenomfr,
            ];
            $Inscription->nom = [
                'fr' => $request->nomfr,
                'ar' => $request->nomar,
                'en' => $request->nomfr,
            ];

            $Inscription->email = $request->email;
            $Inscription->gender = $request->gender;
            $Inscription->numtelephone  = $request->numtelephone;
            $Inscription->datenaissance = $request->datenaissance;
            $Inscription->lieunaissance = $request->lieunaissance;
            $Inscription->wilaya = $request->wilaya;
            $Inscription->dayra = $request->dayra;
            $Inscription->baladia = $request->baladia;
            $Inscription->adresseactuelle = $request->adresseactuelle;
            $Inscription->codepostal = $request->codepostal;
            $Inscription->residenceactuelle = $request->residenceactuelle;
            $Inscription->etatsante = $request->etatsante;
            $Inscription->identificationmaladie = $request->identificationmaladie;
            $Inscription->alfdlprsaldr = $request->alfdlprsaldr;
            $Inscription->autresnotes = $request->autresnotes;

            // ✅ إضافة اللغة الإنجليزية لولي الأمر
            $Inscription->prenomwali = [
                'fr' => $request->prenomfrwali,
                'ar' => $request->prenomarwali,
                'en' => $request->prenomfrwali,
            ];
            $Inscription->nomwali = [
                'fr' => $request->nomfrwali,
                'ar' => $request->nomarwali,
                'en' => $request->nomfrwali,
            ];

            $Inscription->relationetudiant = $request->relationetudiant;
            $Inscription->adressewali = $request->adressewali;
            $Inscription->numtelephonewali = $request->numtelephonewali;
            $Inscription->emailwali = $request->emailwali;
            $Inscription->wilayawali = $request->wilayawali;
            $Inscription->dayrawali = $request->dayrawali;
            $Inscription->baladiawali = $request->baladiawali;
            $Inscription->statu = "procec";

            $Inscription->save();

            toastr()->success(trans('messages.success'));
            return redirect()->route('Inscriptions.index')->withSuccess('Bien ajouté');
        }
        catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Inscription\Inscription  $inscription
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,$id)
    {

        $request->validate([
            'section_id2' => 'required|exists:sections,id',
        ]);

        if (!Auth::user() || !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        $schoolId = $this->currentSchoolId();

        try {
            $section = Section::query()
                ->forSchool($schoolId)
                ->findOrFail($request->section_id2);

            $inscription = Inscription::findOrFail($id);

            DB::transaction(function () use ($inscription, $section) {
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
            return back()->withErrors($exception->errors());
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        toastr()->success(trans('messages.success'));

        return redirect()->route('Inscriptions.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$id)
    {

        try{
        Inscription::where('id', $id)->
            update(['statu'=> $request->statu,]);
            toastr()->success(trans('messages.Update'));
            return redirect()->route('Inscriptions.index');
        }
        catch
        (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Inscription\Inscription  $inscription
     * @return \Illuminate\Http\Response
     */
    public function update(StoreInscription $request, $id)
    {
        $validated = $request->validated();

        try {
            Inscription::where('id', $id)->update([
                'school_id' => $request->school_id,
                'grade_id' => $request->grade_id,
                'classroom_id' => $request->classroom_id,
                'inscriptionetat'  => $request->inscriptionetat,
                'nomecoleprecedente'  => $request->nomecoleprecedente,
                'dernieresection'  => $request->dernieresection,
                'moyensannuels'  => $request->moyensannuels,
                'numeronationaletudiant'  => $request->numeronationaletudiant,

                // ✅ نضيف الإنجليزية مثل الفرنسية
                'prenom' => [
                    'fr' => $request->prenomfr,
                    'ar' => $request->prenomar,
                    'en' => $request->prenomfr,
                ],
                'nom' => [
                    'fr' => $request->nomfr,
                    'ar' => $request->nomar,
                    'en' => $request->nomfr,
                ],

                'email' => $request->email,
                'numtelephone'  => $request->numtelephone,
                'datenaissance' => $request->datenaissance,
                'lieunaissance' => $request->lieunaissance,
                'wilaya' => $request->wilaya,
                'dayra' => $request->dayra,
                'baladia' => $request->baladia,
                'adresseactuelle' => $request->adresseactuelle,
                'codepostal' => $request->codepostal,
                'residenceactuelle' => $request->residenceactuelle,
                'etatsante' => $request->etatsante,
                'identificationmaladie' => $request->identificationmaladie,
                'alfdlprsaldr' => $request->alfdlprsaldr,
                'autresnotes' => $request->autresnotes,

                // ✅ نفس الشي لولي الأمر
                'prenomwali' => [
                    'fr' => $request->prenomfrwali,
                    'ar' => $request->prenomarwali,
                    'en' => $request->prenomfrwali,
                ],
                'nomwali' => [
                    'fr' => $request->nomfrwali,
                    'ar' => $request->nomarwali,
                    'en' => $request->nomfrwali,
                ],

                'relationetudiant' => $request->relationetudiant,
                'adressewali' => $request->adressewali,
                'numtelephonewali' => $request->numtelephonewali,
                'emailwali' => $request->emailwali,
                'wilayawali' => $request->wilayawali,
                'dayrawali' => $request->dayrawali,
                'baladiawali' => $request->baladiawali,
                'statu' => $request->statu,
            ]);

            toastr()->success(trans('messages.Update'));
            return redirect()->route('Inscriptions.index');
        }
        catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Inscription\Inscription  $inscription
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
    //     $MySchoolgrade_id = Schoolgrade::where('school_id',$id)->pluck('school_id');
    //    if($MySchoolgrade_id->count() == 0){

          Inscription::where('id',$id)->delete();
          toastr()->error(trans('messages.delete'));
          return redirect()->route('Inscriptions.index');
    //   }

    //   else{

    //       toastr()->error('Error');
    //       return redirect()->route('Schoolgrades.index');

    //   }
    }
}
