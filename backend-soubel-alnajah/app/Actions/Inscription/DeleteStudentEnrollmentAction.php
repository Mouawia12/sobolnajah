<?php

namespace App\Actions\Inscription;

use App\Models\Inscription\StudentInfo;
use Illuminate\Support\Facades\DB;

class DeleteStudentEnrollmentAction
{
    public function execute(StudentInfo $student): void
    {
        DB::transaction(function () use ($student) {
            $guardian = $student->parent;
            $studentUser = $student->user;
            $guardianUser = $guardian?->user;

            $student->forceDelete();

            if ($studentUser) {
                $studentUser->delete();
            }

            if ($guardian) {
                $remainingChildren = $guardian->students()->whereKeyNot($student->id)->count();

                if ($remainingChildren === 0) {
                    if ($guardianUser) {
                        $guardianUser->delete();
                    }

                    $guardian->delete();
                }
            }

            $notifiableIds = array_filter([
                $studentUser?->id,
                $guardianUser?->id,
            ]);

            if (!empty($notifiableIds)) {
                DB::table('notifications')
                    ->whereIn('notifiable_id', $notifiableIds)
                    ->delete();
            }
        });
    }
}
