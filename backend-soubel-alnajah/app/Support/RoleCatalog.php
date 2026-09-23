<?php

namespace App\Support;

use App\Models\Role;

/**
 * مصدر موحّد للأدوار الأساسية في النظام.
 *
 * هذه الأدوار «أساسية»: تُنشأ تلقائياً، ثابتة، ولا يمكن حذفها من صفحة الأدوار.
 * أي مستخدم يُسند إليه دور واحد فقط ويخضع لصلاحيات ذلك الدور.
 */
class RoleCatalog
{
    /** الأدوار الأساسية (المعرّف => الاسم المعروض بالعربية). */
    public const CORE_ROLES = [
        'admin' => 'مدير',
        'supervisor' => 'ناظر',
        'teacher' => 'أستاذ',
        'accountant' => 'محاسب',
        'employee' => 'موظف',
        'guardian' => 'ولي أمر',
        'student' => 'تلميذ',
    ];

    /** أسماء الأدوار الأساسية غير القابلة للحذف. */
    public static function coreRoleNames(): array
    {
        return array_keys(self::CORE_ROLES);
    }

    /** هل الدور أساسي (ثابت لا يُحذف)؟ */
    public static function isCore(string $name): bool
    {
        return array_key_exists($name, self::CORE_ROLES);
    }

    /** الاسم المعروض لدور أساسي، أو المعرّف نفسه إن لم يكن أساسياً. */
    public static function label(string $name): string
    {
        return self::CORE_ROLES[$name] ?? $name;
    }

    /**
     * يضمن وجود كل الأدوار الأساسية في قاعدة البيانات.
     * يُستدعى عند فتح صفحة الأدوار حتى تبقى الأدوار الأساسية موجودة دائماً.
     */
    public static function ensureCoreRolesExist(): void
    {
        foreach (self::CORE_ROLES as $name => $label) {
            Role::firstOrCreate(
                ['name' => $name],
                ['display_name' => $label, 'description' => $label]
            );
        }
    }
}
