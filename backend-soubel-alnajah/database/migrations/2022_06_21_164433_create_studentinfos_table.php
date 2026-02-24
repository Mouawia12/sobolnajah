<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentinfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('studentinfos', function (Blueprint $table) {
            $table->increments('id', true);

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')
                            ->onUpdate('cascade')->onDelete('cascade');


            $table->unsignedInteger('section_id')->nullable();
            $table->foreign('section_id')->references('id')->on('sections')
                        ->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedInteger('parent_id');
            $table->foreign('parent_id')->references('id')->on('my_parents')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->integer('gender');

            $table->string('prenom');   
            $table->string('nom');
            $table->string('lieunaissance');   
            $table->string('wilaya');   
            $table->string('dayra');   
            $table->string('baladia');
            
            $table->date('datenaissance');

            $table->bigInteger('numtelephone');

            $table->softDeletes();
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
        Schema::dropIfExists('studentinfos');
    }
}
