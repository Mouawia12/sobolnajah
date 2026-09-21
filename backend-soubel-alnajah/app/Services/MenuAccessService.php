<?php

namespace App\Services;

use App\Models\RoleMenuSection;
use App\Models\User;
use App\Support\MenuCatalog;
use Illuminate\Support\Facades\Cache;

class MenuAccessService
{
    /**
     * مفاتيح أقسام السايدبار التي يراها المستخدم.
     * admin يرى كل الأقسام دائماً؛ باقي الأدوار حسب المُسنَد في role_menu_sections.
     *
     * @return array<int, string>
     */
    public function allowedSections(?User $user): array
    {
        if (!$user) {
            return [];
        }

        if ($user->hasRole('admin')) {
            return MenuCatalog::keys();
        }

        $roles = $user->roles->map(fn ($r) => ['id' => $r->id, 'name' => $r->name])
            ->sortBy('id')->values();
        if ($roles->isEmpty()) {
            return [];
        }

        $version = Cache::get('menu_sections_version', 1);
        $cacheKey = 'menu_sections:' . $version . ':' . $roles->pluck('id')->implode(',');

        return Cache::remember($cacheKey, 600, function () use ($roles) {
            $rowsByRole = RoleMenuSection::query()
                ->whereIn('role_id', $roles->pluck('id'))
                ->get(['role_id', 'section_key'])
                ->groupBy('role_id');

            $result = [];
            foreach ($roles as $role) {
                $rows = ($rowsByRole->get($role['id']) ?? collect())->pluck('section_key')->all();

                // لا صفوف => الدور غير مضبوط بعد => افتراضيات الدور المعروف.
                // وجود صفوف (ولو العلامة فقط) => نستعمل الضبط الصريح (يحترم الإخفاء الكامل).
                $sections = empty($rows)
                    ? \App\Support\MenuCatalog::defaultsForRole($role['name'])
                    : array_diff($rows, [\App\Support\MenuCatalog::CONFIGURED_MARKER]);

                $result = array_merge($result, $sections);
            }

            return array_values(array_unique($result));
        });
    }

    public function canSeeSection(?User $user, string $sectionKey): bool
    {
        return in_array($sectionKey, $this->allowedSections($user), true);
    }

    /**
     * تُستدعى بعد أي تعديل على الصلاحيات لإبطال الكاش.
     */
    public static function bustCache(): void
    {
        Cache::forever('menu_sections_version', now()->timestamp);
    }
}
