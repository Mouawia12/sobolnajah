<?php

namespace App\Policies;

use App\Models\AgendaScolaire\NoteStudent;
use App\Models\User;

class NoteStudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, NoteStudent $noteStudent): bool
    {
        return $this->canAccessNote($user, $noteStudent);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, NoteStudent $noteStudent): bool
    {
        return $this->canAccessNote($user, $noteStudent);
    }

    public function delete(User $user, NoteStudent $noteStudent): bool
    {
        return $this->canAccessNote($user, $noteStudent);
    }

    private function canAccessNote(User $user, NoteStudent $noteStudent): bool
    {
        if (!$user->hasRole('admin')) {
            return false;
        }

        if (!$user->school_id) {
            return true;
        }

        $studentSchoolId = $noteStudent->student?->section?->school_id;
        if (!$studentSchoolId) {
            return false;
        }

        return (int) $studentSchoolId === (int) $user->school_id;
    }
}
