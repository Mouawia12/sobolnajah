<?php

namespace App\Http\Controllers\School;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\School\School;
use App\Models\School\Schoolgrade;
use App\Models\School\Section;

use App\Models\Inscription\Teacher;

use App\Http\Requests\StoreSection;
use Illuminate\Support\Facades\DB;
use Throwable;

class SectionController extends Controller
{
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

        $data['Schoolgrade'] = Schoolgrade::query()
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->orderBy('name_grade')
            ->get();

        $data['Section'] = Section::query()
            ->forSchool($schoolId)
            ->with(['classroom', 'teachers'])
            ->orderBy('created_at', 'desc')
            ->get();

        $data['Teacher'] = Teacher::query()
            ->forSchool($schoolId)
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        $data['notify'] = $this->notifications();

        return view('admin.sections', $data);
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSection $request)
    {
        $request->validated();

        $schoolId = $this->currentSchoolId();

        if ($schoolId && (int) $request->school_id !== $schoolId) {
            return back()->withErrors(['school_id' => trans('messages.error')]);
        }

        $teacherIds = collect($request->teacher_id)->filter()->unique()->all();

        if ($schoolId) {
            $teacherIds = Teacher::query()
                ->forSchool($schoolId)
                ->whereIn('id', $teacherIds)
                ->pluck('id')
                ->all();
        }

        try {
            DB::transaction(function () use ($request, $teacherIds) {
                $section = Section::create([
                    'school_id' => $request->school_id,
                    'grade_id' => $request->grade_id,
                    'classroom_id' => $request->classroom_id,
                    'name_section' => [
                        'fr' => $request->name_sectionfr,
                        'en' => $request->name_sectionfr,
                        'ar' => $request->name_sectionar,
                    ],
                    'Status' => 1,
                ]);

                if (!empty($teacherIds)) {
                    $section->teachers()->sync($teacherIds);
                }
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        toastr()->success(trans('messages.success'));

        return redirect()->route('Sections.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        if ($request->teacher_test == 1) {
            $Sections = Section::query()
                ->forSchool($this->currentSchoolId())
                ->findOrFail($id);
            //update pivot tABLE
            if (isset($request->teacher_id)) {
                $teacherIds = Teacher::query()
                    ->forSchool($this->currentSchoolId())
                    ->whereIn('id', (array) $request->teacher_id)
                    ->pluck('id')
                    ->all();
                $Sections->teachers()->sync($teacherIds);
            } else {
                $Sections->teachers()->sync([]);
            }
            $Sections->save();
        } else {
            try {
                Section::query()
                    ->forSchool($this->currentSchoolId())
                    ->where('id', $id)
                    ->update(['Status' => $request->statu]);
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['error' => $e->getMessage()]);
            }
        }
        toastr()->success(trans('messages.Update'));
        return redirect()->route('Sections.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreSection $request, $id)
    {
        try {
            $validated = $request->validated();

            $Sections = Section::query()
                ->forSchool($this->currentSchoolId())
                ->findOrFail($id);

            if ($this->currentSchoolId() && (int) $request->school_id !== $Sections->school_id) {
                return back()->withErrors(['school_id' => trans('messages.error')]);
            }

            $teacherIds = Teacher::query()
                ->forSchool($this->currentSchoolId())
                ->whereIn('id', (array) ($request->teacher_id ?? []))
                ->pluck('id')
                ->all();

            // إضافة ترجمة الانجليزية بنفس قيمة الفرنسية
            $Sections->name_section = [
                'fr' => $request->name_sectionfr,
                'en' => $request->name_sectionfr,
                'ar' => $request->name_sectionar
            ];
            $Sections->grade_id = $request->grade_id;
            $Sections->classroom_id = $request->classroom_id;
            $Sections->Status = $request->statu;

            //update pivot tABLE
            $Sections->teachers()->sync($teacherIds);

            $Sections->save();

            toastr()->success(trans('messages.Update'));
            return redirect()->route('Sections.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Section::query()
            ->forSchool($this->currentSchoolId())
            ->where('id', $id)
            ->delete();
        toastr()->error(trans('messages.delete'));
        return redirect()->route('Sections.index');
    }

    public function getSection($id)
    {
        $list_section = Section::query()
            ->forSchool($this->currentSchoolId())
            ->where("classroom_id", $id)
            ->pluck("name_section", "id");
        return $list_section;
    }

    public function getSection2($id)
    {
        $list_section = Section::query()
            ->forSchool($this->currentSchoolId())
            ->where("id", $id)
            ->pluck("name_section", "id");
        return $list_section;
    }
}
