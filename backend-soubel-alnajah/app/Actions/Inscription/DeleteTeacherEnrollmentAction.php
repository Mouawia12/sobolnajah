<?php

namespace App\Actions\Inscription;

use App\Models\Inscription\Teacher;
use Illuminate\Support\Facades\DB;

class DeleteTeacherEnrollmentAction
{
    public function execute(Teacher $teacher): void
    {
        DB::transaction(function () use ($teacher) {
            $teacher->delete();

            if ($teacher->user) {
                $teacher->user->delete();
            }
        });
    }
}
