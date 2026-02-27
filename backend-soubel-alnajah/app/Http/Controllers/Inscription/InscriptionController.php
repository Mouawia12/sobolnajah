<?php

namespace App\Http\Controllers\Inscription;

use App\Actions\Inscription\ApproveInscriptionAction;
use App\Actions\Inscription\BuildInscriptionPayloadAction;
use App\Actions\Inscription\CreateInscriptionAction;
use App\Actions\Inscription\DeleteInscriptionAction;
use App\Actions\Inscription\UpdateInscriptionAction;
use App\Actions\Inscription\UpdateInscriptionStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveInscriptionRequest;
use App\Http\Requests\DestroyInscriptionRequest;
use App\Http\Requests\StoreInscription;
use App\Http\Requests\UpdateInscriptionStatusRequest;
use App\Models\Inscription\Inscription;
use App\Models\School\Classroom;
use App\Models\School\School;
use App\Models\School\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Throwable;

class InscriptionController extends Controller
{
    public function __construct(
        private ApproveInscriptionAction $approveInscriptionAction,
        private BuildInscriptionPayloadAction $buildInscriptionPayloadAction,
        private CreateInscriptionAction $createInscriptionAction,
        private UpdateInscriptionAction $updateInscriptionAction,
        private UpdateInscriptionStatusAction $updateInscriptionStatusAction,
        private DeleteInscriptionAction $deleteInscriptionAction
    )
    {
        $this->middleware(['auth', 'role:admin', 'force.password.change'])
            ->only(['show', 'approve', 'edit', 'update', 'destroy', 'updateStatus']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $isAdmin = Auth::check() && Auth::user()->hasRole('admin');

        if ($isAdmin) {
            $this->authorize('viewAny', Inscription::class);
        }

        $schoolId = $this->currentSchoolId();
        $search = trim((string) request('q'));
        $status = request('status');
        $classroomId = request('classroom_id');

        $data['School'] = School::query()
            ->when($schoolId, fn ($query) => $query->whereKey($schoolId))
            ->with('schoolgrades')
            ->orderBy('name_school')
            ->get();

        if ($isAdmin) {
            $inscriptionQuery = Inscription::query()
                ->select([
                    'id',
                    'school_id',
                    'classroom_id',
                    'prenom',
                    'nom',
                    'email',
                    'numtelephone',
                    'statu',
                    'created_at',
                ])
                ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
                ->with([
                    'Classroom:id,school_id,grade_id,name_class',
                    'Classroom.Schoolgrade:id,school_id,name_grade',
                    'Classroom.Schoolgrade.School:id,name_school',
                    'Classroom.Sections:id,classroom_id,name_section',
                ])
                ->when($classroomId, fn ($query) => $query->where('classroom_id', $classroomId))
                ->when($status, fn ($query) => $query->where('statu', $status))
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($inscriptionQuery) use ($search) {
                        $inscriptionQuery->where('prenom->fr', 'like', '%' . $search . '%')
                            ->orWhere('prenom->ar', 'like', '%' . $search . '%')
                            ->orWhere('prenom', 'like', '%' . $search . '%')
                            ->orWhere('nom->fr', 'like', '%' . $search . '%')
                            ->orWhere('nom->ar', 'like', '%' . $search . '%')
                            ->orWhere('nom', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('numtelephone', 'like', '%' . $search . '%');
                    });
                })
                ->orderByDesc('created_at');

            $data['Inscription'] = $inscriptionQuery->paginate(20)->withQueryString();

            $data['Classrooms'] = Classroom::query()
                ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
                ->with('schoolgrade')
                ->orderBy('id')
                ->get();
        }

        $data['notify'] = $this->notifications();
        $data['breadcrumbs'] = [
            ['label' => 'لوحة التحكم', 'url' => url('/admin')],
            ['label' => trans('inscription.inscriptionstudent')],
        ];

        if (Auth::user()) {
            if ($isAdmin) {
                return view('admin.studentsinscription', $data);
            } else {
                return view('front-end.inscription', $data);
            }
        }

        return view('front-end.inscription', $data);
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
        $this->authorize('create', Inscription::class);
        $validated = $request->validated();

        try {
            $payload = $this->buildInscriptionPayloadAction->forStore($request->all());
            $this->createInscriptionAction->execute($payload);

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
        abort(405);
    }

    public function approve(ApproveInscriptionRequest $request, $id)
    {
        $validated = $request->validated();

        if (!Auth::user() || !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        $schoolId = $this->currentSchoolId();

        try {
            $section = Section::query()
                ->forSchool($schoolId)
                ->findOrFail($validated['section_id2']);

            $inscription = Inscription::query()
                ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
                ->findOrFail((int) $validated['id']);
            $this->authorize('approve', $inscription);

            $this->approveInscriptionAction->execute($inscription, $section);
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
        abort(405);
    }

    public function updateStatus(UpdateInscriptionStatusRequest $request, $id)
    {
        $validated = $request->validated();

        $inscription = Inscription::query()
            ->when($this->currentSchoolId(), fn ($query, $schoolId) => $query->where('school_id', $schoolId))
            ->findOrFail((int) $validated['id']);
        $this->authorize('update', $inscription);

        $this->updateInscriptionStatusAction->execute($inscription, $validated['statu']);

        toastr()->success(trans('messages.Update'));
        return redirect()->route('Inscriptions.index');
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
        $inscription = Inscription::query()
            ->when($this->currentSchoolId(), fn ($query, $schoolId) => $query->where('school_id', $schoolId))
            ->findOrFail($id);
        $this->authorize('update', $inscription);
        $validated = $request->validated();

        try {
            $payload = $this->buildInscriptionPayloadAction->forUpdate($request->all());
            $this->updateInscriptionAction->execute($inscription, $payload);

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
    public function destroy(DestroyInscriptionRequest $request, $id)
    {
        $validated = $request->validated();
        $inscription = Inscription::query()
            ->when($this->currentSchoolId(), fn ($query, $schoolId) => $query->where('school_id', $schoolId))
            ->findOrFail((int) $validated['id']);
        $this->authorize('delete', $inscription);

        $this->deleteInscriptionAction->execute($inscription);
        toastr()->error(trans('messages.delete'));
        return redirect()->route('Inscriptions.index');
    }
}
