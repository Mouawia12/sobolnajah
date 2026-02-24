<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exames', function (Blueprint $table) {
            $table->increments('id', true);
            $table->text('file');  
            $table->string('name');  
            
            $table->integer('grade_id')->unsigned();
            $table->foreign('grade_id')->references('id')->on('schoolgrades')
                        ->onDelete('cascade');  

            $table->unsignedInteger('specialization_id');          
            $table->foreign('specialization_id')->references('id')->on('specializations')
                        ->onUpdate('cascade')->onDelete('cascade');
            
            $table->integer('classroom_id')->unsigned();
            $table->foreign('classroom_id')->references('id')->on('classrooms')
                        ->onDelete('cascade'); 
            
            $table->integer('Annscolaire');
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
        Schema::dropIfExists('exames');
    }
}
