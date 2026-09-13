<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_marks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('assessment_id');
            $table->foreign('assessment_id')->references('id')->on('assessments')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedInteger('student_id');
            $table->foreign('student_id')->references('id')->on('studentinfos')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->decimal('mark', 5, 2)->nullable(); // فارغ = لم تُدخل / غائب
            $table->boolean('is_absent')->default(false);
            $table->string('note')->nullable();

            $table->timestamps();

            $table->unique(['assessment_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_marks');
    }
};
