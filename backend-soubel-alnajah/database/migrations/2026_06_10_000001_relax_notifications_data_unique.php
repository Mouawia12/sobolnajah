<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * عمود notifications.data كان string(255) UNIQUE، ما يجعل إرسال نفس الإشعار
 * لأكثر من مستلم (تلميذ + ولي) أو إعادة الحفظ يفشل بخطأ تكرار. نزيل القيد الفريد
 * ونوسّع العمود إلى text. المعالجة حسب المحرّك لاختلاف SQLite عن MySQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // القيد الفريد المضمّن في SQLite فهرس تلقائي لا يُسقَط بالاسم، لذا نعيد بناء الجدول.
            $this->rebuildSqlite(false);

            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropUnique('notifications_data_unique');
        });

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE notifications MODIFY data TEXT NOT NULL');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->rebuildSqlite(true);

            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE notifications MODIFY data VARCHAR(255) NOT NULL');
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->unique('data', 'notifications_data_unique');
        });
    }

    private function rebuildSqlite(bool $withUnique): void
    {
        // نبني جدولاً مؤقتاً (تُشتقّ أسماء فهارسه من اسمه فلا تتعارض مع فهارس الجدول القديم)،
        // ننسخ البيانات، نُسقط القديم، ثم نعيد تسمية المؤقت.
        Schema::dropIfExists('notifications_tmp');

        Schema::create('notifications_tmp', function (Blueprint $table) use ($withUnique) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->index(['notifiable_type', 'notifiable_id']);
            if ($withUnique) {
                $table->string('data', 255)->unique();
            } else {
                $table->text('data');
            }
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        DB::statement('INSERT INTO notifications_tmp (id, type, notifiable_type, notifiable_id, data, read_at, created_at, updated_at)
            SELECT id, type, notifiable_type, notifiable_id, data, read_at, created_at, updated_at FROM notifications');

        Schema::drop('notifications');
        Schema::rename('notifications_tmp', 'notifications');
    }
};
