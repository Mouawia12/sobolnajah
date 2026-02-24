<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->increments('id', true);

            $table->unsignedInteger('student_id');
            $table->unsignedInteger('from_school');
            $table->unsignedInteger('from_grade');
            $table->unsignedInteger('from_Classroom');
            $table->unsignedInteger('from_section');
            $table->unsignedInteger('to_school');
            $table->unsignedInteger('to_grade');
            $table->unsignedInteger('to_Classroom');
            $table->unsignedInteger('to_section');

            $table->timestamps();
        });

        Schema::table('promotions', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('studentinfos')
            ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('from_school')->references('id')->on('schools')
            ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('from_grade')->references('id')->on('schoolgrades')
            ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('from_Classroom')->references('id')->on('classrooms')
            ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('from_section')->references('id')->on('sections')
            ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('to_school')->references('id')->on('schools')
            ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('to_grade')->references('id')->on('schoolgrades')
            ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('to_Classroom')->references('id')->on('classrooms')
            ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('to_section')->references('id')->on('sections')
            ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotions');
    }
}
