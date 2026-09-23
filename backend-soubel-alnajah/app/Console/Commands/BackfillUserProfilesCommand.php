<?php

namespace App\Console\Commands;

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

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $teachers = 0;
        $guardians = 0;

        // المعلّمون بلا سجل معلّم.
        User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))
            ->chunkById(100, function ($users) use (&$teachers, $dry) {
                foreach ($users as $user) {
                    if (Teacher::query()->where('user_id', $user->id)->exists()) {
                        continue;
                    }
                    $teachers++;
                    if (!$dry) {
                        Teacher::create([
                            'user_id' => $user->id,
                            'specialization_id' => null,
                            'name' => (string) $user->name,
                            'gender' => null,
                            'joining_date' => null,
                            'address' => null,
                        ]);
                    }
                }
            });

        // الأولياء بلا سجل ولي.
        User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'guardian'))
            ->chunkById(100, function ($users) use (&$guardians, $dry) {
                foreach ($users as $user) {
                    if (MyParent::query()->where('user_id', $user->id)->exists()) {
                        continue;
                    }
                    $guardians++;
                    if (!$dry) {
                        MyParent::create([
                            'user_id' => $user->id,
                            'prenomwali' => ['ar' => (string) $user->name, 'fr' => (string) $user->name, 'en' => (string) $user->name],
                            'nomwali' => ['ar' => '', 'fr' => '', 'en' => ''],
                            'relationetudiant' => '',
                            'adressewali' => '',
                            'wilayawali' => '',
                            'dayrawali' => '',
                            'baladiawali' => '',
                            'numtelephonewali' => 0,
                        ]);
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
