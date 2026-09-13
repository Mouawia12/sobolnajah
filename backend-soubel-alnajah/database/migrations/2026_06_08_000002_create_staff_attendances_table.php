<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('school_id');
            $table->foreign('school_id')->references('id')->on('schools')
                ->onUpdate('cascade')->onDelete('cascade');

            // علاقة متعددة الأشكال: أستاذ (teachers) أو موظف (employees)
            $table->string('staffable_type');
            $table->unsignedBigInteger('staffable_id');

            $table->date('date');

            // 0 = غائب، 1 = حاضر، 2 = متأخر
            $table->unsignedTinyInteger('status')->default(1);
            $table->time('check_in')->nullable();
            $table->string('notes')->nullable();

            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->foreign('recorded_by')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('set null');

            $table->timestamps();

            $table->unique(['staffable_type', 'staffable_id', 'date'], 'staff_attendance_unique_day');
            $table->index(['school_id', 'date']);
            $table->index(['staffable_type', 'staffable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendances');
    }
};
