<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('job_posts', 'cover_image_path')) {
                $table->string('cover_image_path', 255)->nullable()->after('requirements');
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            if (Schema::hasColumn('job_posts', 'cover_image_path')) {
                $table->dropColumn('cover_image_path');
            }
        });
    }
};
