<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolGrades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schoolgrades', function (Blueprint $table) {
            $table->increments('id', true);

			$table->integer('school_id')->unsigned();
            $table->foreign('school_id')->references('id')->on('schools')
						->onDelete('cascade');

			$table->string('name_grade');
			$table->string('notes');
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
        Schema::dropIfExists('schoolgrades');
    }
}
