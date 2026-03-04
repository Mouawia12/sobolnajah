<?php

namespace App\Providers;

use App\Models\Inscription\StudentInfo;
use App\Services\HomeDashboardCacheService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

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
    }
}
