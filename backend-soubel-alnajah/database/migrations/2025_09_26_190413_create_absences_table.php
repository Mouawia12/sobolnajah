<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbsencesTable extends Migration
{
    public function up()
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id();

            // ربط الغياب بالطالب
            $table->unsignedInteger('student_id');
            $table->foreign('student_id')->references('id')->on('studentinfos')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->date('date'); // يوم الغياب

            // تخزين حالة كل ساعة (صباح + مساء)
            $table->boolean('hour_1')->default(true);
            $table->boolean('hour_2')->default(true);
            $table->boolean('hour_3')->default(true);
            $table->boolean('hour_4')->default(true);
            $table->boolean('hour_5')->default(true);
            $table->boolean('hour_6')->default(true);
            $table->boolean('hour_7')->default(true);
            $table->boolean('hour_8')->default(true);
            $table->boolean('hour_9')->default(true);
            $table->boolean('hour_10')->default(true);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('absences');
    }
}
