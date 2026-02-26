<?php

namespace App\Services;

use App\Models\Inscription\StudentInfo;
use App\Models\School\Section;
use Illuminate\Support\Facades\Cache;

class HomeDashboardCacheService
{
    public function forgetForStudent(StudentInfo $student): void
    {
        $schoolIds = [];

        $currentSectionId = $student->section_id ? (int) $student->section_id : null;
        $originalSectionId = $student->getOriginal('section_id')
            ? (int) $student->getOriginal('section_id')
            : null;

        if ($currentSectionId) {
            $schoolIds[] = $this->resolveSchoolIdFromSection($currentSectionId);
        }

        if ($originalSectionId) {
            $schoolIds[] = $this->resolveSchoolIdFromSection($originalSectionId);
        }

        $this->forgetForSchools(array_values(array_filter(array_unique($schoolIds))));
    }

    public function forgetForSchool(?int $schoolId): void
    {
        $this->forgetForSchools($schoolId ? [$schoolId] : []);
    }

    /**
     * @param array<int> $schoolIds
     */
    public function forgetForSchools(array $schoolIds): void
    {
        $cacheSchoolKeys = array_map(static fn (int $id) => (string) $id, $schoolIds);
        $cacheSchoolKeys[] = 'all';
        $cacheSchoolKeys = array_values(array_unique($cacheSchoolKeys));

        $locales = array_keys((array) config('laravellocalization.supportedLocales', []));
        if ($locales === []) {
            $locales = [app()->getLocale()];
        }

        foreach ($cacheSchoolKeys as $cacheSchoolKey) {
            foreach ($locales as $locale) {
                Cache::forget("home:school:{$cacheSchoolKey}:locale:{$locale}:students-by-grade");
                Cache::forget("home:school:{$cacheSchoolKey}:locale:{$locale}:students-monthly");
            }
        }
    }

    private function resolveSchoolIdFromSection(int $sectionId): ?int
    {
        return Section::query()
            ->where('id', $sectionId)
            ->value('school_id');
    }
}
