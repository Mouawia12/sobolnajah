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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Services\OpenAiTranslationService;

class PublicationController extends Controller
{
    public function __construct(private OpenAiTranslationService $translator)
    {
    }

    public function index(Request $request)
    {
        $data['Publications'] = Publication::orderByDesc('created_at')->get();
        $data['Grade'] = Grade::all();
        $data['Agenda'] = Agenda::all();
        $data['School'] = School::all();
        $data['notify'] = $this->notifications();

        if (Auth::user()) {
            if (Auth::user()->hasRole('admin')) {
                return view('admin.publications', $data);
            } else {
                return view('front-end.publications', $data);
            }
        } else {
            return view('front-end.publications', $data);
        }
    }

    public function create()
    {
        //
    }

    public function store(StorePublication $request)
    {
        $validated = $request->validated();

        try {
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
        $data['Publications'] = Publication::where('id', $id)->get();
        $data['Grade'] = Grade::all();
        $data['Agenda'] = Agenda::all();
        return view('front-end.singlepublication', $data);
    }

    public function edit(Publication $publication)
    {
        //
    }

    public function update(StorePublication $request, $id)
    {
        $validated = $request->validated();

        try {
            $originalTitle = $request->titlear;
            $originalBody  = $request->bodyar;

            $translations = $this->translator->translatePublicationContent($originalTitle, $originalBody);
            $titleTranslations = $translations['title'];
            $bodyTranslations = $translations['body'];

            $Publication = Publication::findOrFail($id);
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

    public function destroy(Request $request, $id)
    {
        try {
            $publication = Publication::findOrFail($id);
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
