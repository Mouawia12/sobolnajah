<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('school_id');
            $table->string('slug', 180);
            $table->string('title', 180);
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'slug']);
            $table->index(['school_id', 'status', 'published_at']);
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('school_id');
            $table->unsignedBigInteger('job_post_id');
            $table->string('full_name', 160);
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->string('status', 30)->default('new');
            $table->string('cv_path', 255);
            $table->string('cv_original_name', 255)->nullable();
            $table->string('cv_mime', 120)->nullable();
            $table->unsignedBigInteger('cv_size')->nullable();
            $table->string('submitted_ip', 45)->nullable();
            $table->text('submitted_user_agent')->nullable();
            $table->text('review_notes')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status', 'created_at']);
            $table->index(['job_post_id', 'created_at']);
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('job_post_id')->references('id')->on('job_posts')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_posts');
    }
};
