<?php

namespace App\Http\Controllers\Inscription;

use App\Models\Inscription\Teacher;
use App\Models\User;
use App\Models\Specialization\Specialization;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreTeacher;
use Illuminate\Support\Facades\DB;
use Throwable;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $schoolId = $this->currentSchoolId();

        $data['Teacher'] = Teacher::query()
            ->forSchool($schoolId)
            ->with(['user', 'specialization'])
            ->orderByDesc('created_at')
            ->get();

        $data['Specializations'] = Specialization::all();
        $data['notify'] = $this->notifications();

        return view('admin.teacher', $data);
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
    public function store(StoreTeacher $request)
    {
        $request->validated();

        $schoolId = $this->currentSchoolId();

        try {
            DB::transaction(function () use ($request, $schoolId) {
                $user = User::create([
                    'name' => [
                        'fr' => $request->name_teacherfr,
                        'ar' => $request->name_teacherar,
                        'en' => $request->name_teacherfr,
                    ],
                    'email' => $request->email,
                    'password' => Hash::make('profsobolnajah2022'),
                    'school_id' => $schoolId,
                ]);

                $user->attachRole('teacher');

                Teacher::create([
                    'user_id' => $user->id,
                    'specialization_id' => $request->specialization_id,
                    'name' => [
                        'fr' => $request->name_teacherfr,
                        'ar' => $request->name_teacherar,
                        'en' => $request->name_teacherfr,
                    ],
                    'gender' => $request->gender,
                    'joining_date' => $request->joining_date,
                    'address' => $request->address,
                ]);
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        toastr()->success(trans('messages.success'));

        return redirect()->route('Teachers.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function show(Teacher $teacher)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function edit(Teacher $teacher)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function update(StoreTeacher $request, $id)
    {
        $request->validated();

        $schoolId = $this->currentSchoolId();

        $teacher = Teacher::query()
            ->forSchool($schoolId)
            ->with('user')
            ->findOrFail($id);

        try {
            DB::transaction(function () use ($teacher, $request, $schoolId) {
                $teacher->update([
                    'name' => [
                        'fr' => $request->name_teacherfr,
                        'ar' => $request->name_teacherar,
                        'en' => $request->name_teacherfr,
                    ],
                    'gender' => $request->gender,
                    'joining_date' => $request->joining_date,
                    'address' => $request->address,
                ]);

                if ($teacher->user) {
                    $teacher->user->update([
                        'name' => [
                            'fr' => $request->name_teacherfr,
                            'ar' => $request->name_teacherar,
                            'en' => $request->name_teacherfr,
                        ],
                        'email' => $request->email,
                        'school_id' => $schoolId,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        toastr()->success(trans('messages.Update'));

        return redirect()->route('Teachers.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function destroy(Teacher $teacher, $id)
    {
        $schoolId = $this->currentSchoolId();

        $teacher = Teacher::query()
            ->forSchool($schoolId)
            ->with(['user', 'sections'])
            ->findOrFail($id);

        if ($teacher->sections()->exists()) {
            toastr()->error('هذا المعلم ينتمي لقسم لا يمكن حذفه');
            return redirect()->route('Teachers.index');
        }

        DB::transaction(function () use ($teacher) {
            $teacher->delete();

            if ($teacher->user) {
                $teacher->user->delete();
            }
        });

        toastr()->error(trans('messages.delete'));

        return redirect()->route('Teachers.index');
    }
}
