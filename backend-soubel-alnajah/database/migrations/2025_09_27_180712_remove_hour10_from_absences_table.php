<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('absences', function (Blueprint $table) {
            $table->dropColumn('hour_10'); // حذف العمود فقط
        });
    }

    public function down()
    {
        Schema::table('absences', function (Blueprint $table) {
            $table->boolean('hour_10')->default(true); // استرجاع العمود إذا عملت rollback
        });
    }
};
