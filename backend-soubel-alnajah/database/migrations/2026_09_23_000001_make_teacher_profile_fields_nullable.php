<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * جعل بيانات المعلّم غير إجبارية: يمكن إنشاء الأستاذ بحساب فقط
 * ثم إسناد التخصص/المواد لاحقاً من قائمة الأساتذة.
 * الأعمدة: specialization_id, gender, joining_date, address تصبح NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE teachers MODIFY specialization_id INT UNSIGNED NULL');
            DB::statement('ALTER TABLE teachers MODIFY gender INT NULL');
            DB::statement('ALTER TABLE teachers MODIFY joining_date DATE NULL');
            DB::statement('ALTER TABLE teachers MODIFY address TEXT NULL');

            return;
        }

        if ($driver === 'sqlite') {
            // SQLite لا يدعم تعديل العمود؛ نعيد بناء الجدول مع الحفاظ على البيانات.
            // legacy_alter_table يمنع SQLite من إعادة كتابة مفاتيح الجداول الأخرى نحو الاسم المؤقت.
            DB::statement('PRAGMA legacy_alter_table = ON');
            Schema::disableForeignKeyConstraints();
            DB::statement('ALTER TABLE teachers RENAME TO teachers_old');
            DB::statement('CREATE TABLE teachers (
                id integer primary key autoincrement not null,
                user_id integer not null,
                specialization_id integer null,
                name varchar not null,
                gender integer null,
                joining_date date null,
                address text null,
                created_at datetime null,
                updated_at datetime null,
                foreign key(user_id) references users(id) on update cascade on delete cascade,
                foreign key(specialization_id) references specializations(id) on update cascade on delete cascade
            )');
            DB::statement('INSERT INTO teachers (id, user_id, specialization_id, name, gender, joining_date, address, created_at, updated_at)
                SELECT id, user_id, specialization_id, name, gender, joining_date, address, created_at, updated_at FROM teachers_old');
            DB::statement('DROP TABLE teachers_old');
            Schema::enableForeignKeyConstraints();
            DB::statement('PRAGMA legacy_alter_table = OFF');
        }
    }

    public function down(): void
    {
        // لا نُرجع القيد NOT NULL لتفادي فشل التراجع على صفوف تحمل قيماً فارغة.
    }
};
