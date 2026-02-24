<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeachersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->increments('id', true);

            $table->unsignedBigInteger('user_id');
        
            $table->foreign('user_id')->references('id')->on('users')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedInteger('specialization_id');

            $table->foreign('specialization_id')->references('id')->on('specializations')
                  ->onUpdate('cascade')->onDelete('cascade');
            
            $table->string('name');
            
            $table->Integer('gender');
            $table->date('joining_date');
            $table->text('address');
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
        Schema::dropIfExists('teachers');
    }
}
