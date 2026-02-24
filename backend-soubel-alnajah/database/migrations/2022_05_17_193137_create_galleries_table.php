<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGalleriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->increments('id',true);

			$table->integer('publication_id')->unsigned();
            $table->foreign('publication_id')->references('id')->on('publications')
						->onDelete('cascade');

            $table->integer('agenda_id')->unsigned();
            $table->foreign('agenda_id')->references('id')->on('agenda')
						->onDelete('cascade');

            $table->integer('grade_id')->unsigned();
            $table->foreign('grade_id')->references('id')->on('grades')
                        ->onDelete('cascade');
                        
            $table->string('img_url');
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
        Schema::dropIfExists('galleries');
    }
}
