<?php

namespace App\Http\Controllers\AgendaScolaire;

use App\Models\School\School;
use App\Models\AgendaScolaire\Publication;
use App\Models\AgendaScolaire\Agenda;
use App\Models\AgendaScolaire\Gallery;
use App\Models\AgendaScolaire\Grade;
use App\Models\Inscription\Inscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublication;
use App\Http\Requests\UpdatePublicationRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Services\OpenAiTranslationService;

class PublicationController extends Controller
{
    public function __construct(private OpenAiTranslationService $translator)
    {
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
            ->with(['grade', 'agenda'])
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
        $data['Grade'] = Grade::all();
        $data['Agenda'] = Agenda::all();
        $data['School'] = School::query()
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

        $schoolId = $this->currentSchoolId();

        try {
            if ($schoolId && (int) $request->school_id2 !== (int) $schoolId) {
                abort(403, 'غير مصرح لك بإضافة منشورات لمدرسة مختلفة.');
            }

            $originalTitle = $request->titlear;
            $originalBody  = $request->bodyar;

            $translations = $this->translator->translatePublicationContent($originalTitle, $originalBody);
            $titleTranslations = $translations['title'];
            $bodyTranslations = $translations['body'];

            // 📝 حفظ المنشور
            $Publication = new Publication();
            $Publication->school_id = $request->school_id2;
            $Publication->grade_id  = $request->grade_id2;
            $Publication->agenda_id = $request->agenda_id;
            $Publication->title     = $titleTranslations;
            $Publication->body      = $bodyTranslations;
            $Publication->like      = rand(90, 999);
            $Publication->save();

            // 📷 رفع الصور
            $data = [];
            if ($request->hasfile('img_url')) {
                foreach ($request->img_url as $image) {
                    $randstr = Str::random(rand(4, 10));
                    $img_url = time() . $randstr . '.' . $image->extension();
                    $path = $image->storeAs('agenda', $img_url, 'public');
                    $data[] = $img_url;
                }
            }

            $Gallery = new Gallery();
            $Gallery->publication_id = $Publication->id;
            $Gallery->agenda_id      = $request->agenda_id;
            $Gallery->grade_id       = $request->grade_id2;
            $Gallery->img_url        = json_encode($data ?? []);
            $Gallery->save();

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
            $Publication = Publication::findOrFail($id);
            $this->authorize('update', $Publication);

            $schoolId = $this->currentSchoolId();
            if ($schoolId && (int) $request->school_id2 !== (int) $schoolId) {
                abort(403, 'غير مصرح لك بتعديل منشورات مدرسة مختلفة.');
            }

            $originalTitle = $request->titlear;
            $originalBody  = $request->bodyar;

            $translations = $this->translator->translatePublicationContent($originalTitle, $originalBody);
            $titleTranslations = $translations['title'];
            $bodyTranslations = $translations['body'];

            $Publication->school_id = $request->school_id2;
            $Publication->grade_id  = $request->grade_id2;
            $Publication->agenda_id = $request->agenda_id;
            $Publication->title     = $titleTranslations;
            $Publication->body      = $bodyTranslations;
            $Publication->save();

            if ($request->hasfile('img_url')) {
                $data = [];
                foreach ($request->img_url as $image) {
                    $randstr = Str::random(rand(4, 10));
                    $img_url = time() . $randstr . '.' . $image->extension();
                    $path = $image->storeAs('agenda', $img_url, 'public');
                    $data[] = $img_url;
                }

                $gallery = Gallery::where('publication_id', $id)->first();
                if ($gallery && $gallery->img_url) {
                    $oldImages = json_decode($gallery->img_url, true);
                    foreach ($oldImages as $img) {
                        Storage::disk('public')->delete('agenda/' . $img);
                    }
                }

                if ($gallery) {
                    $gallery->img_url = json_encode($data);
                    $gallery->save();
                }
            }

            toastr()->success(trans('messages.Update'));
            return redirect()->route('Publications.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $publication = Publication::findOrFail($id);
            $this->authorize('delete', $publication);
            $gallery = Gallery::where('publication_id', $id)->first();

            if ($gallery && $gallery->img_url) {
                $images = json_decode($gallery->img_url, true);
                foreach ($images as $img) {
                    Storage::disk('public')->delete('agenda/' . $img);
                }
                $gallery->delete();
            }

            $publication->delete();

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
                $School = School::all();
                $Inscription = Inscription::all();
                return view('admin.home');
            } else {
                return view('welcome', compact('Publication'));
            }
        } else {
            return view('welcome', compact('Publication'));
        }
    }
}
