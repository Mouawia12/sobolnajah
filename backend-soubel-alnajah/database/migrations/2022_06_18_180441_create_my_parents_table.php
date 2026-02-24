<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMyParentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('my_parents', function (Blueprint $table) {
            $table->increments('id', true);

            $table->string('prenomwali'); 
            $table->string('nomwali'); 
            $table->string('relationetudiant'); 
            $table->string('adressewali'); 
            $table->string('wilayawali'); 
            $table->string('dayrawali'); 
            $table->string('baladiawali');

            $table->bigInteger('numtelephonewali'); 
            $table->unsignedBigInteger('user_id');
        

            $table->foreign('user_id')->references('id')->on('users')
                  ->onUpdate('cascade')->onDelete('cascade');

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
        Schema::dropIfExists('my_parents');
    }
}
