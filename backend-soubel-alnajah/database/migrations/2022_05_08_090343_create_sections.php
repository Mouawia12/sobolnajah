<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSections extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->increments('id', true);

			$table->integer('school_id')->unsigned();
            $table->foreign('school_id')->references('id')->on('schools')
						->onDelete('cascade');

            $table->integer('grade_id')->unsigned();
            $table->foreign('grade_id')->references('id')->on('schoolgrades')
						->onDelete('cascade');

            $table->integer('classroom_id')->unsigned();
            $table->foreign('classroom_id')->references('id')->on('classrooms')
						->onDelete('cascade');

			$table->string('name_section');
            $table->integer('Status');
			$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sections');
    }
}
