<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('school_id');
            $table->foreign('school_id')->references('id')->on('schools')
                ->onUpdate('cascade')->onDelete('cascade');

            // حساب دخول اختياري للموظف
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('set null');

            $table->string('name');
            $table->string('job_title')->nullable();
            $table->string('phone')->nullable();
            $table->date('joining_date')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['school_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
