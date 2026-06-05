<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('studentinfos', function (Blueprint $table) {
            // الحقول القادمة من ملفات الوزارة (Eleve.xls)
            $table->string('national_id', 20)->nullable()->after('id'); // رقم التعريف
            $table->string('registration_number', 30)->nullable()->after('national_id'); // رقم القيد
            $table->date('enrolled_at')->nullable()->after('datenaissance'); // تاريخ التسجيل
            $table->string('schooling_system', 30)->nullable(); // نظام التمدرس
            $table->string('birth_certificate_type', 60)->nullable(); // عقد الميلاد
            $table->string('birth_record_year', 8)->nullable(); // سنة التسجيل في سجل الولادات
            $table->string('birth_certificate_number', 30)->nullable(); // رقم عقد الميلاد
            $table->boolean('born_by_judgment')->default(false); // مولود بحكم

            $table->unique('national_id', 'uq_studentinfos_national_id');
        });
    }

    public function down(): void
    {
        Schema::table('studentinfos', function (Blueprint $table) {
            $table->dropUnique('uq_studentinfos_national_id');
            $table->dropColumn([
                'national_id',
                'registration_number',
                'enrolled_at',
                'schooling_system',
                'birth_certificate_type',
                'birth_record_year',
                'birth_certificate_number',
                'born_by_judgment',
            ]);
        });
    }
};
