<?php

namespace App\Actions\Inscription;

use App\Models\Inscription\MyParent;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

class UpdateGuardianAccountAction
{
    public function __construct(private BuildLocalizedNameAction $buildLocalizedNameAction)
    {
    }

    public function execute(MyParent $guardian, array $guardianData, int $schoolId): void
    {
        $user = $guardian->user;

        if (!$user) {
            throw new ModelNotFoundException('Guardian user account not found.');
        }

        $user->fill([
            'name' => $this->buildLocalizedNameAction->execute(
                Arr::get($guardianData, 'first_name.fr'),
                Arr::get($guardianData, 'first_name.ar'),
                Arr::get($guardianData, 'first_name.en'),
                true
            ),
            'email' => Arr::get($guardianData, 'email') ?: $user->email,
            'school_id' => $schoolId,
        ]);

        $user->save();

        if (!$user->hasRole('guardian')) {
            $user->attachRole('guardian');
        }
    }
}
