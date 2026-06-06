<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * يجمع دفعات الإخوة المسجلة معاً (دفعة عائلية) تحت معرف واحد
     * حتى تظهر كلها في وصل مطبوع واحد باسم ولي الأمر.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('family_group', 36)->nullable()->after('notes')->index();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['family_group']);
            $table->dropColumn('family_group');
        });
    }
};
