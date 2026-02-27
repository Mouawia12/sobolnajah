<?php

namespace App\Http\Controllers\AgendaScolaire;

use App\Actions\Publication\CreatePublicationAction;
use App\Actions\Publication\DeletePublicationAction;
use App\Actions\Publication\UpdatePublicationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyPublicationRequest;
use App\Http\Requests\StorePublication;
use App\Http\Requests\UpdatePublicationRequest;
use App\Models\AgendaScolaire\Agenda;
use App\Models\AgendaScolaire\Grade;
use App\Models\School\School;
use App\Models\AgendaScolaire\Publication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicationController extends Controller
{
    public function __construct(
        private CreatePublicationAction $createPublicationAction,
        private UpdatePublicationAction $updatePublicationAction,
        private DeletePublicationAction $deletePublicationAction
    ) {
        $this->middleware(['auth', 'role:admin'])->only(['create', 'edit', 'store', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        $schoolId = $this->currentSchoolId();
        $isAdmin = Auth::check() && Auth::user()->hasRole('admin');

        if (!$isAdmin) {
            return view('front-end.publications');
        }

        $search = trim((string) $request->query('q'));
        $gradeId = $request->query('grade_id');
        $agendaId = $request->query('agenda_id');
        $from = $request->query('date_from');
        $to = $request->query('date_to');

        $publicationQuery = Publication::query()
            ->select(['id', 'school_id', 'grade_id', 'agenda_id', 'title', 'body', 'created_at'])
            ->with(['gallery:id,publication_id,img_url'])
            ->orderByDesc('created_at');

        if ($schoolId) {
            $publicationQuery->where('school_id', $schoolId);
        }

        $publicationQuery
            ->when($gradeId, fn ($query) => $query->where('grade_id', $gradeId))
            ->when($agendaId, fn ($query) => $query->where('agenda_id', $agendaId))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($publicationQuery) use ($search) {
                    $publicationQuery->where('title', 'like', '%' . $search . '%')
                        ->orWhere('body', 'like', '%' . $search . '%');
                });
            });

        $data['Publications'] = $publicationQuery->paginate(20)->withQueryString();
        $data['Grade'] = Grade::query()
            ->select(['id', 'name_grades'])
            ->orderBy('id')
            ->get();
        $data['Agenda'] = Agenda::query()
            ->select(['id', 'name_agenda'])
            ->orderBy('id')
            ->get();
        $data['School'] = School::query()
            ->select(['id', 'name_school'])
            ->when($schoolId, fn ($query) => $query->whereKey($schoolId))
            ->get();
        $data['notify'] = $this->notifications();
        $data['breadcrumbs'] = [
            ['label' => 'لوحة التحكم', 'url' => url('/admin')],
            ['label' => trans('main_sidebar.publication')],
        ];

        return view('admin.publications', $data);
    }

    public function create()
    {
        //
    }

    public function store(StorePublication $request)
    {
        $validated = $request->validated();
        $this->authorize('create', Publication::class);

        try {
            $this->createPublicationAction->execute($validated, $this->currentSchoolId());

            toastr()->success(trans('messages.success'));
            return redirect()->route('Publications.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function show(Publication $publication, $id)
    {
        $singlePublication = Publication::query()
            ->with('galleries')
            ->findOrFail($id);

        $data['Publications'] = collect([$singlePublication]);
        $data['Grade'] = Cache::remember(
            'public:publication:grades',
            now()->addMinutes(15),
            fn () => Grade::query()->orderBy('id')->get()
        );
        $data['Agenda'] = Cache::remember(
            'public:publication:agendas',
            now()->addMinutes(15),
            fn () => Agenda::query()->orderBy('id')->get()
        );
        return view('front-end.singlepublication', $data);
    }

    public function edit(Publication $publication)
    {
        //
    }

    public function update(UpdatePublicationRequest $request, $id)
    {
        $validated = $request->validated();

        try {
            $publication = Publication::findOrFail($id);
            $this->authorize('update', $publication);
            $this->updatePublicationAction->execute($publication, $validated, $this->currentSchoolId());

            toastr()->success(trans('messages.Update'));
            return redirect()->route('Publications.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(DestroyPublicationRequest $request, $id)
    {
        $validated = $request->validated();

        try {
            $publication = Publication::findOrFail((int) $validated['id']);
            $this->authorize('delete', $publication);
            $this->deletePublicationAction->execute($publication);

            toastr()->error(trans('messages.delete'));
            return redirect()->route('Publications.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function welcome()
    {
        $Publication = Publication::orderBy('id', 'desc')->take(3)->get();

        if (Auth::user()) {
            if (Auth::user()->hasRole('admin')) {
                return view('admin.home');
            } else {
                return view('welcome', compact('Publication'));
            }
        } else {
            return view('welcome', compact('Publication'));
        }
    }

}
