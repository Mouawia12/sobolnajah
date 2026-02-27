<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_contracts', function (Blueprint $table) {
            $table->string('external_contract_no', 40)->nullable()->after('payment_plan_id');
            $table->string('guardian_name', 160)->nullable()->after('academic_year');
            $table->json('metadata')->nullable()->after('notes');

            $table->unique(['school_id', 'academic_year', 'external_contract_no'], 'uq_contract_external_no_per_year');
        });
    }

    public function down(): void
    {
        Schema::table('student_contracts', function (Blueprint $table) {
            $table->dropUnique('uq_contract_external_no_per_year');
            $table->dropColumn(['external_contract_no', 'guardian_name', 'metadata']);
        });
    }
};
