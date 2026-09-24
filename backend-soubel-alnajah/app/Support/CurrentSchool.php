<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * محدّد الفرع (المدرسة) الحالي — مصدر واحد للعزل المركزي بين الفروع.
 *
 * القاعدة:
 * - لا مستخدم (طرفية/مهام/زوّار)            => null  (بلا تقييد)
 * - مدير فرع (user.school_id محدّد)          => فرعه  (تقييد صارم لا يتجاوزه)
 * - مدير عام (user.school_id = null)         => الفرع النشط في الجلسة إن وُجد، وإلا null (كل الفروع)
 *
 * يمكن تجاوز القيمة مؤقتاً عبر set()/withSchool() للعمليات الإدارية أو المهام الخلفية.
 */
class CurrentSchool
{
    private static ?int $override = null;
    private static bool $overridden = false;

    /** مفتاح الجلسة الذي يحفظ الفرع النشط للمدير العام. */
    public const SESSION_KEY = 'active_branch_id';

    /**
     * معرّف الفرع الفعّال، أو null = بلا تقييد (رؤية كل الفروع).
     */
    public static function id(): ?int
    {
        if (self::$overridden) {
            return self::$override;
        }

        if (!Auth::check()) {
            return null;
        }

        $user = Auth::user();

        // مدير فرع: مقيّد بفرعه دائماً.
        if ($user && $user->school_id) {
            return (int) $user->school_id;
        }

        // مدير عام: فرع نشط اختياري من الجلسة، وإلا كل الفروع.
        $active = self::sessionBranchId();

        return $active ?: null;
    }

    /** هل المستخدم الحالي مقيّد بفرع واحد (مدير فرع)؟ */
    public static function isLocked(): bool
    {
        return Auth::check() && (bool) Auth::user()?->school_id;
    }

    /**
     * هل المستخدم الحالي ولي أو تلميذ؟ وصولهما مبني على الملكية (أبناؤه/سجلّه)
     * لا على الفرع، فالولي قد يكون له أبناء في فرعين مختلفين.
     */
    public static function isFamilyUser(): bool
    {
        if (self::$overridden || !Auth::check()) {
            return false;
        }

        return (bool) Auth::user()?->hasRole(['guardian', 'student']);
    }

    /** تثبيت فرع مؤقتاً (للمهام/الطرفية/عمليات المدير العام عبر الفروع). */
    public static function set(?int $schoolId): void
    {
        self::$override = $schoolId;
        self::$overridden = true;
    }

    /** إلغاء التثبيت المؤقّت والعودة للسلوك الافتراضي. */
    public static function clear(): void
    {
        self::$override = null;
        self::$overridden = false;
    }

    /** تنفيذ دالة ضمن فرع محدّد (أو بلا تقييد إن null) ثم استعادة الحالة السابقة. */
    public static function withSchool(?int $schoolId, callable $callback)
    {
        $prevOverridden = self::$overridden;
        $prevOverride = self::$override;

        self::set($schoolId);

        try {
            return $callback();
        } finally {
            self::$overridden = $prevOverridden;
            self::$override = $prevOverride;
        }
    }

    /** تنفيذ دالة دون أي تقييد بفرع (رؤية كل الفروع). */
    public static function withoutRestriction(callable $callback)
    {
        return self::withSchool(null, $callback);
    }

    private static function sessionBranchId(): ?int
    {
        if (!app()->bound('session') || !app('session')->isStarted()) {
            return null;
        }

        $value = session(self::SESSION_KEY);

        return $value ? (int) $value : null;
    }
}
