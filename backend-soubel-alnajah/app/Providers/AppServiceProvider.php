<?php

namespace App\Providers;

use App\Models\HR\Employee;
use App\Models\HR\StaffAttendance;
use App\Models\Inscription\StudentInfo;
use App\Models\Inscription\Teacher;
use App\Models\School\School;
use App\Services\HomeDashboardCacheService;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // الثيم الإداري مبني على Bootstrap — قالب الترقيم الافتراضي (Tailwind)
        // يعرض أسهم SVG عملاقة بدون تنسيق.
        Paginator::useBootstrapFive();

        // أسماء مختصرة ثابتة لعلاقة حضور الموظفين متعددة الأشكال (staffable).
        // morphMap غير ملزِمة: تضيف الاختصارات دون فرضها على باقي العلاقات (كالإشعارات).
        Relation::morphMap([
            StaffAttendance::TYPE_TEACHER => Teacher::class,
            StaffAttendance::TYPE_EMPLOYEE => Employee::class,
        ]);

        // Compatibility for older Blade compilers that don't provide @selected.
        Blade::directive('selected', function ($expression) {
            return "<?php echo ($expression) ? 'selected' : ''; ?>";
        });

        StudentInfo::saved(function (StudentInfo $student): void {
            app(HomeDashboardCacheService::class)->forgetForStudent($student);
        });

        StudentInfo::deleted(function (StudentInfo $student): void {
            app(HomeDashboardCacheService::class)->forgetForStudent($student);
        });

        StudentInfo::restored(function (StudentInfo $student): void {
            app(HomeDashboardCacheService::class)->forgetForStudent($student);
        });

        StudentInfo::forceDeleted(function (StudentInfo $student): void {
            app(HomeDashboardCacheService::class)->forgetForStudent($student);
        });

        // قائمة الفروع مخزّنة مؤقتاً (Controller::branchOptions)، نبطلها عند أي تغيير على الفروع.
        $forgetBranches = static function (): void {
            Cache::forget('lookup:branches');
        };
        School::saved($forgetBranches);
        School::deleted($forgetBranches);
    }
}
