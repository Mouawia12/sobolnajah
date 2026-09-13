<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('school_id');
            $table->foreign('school_id')->references('id')->on('schools')
                ->onUpdate('cascade')->onDelete('cascade');

            // الأستاذ المنشئ (قد يكون فارغاً إن أنشأه المسؤول)
            $table->unsignedInteger('teacher_id')->nullable();
            $table->foreign('teacher_id')->references('id')->on('teachers')
                ->onUpdate('cascade')->onDelete('set null');

            $table->unsignedInteger('section_id');
            $table->foreign('section_id')->references('id')->on('sections')
                ->onUpdate('cascade')->onDelete('cascade');

            // المادة (تخصص الأستاذ)
            $table->unsignedInteger('specialization_id')->nullable();
            $table->foreign('specialization_id')->references('id')->on('specializations')
                ->onUpdate('cascade')->onDelete('set null');

            $table->string('title');
            $table->string('type')->default('devoir'); // devoir = فرض، exam = امتحان
            $table->unsignedTinyInteger('term')->nullable(); // الفصل 1/2/3 (اختياري)
            $table->decimal('max_mark', 5, 2)->default(20);
            $table->decimal('coefficient', 4, 2)->default(1);
            $table->date('date')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->index(['school_id', 'section_id']);
            $table->index(['section_id', 'specialization_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
