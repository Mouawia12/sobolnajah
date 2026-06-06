<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * تحويل أعمدة الساعات من boolean إلى tinyint لدعم 3 حالات:
     * 0 = غائب، 1 = حاضر (الافتراضي)، 2 = متأخر
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return; // sqlite (الاختبارات) يخزن الأعداد الصحيحة أصلاً
        }

        for ($i = 1; $i <= 9; $i++) {
            DB::statement("ALTER TABLE absences MODIFY hour_{$i} TINYINT UNSIGNED NOT NULL DEFAULT 1");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        for ($i = 1; $i <= 9; $i++) {
            DB::statement("ALTER TABLE absences MODIFY hour_{$i} TINYINT(1) NOT NULL DEFAULT 1");
        }
    }
};
