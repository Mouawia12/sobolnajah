<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoteStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('note_students', function (Blueprint $table) {

            $table->increments('id', true);

   

            $table->unsignedInteger('student_id');
            $table->foreign('student_id')->references('id')->on('studentinfos')
                        ->onUpdate('cascade')->onDelete('cascade');

            $table->text('urlfile1')->nullable();
            $table->text('urlfile2')->nullable();
            $table->text('urlfile3')->nullable(); 
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
        Schema::dropIfExists('note_students');
    }
}
