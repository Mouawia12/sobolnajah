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
        ]);

        // الولي الموجود يُطابَق بالهاتف أو البريد من نموذج تسجيل (قد يكون عاماً)،
        // فلا نغيّر بريد الدخول ولا ننقله لمؤسسة أخرى: تغيير البريد ثم «نسيت كلمة
        // المرور» كان يسمح بالاستيلاء على حساب الولي بمجرد معرفة رقم هاتفه.
        if (!$user->school_id) {
            $user->school_id = $schoolId;
        }

        $user->save();

        if (!$user->hasRole('guardian')) {
            $user->attachRole('guardian');
        }
    }
}
