<?php

namespace App\Http\Controllers\Inscription;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudent;
use App\Imports\StudentsImport;
use App\Models\Inscription\StudentInfo;
use App\Models\School\School;
use App\Models\School\Section;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class StudentController extends Controller
{
    public function __construct(private StudentEnrollmentService $enrollmentService)
    {
    }

    public function index()
    {
        $schoolId = $this->currentSchoolId();

        $data['School'] = School::query()
            ->when($schoolId, fn ($query) => $query->whereKey($schoolId))
            ->with('schoolgrades')
            ->orderBy('name_school')
            ->get();

        $data['StudentInfo'] = StudentInfo::query()
            ->forSchool($schoolId)
            ->with(['user', 'parent.user', 'section.classroom.school'])
            ->orderByDesc('created_at')
            ->get();

        $data['notify'] = $this->notifications();

        return view('admin.studentInfo', $data);
    }

    public function create()
    {
        $schoolId = $this->currentSchoolId();

        $data['School'] = School::query()
            ->when($schoolId, fn ($query) => $query->whereKey($schoolId))
            ->with('schoolgrades')
            ->orderBy('name_school')
            ->get();

        $data['notify'] = $this->notifications();

        return view('admin.addStudentParent', $data);
    }

    public function store(StoreStudent $request)
    {
        $request->validated();

        $schoolId = $this->currentSchoolId();

        $section = Section::query()
            ->forSchool($schoolId)
            ->findOrFail($request->section_id);

        $studentPayload = [
            'first_name' => [
                'fr' => $request->prenomfr,
                'ar' => $request->prenomar,
                'en' => $request->prenomfr,
            ],
            'last_name' => [
                'fr' => $request->nomfr,
                'ar' => $request->nomar,
                'en' => $request->nomfr,
            ],
            'email' => $request->email,
            'gender' => (int) $request->gender,
            'phone' => $request->numtelephone,
            'birth_date' => $request->datenaissance,
            'birth_place' => $request->lieunaissance,
            'wilaya' => $request->wilaya,
            'dayra' => $request->dayra,
            'baladia' => $request->baladia,
        ];

        $guardianPayload = [
            'first_name' => [
                'fr' => $request->prenomfrwali,
                'ar' => $request->prenomarwali,
                'en' => $request->prenomfrwali,
            ],
            'last_name' => [
                'fr' => $request->nomfrwali,
                'ar' => $request->nomarwali,
                'en' => $request->nomfrwali,
            ],
            'relation' => $request->relationetudiant,
            'address' => $request->adressewali,
            'wilaya' => $request->wilayawali,
            'dayra' => $request->dayrawali,
            'baladia' => $request->baladiawali,
            'phone' => $request->numtelephonewali,
            'email' => $request->emailwali,
        ];

        try {
            $this->enrollmentService->createStudent($studentPayload, $guardianPayload, $section);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()])->withInput();
        }

        toastr()->success(trans('messages.success'));

        return redirect()->route('Students.create');
    }

    public function update(StoreStudent $request, $id)
    {
        $request->validated();

        $schoolId = $this->currentSchoolId();

        $student = StudentInfo::query()
            ->forSchool($schoolId)
            ->with(['user', 'parent.user'])
            ->findOrFail($id);

        $section = Section::query()
            ->forSchool($schoolId)
            ->findOrFail($request->section_id);

        try {
            DB::transaction(function () use ($student, $request, $section) {
                $student->update([
                    'section_id' => $section->id,
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
                    'gender' => (int) $request->gender,
                    'numtelephone' => $request->numtelephone,
                    'datenaissance' => $request->datenaissance,
                    'lieunaissance' => $request->lieunaissance,
                    'wilaya' => $request->wilaya,
                    'dayra' => $request->dayra,
                    'baladia' => $request->baladia,
                ]);

                if ($student->user) {
                    $student->user->update([
                        'name' => [
                            'fr' => $request->prenomfr,
                            'ar' => $request->prenomar,
                            'en' => $request->prenomfr,
                        ],
                        'email' => $request->email,
                        'school_id' => $section->school_id,
                    ]);
                }

                if ($student->parent) {
                    $student->parent->update([
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
                        'wilayawali' => $request->wilayawali,
                        'dayrawali' => $request->dayrawali,
                        'baladiawali' => $request->baladiawali,
                        'numtelephonewali' => $request->numtelephonewali,
                    ]);

                    if ($student->parent->user) {
                        $student->parent->user->update([
                            'name' => [
                                'fr' => $request->prenomfrwali,
                                'ar' => $request->prenomarwali,
                                'en' => $request->prenomfrwali,
                            ],
                            'email' => $request->emailwali,
                            'school_id' => $section->school_id,
                        ]);
                    }
                }
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()])->withInput();
        }

        toastr()->success(trans('messages.Update'));

        return redirect()->route('Students.index');
    }

    public function destroy($id)
    {
        $schoolId = $this->currentSchoolId();

        $student = StudentInfo::query()
            ->forSchool($schoolId)
            ->with(['user', 'parent.students', 'parent.user'])
            ->findOrFail($id);

        DB::transaction(function () use ($student) {
            $guardian = $student->parent;
            $studentUser = $student->user;

            $student->forceDelete();

            if ($studentUser) {
                $studentUser->delete();
            }

            if ($guardian) {
                $remainingChildren = $guardian->students()->whereKeyNot($student->id)->count();

                if ($remainingChildren === 0) {
                    if ($guardian->user) {
                        $guardian->user->delete();
                    }
                    $guardian->delete();
                }
            }

            $notifiableIds = array_filter([
                $studentUser?->id,
                $guardian?->user?->id,
            ]);

            if (!empty($notifiableIds)) {
                DB::table('notifications')
                    ->whereIn('notifiable_id', $notifiableIds)
                    ->delete();
            }
        });

        toastr()->error(trans('messages.delete'));

        return redirect()->route('Students.index');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $schoolId = $this->currentSchoolId();

        try {
            Excel::import(new StudentsImport($this->enrollmentService, $schoolId), $request->file('file'));
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        toastr()->success('تم استيراد التلاميذ بنجاح');

        return redirect()->route('Students.index');
    }
}
