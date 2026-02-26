<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('school_id');
            $table->unsignedInteger('section_id');
            $table->string('academic_year', 20);
            $table->string('title', 160)->nullable();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'section_id', 'academic_year'], 'uq_timetables_school_section_year');
            $table->index(['school_id', 'is_published', 'academic_year']);
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('timetable_id');
            $table->unsignedTinyInteger('day_of_week');
            $table->unsignedTinyInteger('period_index');
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->string('subject_name', 140);
            $table->unsignedInteger('teacher_id')->nullable();
            $table->string('room_name', 80)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique(['timetable_id', 'day_of_week', 'period_index'], 'uq_timetable_entries_slot');
            $table->index(['teacher_id', 'day_of_week']);
            $table->foreign('timetable_id')->references('id')->on('timetables')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('teachers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_entries');
        Schema::dropIfExists('timetables');
    }
};
