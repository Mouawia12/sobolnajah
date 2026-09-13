<?php

namespace App\Notifications;

use App\Models\Academic\Assessment;
use App\Models\Inscription\StudentInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MarksEnteredNotification extends Notification
{
    use Queueable;

    public function __construct(
        private StudentInfo $student,
        private Assessment $assessment,
        private ?string $subjectName = null
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'kind' => 'marks_entered',
            'student_id' => $this->student->id,
            'assessment_id' => $this->assessment->id,
            'subject' => $this->subjectName,
            'title' => mb_substr((string) $this->assessment->title, 0, 60),
        ];
    }
}
