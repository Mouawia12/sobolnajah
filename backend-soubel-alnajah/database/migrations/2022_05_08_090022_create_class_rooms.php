<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassRooms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->increments('id');

			$table->integer('school_id')->unsigned();
            $table->foreign('school_id')->references('id')->on('schools')
						->onDelete('cascade');

            $table->integer('grade_id')->unsigned();
            $table->foreign('grade_id')->references('id')->on('schoolgrades')
						->onDelete('cascade');

			$table->string('name_class');
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
        Schema::dropIfExists('classrooms');
    }
}
