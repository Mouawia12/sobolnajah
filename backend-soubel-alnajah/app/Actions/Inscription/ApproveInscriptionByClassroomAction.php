<?php

namespace App\Actions\Inscription;

use App\Models\Inscription\Inscription;
use App\Models\School\Section;
use Illuminate\Validation\ValidationException;

class ApproveInscriptionByClassroomAction
{
    public function __construct(private ApproveInscriptionAction $approveInscriptionAction)
    {
    }

    public function execute(Inscription $inscription, ?int $schoolId): void
    {
        $section = Section::query()
            ->forSchool($schoolId)
            ->where('classroom_id', $inscription->classroom_id)
            ->first();

        if (!$section) {
            throw ValidationException::withMessages([
                'section' => 'No section is available for the selected classroom.',
            ]);
        }

        $this->approveInscriptionAction->execute($inscription, $section);
    }
}
