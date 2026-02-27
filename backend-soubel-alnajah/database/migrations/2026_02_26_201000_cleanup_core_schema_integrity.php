<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('studentinfos', function (Blueprint $table) {
            if (!$this->indexExists('studentinfos', 'uq_studentinfos_user_id')) {
                $table->unique('user_id', 'uq_studentinfos_user_id');
            }
        });

        Schema::table('sections', function (Blueprint $table) {
            if (!$this->indexExists('sections', 'idx_sections_school_grade_classroom_status_created')) {
                $table->index(
                    ['school_id', 'grade_id', 'classroom_id', 'Status', 'created_at'],
                    'idx_sections_school_grade_classroom_status_created'
                );
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            if ($this->indexExists('notifications', 'notifications_data_unique')) {
                $table->dropUnique('notifications_data_unique');
            }

            if (!$this->indexExists('notifications', 'idx_notifications_notifiable_created')) {
                $table->index(['notifiable_id', 'created_at'], 'idx_notifications_notifiable_created');
            }

            if (!$this->indexExists('notifications', 'idx_notifications_notifiable_read')) {
                $table->index(['notifiable_id', 'read_at'], 'idx_notifications_notifiable_read');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if ($this->indexExists('notifications', 'idx_notifications_notifiable_read')) {
                $table->dropIndex('idx_notifications_notifiable_read');
            }

            if ($this->indexExists('notifications', 'idx_notifications_notifiable_created')) {
                $table->dropIndex('idx_notifications_notifiable_created');
            }

            if (!$this->indexExists('notifications', 'notifications_data_unique')) {
                $table->unique('data', 'notifications_data_unique');
            }
        });

        Schema::table('sections', function (Blueprint $table) {
            if ($this->indexExists('sections', 'idx_sections_school_grade_classroom_status_created')) {
                $table->dropIndex('idx_sections_school_grade_classroom_status_created');
            }
        });

        Schema::table('studentinfos', function (Blueprint $table) {
            if ($this->indexExists('studentinfos', 'uq_studentinfos_user_id')) {
                $table->dropUnique('uq_studentinfos_user_id');
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            return DB::table('information_schema.statistics')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', $table)
                ->where('index_name', $index)
                ->exists();
        }

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");

            foreach ($indexes as $item) {
                if (($item->name ?? null) === $index) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }
};
