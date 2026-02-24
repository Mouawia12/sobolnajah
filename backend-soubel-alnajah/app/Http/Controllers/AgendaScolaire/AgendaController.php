<?php

namespace App\Http\Controllers\AgendaScolaire;

use App\Http\Requests\StoreAgenda;
use App\Models\AgendaScolaire\Agenda;
use App\Models\AgendaScolaire\Publication;
use App\Services\OpenAiTranslationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AgendaController extends Controller
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
        $data['notify'] = $this->notifications();
        $data['Agenda'] = Agenda::all();
        return view('admin.agenda',$data);
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
     * @param  \App\Http\Requests\StoreAgenda  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAgenda $request)
    {
        $validated = $request->validated();

        try {
            $translations = $this->translator->translateToFrenchAndEnglish($request->name_agendaar);

            $Agenda = new Agenda();
            $Agenda->name_agenda = [
                'ar' => $request->name_agendaar,
                'fr' => $translations['fr'] ?? '',
                'en' => $translations['en'] ?? '',
            ];
            $Agenda->save();

            toastr()->success(trans('messages.success'));
            return redirect()->route('Agendas.index');

        } catch (\Exception $e){
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreAgenda  $request
     * @param  \App\Models\Agenda  $agenda
     * @return \Illuminate\Http\Response
     */
    public function update(StoreAgenda $request,$id)
    {
        $validated = $request->validated();

        try {
            $translations = $this->translator->translateToFrenchAndEnglish($request->name_agendaar);

            Agenda::where('id', $id)->update([
                'name_agenda->ar' => $request->name_agendaar,
                'name_agenda->fr' => $translations['fr'] ?? '',
                'name_agenda->en' => $translations['en'] ?? '',
            ]);

            toastr()->success(trans('messages.Update'));
            return redirect()->route('Agendas.index');
        }
        catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Agenda  $agenda
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
        $MyAgenda_id = Publication::where('agenda_id',$id)->pluck('agenda_id');
      
        if($MyAgenda_id->count() == 0){
            Agenda::where('id',$id)->delete();
            toastr()->error(trans('messages.delete'));
            return redirect()->route('Agendas.index');
        }
        else{
            toastr()->error(trans('messages.cantdelete'));
            return redirect()->route('Agendas.index');
        }
    }
}
