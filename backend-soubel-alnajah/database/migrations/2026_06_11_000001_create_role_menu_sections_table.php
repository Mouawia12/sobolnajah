<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * صلاحيات ظهور أقسام السايدبار لكل دور: صف = (دور، قسم) يعني أن الدور يرى هذا القسم.
 * دور admin يرى كل الأقسام دائماً (صلاحية كاملة) ولا يحتاج صفوفاً هنا.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_menu_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->string('section_key', 60);
            $table->timestamps();

            $table->unique(['role_id', 'section_key'], 'uq_role_menu_section');
            $table->index('role_id');
        });

        // بذر افتراضي للأدوار الموجودة حفاظاً على السلوك الحالي.
        $defaults = [
            'accountant' => ['finance', 'accountant_portal', 'communication'],
            'teacher' => ['teacher_portal', 'assessments', 'communication'],
            'supervisor' => ['hr'],
        ];

        foreach ($defaults as $roleName => $sections) {
            $roleId = DB::table('roles')->where('name', $roleName)->value('id');
            if (!$roleId) {
                continue;
            }
            foreach ($sections as $key) {
                DB::table('role_menu_sections')->insertOrIgnore([
                    'role_id' => $roleId,
                    'section_key' => $key,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_menu_sections');
    }
};
