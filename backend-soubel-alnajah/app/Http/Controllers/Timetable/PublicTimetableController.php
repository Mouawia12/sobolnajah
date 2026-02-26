<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Models\School\Section;
use App\Models\Timetable\Timetable;
use Illuminate\Support\Facades\Cache;

class PublicTimetableController extends Controller
{
    public const SECTIONS_CACHE_KEY = 'public:timetables:sections';

    public function index()
    {
        $sectionId = request('section_id');

        $sections = Cache::remember(
            self::SECTIONS_CACHE_KEY,
            now()->addMinutes(10),
            function () {
                $publishedSectionIds = Timetable::query()
                    ->published()
                    ->select('section_id')
                    ->distinct();

                return Section::query()
                    ->whereIn('id', $publishedSectionIds)
                    ->with('classroom.schoolgrade')
                    ->orderBy('created_at')
                    ->get();
            }
        );

        $timetables = Timetable::query()
            ->published()
            ->with(['section.classroom.schoolgrade'])
            ->when($sectionId, fn ($query) => $query->where('section_id', $sectionId))
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('front-end.timetables.index', [
            'sections' => $sections,
            'timetables' => $timetables,
        ]);
    }

    public function show(Timetable $timetable)
    {
        if (!$timetable->is_published) {
            abort(404);
        }

        $timetable->load(['entries.teacher', 'section.classroom.schoolgrade.school']);

        return view('front-end.timetables.show', [
            'timetable' => $timetable,
        ]);
    }
}
