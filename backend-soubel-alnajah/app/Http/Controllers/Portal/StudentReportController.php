<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Inscription\MyParent;
use App\Models\Inscription\StudentInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StudentReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'force.password.change']);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        abort_unless($user->hasRole('student') || $user->hasRole('guardian'), 403);

        $students = $user->hasRole('student')
            ? $this->studentRows($user->id)
            : $this->guardianRows($user->id);

        return view('front-end.reports', [
            'students' => $students,
            'reportColumns' => $this->reportColumns(),
        ]);
    }

    private function studentRows(int $userId): Collection
    {
        $student = StudentInfo::query()
            ->where('user_id', $userId)
            ->with(['user', 'section.classroom.schoolgrade', 'noteStudent'])
            ->first();

        return $student ? collect([$student]) : collect();
    }

    private function guardianRows(int $userId): Collection
    {
        $guardian = MyParent::query()
            ->where('user_id', $userId)
            ->with([
                'students.user',
                'students.section.classroom.schoolgrade',
                'students.noteStudent',
            ])
            ->first();

        return $guardian?->students ?? collect();
    }

    private function reportColumns(): array
    {
        return [
            ['key' => 'urlfile1', 'label' => 'الفصل الأول'],
            ['key' => 'urlfile2', 'label' => 'الفصل الثاني'],
            ['key' => 'urlfile3', 'label' => 'الفصل الثالث'],
        ];
    }
}
