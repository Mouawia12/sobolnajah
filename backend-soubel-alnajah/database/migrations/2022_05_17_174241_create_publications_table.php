<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('publications', function (Blueprint $table) {
            $table->increments('id',true);

            $table->integer('school_id')->unsigned();
            $table->foreign('school_id')->references('id')->on('schools')
						->onDelete('cascade');

			$table->integer('grade_id')->unsigned();
            $table->foreign('grade_id')->references('id')->on('grades')
						->onDelete('cascade');

            $table->integer('agenda_id')->unsigned();
            $table->foreign('agenda_id')->references('id')->on('agenda')
						->onDelete('cascade');

			$table->string('title');
            $table->text('body');
            $table->integer('like');



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
        Schema::dropIfExists('publications');
    }
}
