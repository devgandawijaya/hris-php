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
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_number', 50);
            $table->string('nik_ktp', 20)->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->string('gender', 10)->default('male');
            $table->string('birth_place', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('marital_status', 30)->default('single');
            $table->string('tax_ptkp_status', 10)->default('TK/0');
            $table->string('npwp', 30)->nullable();
            $table->string('bpjs_tk_number', 30)->nullable();
            $table->string('bpjs_kes_number', 30)->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->string('employment_status', 30)->default('pkwt'); // pkwtt, pkwt, probation, internship
            $table->date('join_date');
            $table->date('end_date')->nullable();
            $table->string('bank_name', 50)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_account_holder', 150)->nullable();
            $table->decimal('basic_salary', 15, 2)->default(0.00);
            $table->string('photo_path')->nullable();
            $table->string('status', 30)->default('active'); // active, suspended, resigned, terminated
            $table->timestamps();

            $table->unique(['tenant_id', 'employee_number']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'department_id']);
        });

        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('document_type', 50); // ktp, npwp, cv, contract, degree, cert
            $table->string('title');
            $table->string('file_path');
            $table->unsignedInteger('file_size_kb')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('employees');
    }
};

