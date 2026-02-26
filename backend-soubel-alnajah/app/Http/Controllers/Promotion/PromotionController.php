<?php

namespace App\Http\Controllers\Promotion;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyPromotionRequest;
use App\Http\Requests\StorePromotionRequest;
use App\Models\Inscription\StudentInfo;
use App\Models\Promotion\Promotion;
use App\Services\HomeDashboardCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Promotion::class);

        $schoolId = $this->currentSchoolId();
        $search = trim((string) request('q'));

        $data['promotions'] = Promotion::query()
            ->when($schoolId, fn ($query) => $query->where('from_school', $schoolId))
            ->with([
                'student',
                'f_school',
                'f_classroom',
                'f_grade',
                'f_section',
                't_school',
                't_classroom',
                't_grade',
                't_section',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery->where('prenom->fr', 'like', '%' . $search . '%')
                        ->orWhere('prenom->ar', 'like', '%' . $search . '%')
                        ->orWhere('nom->fr', 'like', '%' . $search . '%')
                        ->orWhere('nom->ar', 'like', '%' . $search . '%')
                        ->orWhere('numtelephone', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();
        $data['StudentInfo'] = StudentInfo::query()
            ->forSchool($schoolId)
            ->get();
        $data['notify'] = $this->notifications();

        return view('admin.promotion', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePromotionRequest $request)
    {
        $this->authorize('create', Promotion::class);

        $validated = $request->validated();
        $schoolId = $this->currentSchoolId();

        if ($schoolId && (
            (int) $validated['school_id'] !== $schoolId
            || (int) $validated['school_id_new'] !== $schoolId
        )) {
            abort(403);
        }

        try {
            DB::transaction(function () use ($validated, $schoolId) {
                $students = StudentInfo::query()
                    ->forSchool($schoolId)
                    ->where('section_id', $validated['section_id'])
                    ->get();

                if ($students->isEmpty()) {
                    throw new \RuntimeException(trans('messages.error'));
                }

                // Move students to target section and keep a promotion audit row per student.
                foreach ($students as $student) {
                    $student->update([
                        'section_id' => $validated['section_id_new'],
                    ]);

                    Promotion::updateOrCreate([
                        'student_id' => $student->id,
                        'from_school' => $validated['school_id'],
                        'from_grade' => $validated['grade_id'],
                        'from_Classroom' => $validated['classroom_id'],
                        'from_section' => $validated['section_id'],
                        'to_school' => $validated['school_id_new'],
                        'to_grade' => $validated['grade_id_new'],
                        'to_Classroom' => $validated['classroom_id_new'],
                        'to_section' => $validated['section_id_new'],
                    ]);
                }
            });
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }

        toastr()->success(trans('messages.success'));
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Promotion $promotion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Promotion $promotion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Promotion $promotion)
    {
        //
    }

    public function destroy(DestroyPromotionRequest $request)
    {
        $validated = $request->validated();
        $schoolId = $this->currentSchoolId();

        $promotion = null;
        if ((int) $validated['page_id'] !== 1 && !empty($validated['id'])) {
            $promotion = Promotion::query()
                ->when($schoolId, fn ($query) => $query->where('from_school', $schoolId))
                ->find($validated['id']);
        }
        $this->authorize('delete', $promotion);

        try {
            DB::transaction(function () use ($validated, $schoolId) {
                if ((int) $validated['page_id'] === 1) {
                    $promotions = Promotion::query()
                        ->when($schoolId, fn ($query) => $query->where('from_school', $schoolId))
                        ->get();

                    foreach ($promotions as $item) {
                        StudentInfo::query()
                            ->forSchool($schoolId)
                            ->where('id', $item->student_id)
                            ->update([
                                'section_id' => $item->from_section,
                            ]);
                    }

                    Promotion::query()
                        ->when($schoolId, fn ($query) => $query->where('from_school', $schoolId))
                        ->delete();

                    return;
                }

                $item = Promotion::query()
                    ->when($schoolId, fn ($query) => $query->where('from_school', $schoolId))
                    ->findOrFail($validated['id']);

                StudentInfo::query()
                    ->forSchool($schoolId)
                    ->where('id', $item->student_id)
                    ->update([
                        'section_id' => $item->from_section,
                    ]);

                $item->delete();
            });

            app(HomeDashboardCacheService::class)->forgetForSchool($schoolId);
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }

        toastr()->error(trans('messages.Delete'));
        return redirect()->back();
    }
}
