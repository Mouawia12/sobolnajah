<?php

namespace App\Actions\Function;

use App\Models\AgendaScolaire\Agenda;
use App\Models\AgendaScolaire\Gallery;
use App\Models\AgendaScolaire\Grade;
use App\Models\AgendaScolaire\Publication;
use App\Models\User;

class BuildAgendaPageDataAction
{
    public function execute(?User $user): array
    {
        $data = [
            'Publications' => Publication::query()->latest('created_at')->get(),
            'Grade' => Grade::query()->get(),
            'Agenda' => Agenda::query()->get(),
            'Gallery' => Gallery::query()->get(),
        ];

        $data['view'] = ($user && $user->hasRole('admin'))
            ? 'admin.publications'
            : 'front-end.agenda';

        return $data;
    }
}
