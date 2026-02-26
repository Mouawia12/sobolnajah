<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('studentinfos', function (Blueprint $table) {
            $table->index(['section_id', 'created_at'], 'idx_studentinfos_section_created');
            $table->index(['parent_id', 'created_at'], 'idx_studentinfos_parent_created');
            $table->index(['user_id'], 'idx_studentinfos_user_id');
        });

        Schema::table('inscriptions', function (Blueprint $table) {
            $table->index(['school_id', 'statu', 'created_at'], 'idx_inscriptions_school_status_created');
            $table->index(['grade_id', 'classroom_id'], 'idx_inscriptions_grade_classroom');
            $table->index(['numtelephone'], 'idx_inscriptions_phone');
        });

        Schema::table('absences', function (Blueprint $table) {
            $table->index(['student_id', 'date'], 'idx_absences_student_date');
            $table->index(['date'], 'idx_absences_date');
        });

        Schema::table('publications', function (Blueprint $table) {
            $table->index(['school_id', 'created_at'], 'idx_publications_school_created');
            $table->index(['grade_id', 'agenda_id'], 'idx_publications_grade_agenda');
        });

        Schema::table('chat_room_user', function (Blueprint $table) {
            $table->index(['user_id', 'last_read_at'], 'idx_chat_room_user_user_last_read');
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->index(['chat_room_id', 'created_at'], 'idx_chat_messages_room_created');
            $table->index(['user_id', 'created_at'], 'idx_chat_messages_user_created');
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex('idx_chat_messages_room_created');
            $table->dropIndex('idx_chat_messages_user_created');
        });

        Schema::table('chat_room_user', function (Blueprint $table) {
            $table->dropIndex('idx_chat_room_user_user_last_read');
        });

        Schema::table('publications', function (Blueprint $table) {
            $table->dropIndex('idx_publications_school_created');
            $table->dropIndex('idx_publications_grade_agenda');
        });

        Schema::table('absences', function (Blueprint $table) {
            $table->dropIndex('idx_absences_student_date');
            $table->dropIndex('idx_absences_date');
        });

        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropIndex('idx_inscriptions_school_status_created');
            $table->dropIndex('idx_inscriptions_grade_classroom');
            $table->dropIndex('idx_inscriptions_phone');
        });

        Schema::table('studentinfos', function (Blueprint $table) {
            $table->dropIndex('idx_studentinfos_section_created');
            $table->dropIndex('idx_studentinfos_parent_created');
            $table->dropIndex('idx_studentinfos_user_id');
        });
    }
};
