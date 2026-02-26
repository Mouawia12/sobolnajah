<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('school_id');
            $table->string('name', 120);
            $table->string('plan_type', 40);
            $table->unsignedTinyInteger('installments_count')->nullable();
            $table->string('interval_unit', 20)->default('month');
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'name']);
            $table->index(['school_id', 'is_active']);
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::create('student_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('school_id');
            $table->unsignedInteger('student_id');
            $table->unsignedBigInteger('payment_plan_id')->nullable();
            $table->string('academic_year', 20);
            $table->decimal('total_amount', 12, 2);
            $table->string('plan_type', 40)->default('yearly');
            $table->unsignedTinyInteger('installments_count')->nullable();
            $table->unsignedTinyInteger('due_day')->nullable();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->string('status', 30)->default('draft');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'student_id', 'academic_year'], 'uq_student_contract_year');
            $table->index(['school_id', 'status', 'academic_year']);
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('studentinfos')->onDelete('cascade');
            $table->foreign('payment_plan_id')->references('id')->on('payment_plans')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('contract_installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->unsignedTinyInteger('installment_no');
            $table->date('due_date');
            $table->decimal('amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->string('label', 120)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique(['contract_id', 'installment_no']);
            $table->index(['contract_id', 'status', 'due_date']);
            $table->foreign('contract_id')->references('id')->on('student_contracts')->onDelete('cascade');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('school_id');
            $table->unsignedBigInteger('contract_id');
            $table->unsignedBigInteger('installment_id')->nullable();
            $table->string('receipt_number', 80);
            $table->date('paid_on');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 20)->default('cash');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'receipt_number']);
            $table->index(['school_id', 'paid_on']);
            $table->index(['contract_id', 'paid_on']);
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('contract_id')->references('id')->on('student_contracts')->onDelete('cascade');
            $table->foreign('installment_id')->references('id')->on('contract_installments')->nullOnDelete();
            $table->foreign('received_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('school_id');
            $table->unsignedBigInteger('payment_id');
            $table->string('receipt_code', 80);
            $table->timestamp('issued_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'receipt_code']);
            $table->unique('payment_id');
            $table->index(['school_id', 'issued_at']);
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_receipts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('contract_installments');
        Schema::dropIfExists('student_contracts');
        Schema::dropIfExists('payment_plans');
    }
};
