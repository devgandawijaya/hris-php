<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Upgrade employment_contracts table for digital e-signatures (UU ITE No. 1/2024 & PP 71/2019)
        Schema::table('employment_contracts', function (Blueprint $table) {
            $table->text('contract_body')->nullable()->after('notes');
            $table->text('employee_signature')->nullable()->after('contract_body');
            $table->timestamp('employee_signed_at')->nullable()->after('employee_signature');
            $table->string('employee_sign_ip', 45)->nullable()->after('employee_signed_at');
            $table->text('company_signature')->nullable()->after('employee_sign_ip');
            $table->timestamp('company_signed_at')->nullable()->after('company_signature');
            $table->string('company_sign_ip', 45)->nullable()->after('company_signed_at');
            $table->string('signer_name', 150)->nullable()->after('company_sign_ip');
            $table->string('signer_position', 100)->nullable()->after('signer_name');
            $table->string('signed_hash', 64)->nullable()->after('signer_position');
            $table->boolean('is_digitally_signed')->default(false)->after('signed_hash');
        });

        // 2. Probation Evaluations (Pasal 60 UU Ketenagakerjaan No. 13/2003 & PP 35/2021)
        Schema::create('probation_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('evaluator_user_id')->constrained('users')->cascadeOnDelete();
            $table->date('evaluation_date');
            $table->decimal('technical_competence_score', 5, 2)->default(0.00); // 0-100 (Bobot 40%)
            $table->decimal('attendance_discipline_score', 5, 2)->default(0.00); // 0-100 (Bobot 30%)
            $table->decimal('culture_integrity_score', 5, 2)->default(0.00); // 0-100 (Bobot 30%)
            $table->decimal('final_score', 5, 2)->default(0.00); // Calculated composite
            $table->string('decision', 30); // passed_pkwtt, extended, failed_terminated
            $table->string('sk_number', 100)->nullable(); // e.g. "SK/DIR-PKWTT/2026/001"
            $table->text('evaluator_notes')->nullable();
            $table->text('development_recommendations')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'company_id', 'decision']);
            $table->index(['tenant_id', 'employee_id']);
        });

        // 3. SaaS Payment Gateway Transactions (Midtrans / Xendit Snap & Webhooks)
        Schema::create('payment_gateway_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('subscription_invoice_id')->constrained('subscription_invoices')->cascadeOnDelete();
            $table->string('gateway_provider', 50)->default('midtrans'); // midtrans, xendit, sandbox
            $table->string('transaction_id', 100)->nullable();
            $table->string('order_id', 100)->unique();
            $table->decimal('gross_amount', 15, 2);
            $table->string('payment_type', 50)->default('qris'); // qris, bca_va, mandiri_va, bni_va, bri_va, credit_card
            $table->string('transaction_status', 30)->default('pending'); // pending, settlement, capture, expire, cancel, deny
            $table->string('signature_hash', 128)->nullable();
            $table->string('snap_token', 255)->nullable();
            $table->string('payment_url', 500)->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'transaction_status']);
            $table->index(['gateway_provider', 'order_id']);
        });

        // 4. Kirkpatrick Training Evaluations (Level 1-4)
        Schema::create('training_kirkpatrick_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('training_enrollment_id')->constrained('training_enrollments')->cascadeOnDelete();
            $table->decimal('pre_test_score', 5, 2)->nullable(); // 0 - 100
            $table->decimal('post_test_score', 5, 2)->nullable(); // 0 - 100
            $table->decimal('learning_gain_percentage', 5, 2)->default(0.00); // Level 2 Delta
            $table->decimal('level1_reaction_score', 3, 2)->nullable(); // 1.0 - 5.0
            $table->text('level1_feedback')->nullable();
            $table->decimal('level3_behavior_score', 3, 2)->nullable(); // 1.0 - 5.0 (Supervisor review)
            $table->text('level3_notes')->nullable();
            $table->foreignId('level3_evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('level3_evaluated_at')->nullable();
            $table->string('status', 30)->default('pending'); // pending, pre_test, post_test, completed
            $table->timestamps();

            $table->index(['tenant_id', 'training_enrollment_id']);
        });

        // 5. Digital Training Certificates
        Schema::create('training_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('training_enrollment_id')->constrained('training_enrollments')->cascadeOnDelete();
            $table->string('certificate_number', 100)->unique();
            $table->string('recipient_name', 150);
            $table->string('course_title', 200);
            $table->date('completion_date');
            $table->decimal('score_achieved', 5, 2)->default(0.00);
            $table->string('grade_label', 30)->default('Lulus / Passed');
            $table->string('verification_hash', 64);
            $table->string('signer_name', 150);
            $table->string('signer_title', 100);
            $table->timestamps();

            $table->index(['tenant_id', 'certificate_number']);
        });

        // 6. Flexible Working Arrangement (FWA - WFH vs WFO Scheduler)
        Schema::create('fwa_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->string('work_mode', 20)->default('wfo'); // wfo, wfh
            $table->text('reason')->nullable();
            $table->string('status', 30)->default('pending'); // pending, approved, rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'employee_id', 'date']);
            $table->index(['tenant_id', 'company_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fwa_schedules');
        Schema::dropIfExists('training_certificates');
        Schema::dropIfExists('training_kirkpatrick_evaluations');
        Schema::dropIfExists('payment_gateway_transactions');
        Schema::dropIfExists('probation_evaluations');

        Schema::table('employment_contracts', function (Blueprint $table) {
            $table->dropColumn([
                'contract_body',
                'employee_signature',
                'employee_signed_at',
                'employee_sign_ip',
                'company_signature',
                'company_signed_at',
                'company_sign_ip',
                'signer_name',
                'signer_position',
                'signed_hash',
                'is_digitally_signed',
            ]);
        });
    }
};
