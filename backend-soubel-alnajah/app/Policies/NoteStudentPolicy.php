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
        if ($user->hasRole('admin')) {
            if (!$user->school_id) {
                return true;
            }

            $studentSchoolId = $noteStudent->student?->section?->school_id;
            if (!$studentSchoolId) {
                return false;
            }

            return (int) $studentSchoolId === (int) $user->school_id;
        }

        $student = $noteStudent->student;
        if (!$student) {
            return false;
        }

        if ($user->hasRole('student')) {
            return (int) $student->user_id === (int) $user->id;
        }

        if ($user->hasRole('guardian')) {
            $guardianId = $user->parentProfile?->id;

            return $guardianId !== null && (int) $student->parent_id === (int) $guardianId;
        }

        return false;
    }
}
