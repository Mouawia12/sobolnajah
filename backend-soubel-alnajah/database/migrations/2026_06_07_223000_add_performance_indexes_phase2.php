<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * فهارس أداء إضافية (المرحلة 2).
 *
 * ملاحظة مهمة: في MySQL/InnoDB كل قيد مفتاح أجنبي (foreign) يُنشئ فهرساً مفرداً
 * تلقائياً على عموده، كما أن مايجريشن cleanup_core_schema_integrity أضاف فهرساً
 * مركّباً واسعاً على sections يبدأ بـ school_id. لذلك معظم «الفهارس المفقودة» المتوقّعة
 * موجودة فعلاً، ولا نضيف هنا إلا ما هو ناقص ومُجدٍ حقاً بعد فحص الفهارس الفعلية.
 *
 * الفهرس الوحيد المُجدي والناقص:
 *   student_contracts(school_id, created_at)
 *     يخدم: تصدير/طباعة العقود حسب الفترة (forSchool + whereBetween('created_at'))
 *     وقائمة العقود (forSchool + orderByDesc('created_at')).
 *     الفهارس الموجودة على الجدول لا يبدأ أيٌّ منها بـ (school_id, created_at).
 */
return new class extends Migration {
    public function up(): void
    {
        // try/catch بدل information_schema حتى يعمل على MySQL وsqlite (الاختبارات) معاً،
        // ويتجاهل الخطأ إن كان الفهرس موجوداً مسبقاً.
        try {
            Schema::table('student_contracts', function (Blueprint $table) {
                $table->index(['school_id', 'created_at'], 'idx_contracts_school_created');
            });
        } catch (\Throwable $e) {
            // الفهرس موجود مسبقاً — نتجاهل.
        }
    }

    public function down(): void
    {
        try {
            Schema::table('student_contracts', function (Blueprint $table) {
                $table->dropIndex('idx_contracts_school_created');
            });
        } catch (\Throwable $e) {
            // الفهرس غير موجود — نتجاهل.
        }
    }
};
