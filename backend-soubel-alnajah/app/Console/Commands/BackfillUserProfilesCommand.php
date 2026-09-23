<?php

namespace App\Console\Commands;

use App\Actions\User\CreatePortalUserAction;
use App\Models\Inscription\MyParent;
use App\Models\Inscription\Teacher;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * يُنشئ الملفات الناقصة للمستخدمين الحاليين حسب أدوارهم، حتى لا يحدث
 * خطأ عند تسجيل الدخول بسبب بيانات ناقصة (مثال: أستاذ بلا سجل معلّم).
 */
class BackfillUserProfilesCommand extends Command
{
    protected $signature = 'users:backfill-profiles {--dry-run : عرض ما سيُنشأ دون كتابة}';

    protected $description = 'إنشاء ملفات المعلّمين/الأولياء الناقصة للمستخدمين الحاليين حسب أدوارهم';

    public function handle(CreatePortalUserAction $createPortalUser): int
    {
        $dry = (bool) $this->option('dry-run');
        $teachers = 0;
        $guardians = 0;

        // المعلّمون بلا سجل معلّم.
        User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))
            ->chunkById(100, function ($users) use (&$teachers, $dry, $createPortalUser) {
                foreach ($users as $user) {
                    if (Teacher::query()->where('user_id', $user->id)->exists()) {
                        continue;
                    }
                    $teachers++;
                    if (!$dry) {
                        $createPortalUser->ensureProfileForRole($user, 'teacher');
                    }
                }
            });

        // الأولياء بلا سجل ولي.
        User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'guardian'))
            ->chunkById(100, function ($users) use (&$guardians, $dry, $createPortalUser) {
                foreach ($users as $user) {
                    if (MyParent::query()->where('user_id', $user->id)->exists()) {
                        continue;
                    }
                    $guardians++;
                    if (!$dry) {
                        $createPortalUser->ensureProfileForRole($user, 'guardian');
                    }
                }
            });

        $prefix = $dry ? '[تجريبي] ' : '';
        $this->info($prefix . "معلّمون بلا ملف: {$teachers}");
        $this->info($prefix . "أولياء بلا ملف: {$guardians}");
        $this->info('تم.');

        return self::SUCCESS;
    }
}
