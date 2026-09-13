<?php

namespace App\Actions\Notification;

use App\Models\Academic\Assessment;
use App\Models\Inscription\StudentInfo;
use App\Models\User;
use App\Notifications\MarksEnteredNotification;
use Illuminate\Support\Facades\DB;

class NotifyMarksEnteredAction
{
    /**
     * يُشعر كل تلميذ تم إدخال نقطته + ولي أمره بأن نقاط التقييم أصبحت متاحة،
     * مع منع التكرار عند إعادة الحفظ.
     *
     * @param array<int> $studentIds
     */
    public function execute(Assessment $assessment, array $studentIds): void
    {
        if (empty($studentIds)) {
            return;
        }

        $subjectName = (string) optional($assessment->specialization)->name;

        $students = StudentInfo::query()
            ->with(['user:id', 'parent:id,user_id', 'parent.user:id'])
            ->whereIn('id', $studentIds)
            ->get();

        foreach ($students as $student) {
            $recipients = collect([$student->user, optional($student->parent)->user])
                ->filter()
                ->unique('id');

            foreach ($recipients as $user) {
                if ($this->alreadyNotified($user, $assessment->id, $student->id)) {
                    continue;
                }

                $user->notify(new MarksEnteredNotification($student, $assessment, $subjectName));
            }
        }
    }

    private function alreadyNotified(User $user, int $assessmentId, int $studentId): bool
    {
        return DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', $user->getMorphClass())
            ->where('data', 'like', '%"assessment_id":' . $assessmentId . '%')
            ->where('data', 'like', '%"student_id":' . $studentId . '%')
            ->exists();
    }
}
