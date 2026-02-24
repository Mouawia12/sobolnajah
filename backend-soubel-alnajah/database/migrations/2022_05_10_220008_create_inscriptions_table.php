<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inscriptions', function (Blueprint $table) {
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

            $table->string('inscriptionetat');           
            $table->string('nomecoleprecedente');           
            $table->string('dernieresection');           

            $table->float('moyensannuels');
            $table->bigInteger('numeronationaletudiant');

            $table->string('prenom');   
            $table->string('nom');      
            $table->string('email')->unique();   
            $table->integer('gender'); 

            $table->bigInteger('numtelephone');   

            $table->date('datenaissance'); 

            $table->string('lieunaissance');   
            $table->string('wilaya');   
            $table->string('dayra');   
            $table->string('baladia');   
            $table->string('adresseactuelle');   

            $table->integer('codepostal');

            $table->string('residenceactuelle');   
            $table->string('etatsante');   
            $table->string('identificationmaladie'); 
            $table->text('alfdlprsaldr'); 
            $table->string('autresnotes')->nullable();

            //information wali aamer
            $table->string('prenomwali'); 
            $table->string('nomwali'); 
            $table->string('relationetudiant'); 
            $table->string('adressewali'); 

            $table->bigInteger('numtelephonewali'); 
            $table->string('emailwali'); 

            
            $table->string('wilayawali'); 
            $table->string('dayrawali'); 
            $table->string('baladiawali');

            $table->string('statu');


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
        Schema::dropIfExists('inscriptions');
    }
}
