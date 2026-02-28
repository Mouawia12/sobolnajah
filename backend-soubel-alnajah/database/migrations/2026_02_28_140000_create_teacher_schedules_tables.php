<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teacher_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('school_id');
            $table->unsignedInteger('teacher_id');
            $table->string('academic_year', 20);
            $table->string('title', 160)->nullable();
            $table->string('branch_name', 120)->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->enum('visibility', ['public', 'authenticated'])->default('authenticated');
            $table->timestamp('approved_at')->nullable();
            $table->string('signature_text', 160)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'teacher_id', 'academic_year'], 'uq_teacher_schedules_school_teacher_year');
            $table->index(['school_id', 'status', 'visibility']);
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('teacher_schedule_slots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_schedule_id');
            $table->unsignedTinyInteger('slot_index');
            $table->string('label', 40)->nullable();
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->timestamps();

            $table->unique(['teacher_schedule_id', 'slot_index'], 'uq_teacher_schedule_slot_index');
            $table->foreign('teacher_schedule_id')->references('id')->on('teacher_schedules')->onDelete('cascade');
        });

        Schema::create('teacher_schedule_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_schedule_id');
            $table->unsignedTinyInteger('day_of_week');
            $table->unsignedTinyInteger('slot_index');
            $table->string('subject_name', 140)->nullable();
            $table->string('class_name', 100)->nullable();
            $table->string('room_name', 80)->nullable();
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->unique(['teacher_schedule_id', 'day_of_week', 'slot_index'], 'uq_teacher_schedule_entries_cell');
            $table->index(['teacher_schedule_id', 'day_of_week']);
            $table->foreign('teacher_schedule_id')->references('id')->on('teacher_schedules')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_schedule_entries');
        Schema::dropIfExists('teacher_schedule_slots');
        Schema::dropIfExists('teacher_schedules');
    }
};
