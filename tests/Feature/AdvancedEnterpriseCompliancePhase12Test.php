<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Position;
use App\Models\Employee;
use App\Models\User;
use App\Models\Role;
use App\Models\EmploymentContract;
use App\Models\ProbationEvaluation;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\PaymentGatewayTransaction;
use App\Models\TrainingCourse;
use App\Models\TrainingEnrollment;
use App\Models\TrainingKirkpatrickEvaluation;
use App\Models\TrainingCertificate;
use App\Models\FwaSchedule;
use App\Services\Contract\DigitalContractService;
use App\Services\Probation\ProbationEvaluationService;
use App\Services\Payment\PaymentGatewayService;
use App\Services\Learning\KirkpatrickEvaluationService;
use App\Services\Attendance\FwaService;
use App\Tenancy\TenantManager;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdvancedEnterpriseCompliancePhase12Test extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Company $company;
    protected Branch $branch;
    protected User $hrUser;
    protected User $empUser;
    protected Employee $employeeBudi;
    protected Employee $employeeAni;
    protected Department $department;
    protected Position $positionDev;
    protected SubscriptionPlan $plan;
    protected Subscription $subscription;
    protected SubscriptionInvoice $invoice;
    protected TrainingCourse $course;
    protected TrainingEnrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        $this->tenant = Tenant::create([
            'name' => 'PT Enterprise Nusantara',
            'code' => 'pt-ent-nusantara',
            'status' => 'active',
        ]);

        app(TenantManager::class)->setTenant($this->tenant);

        $this->company = Company::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'PT Enterprise Nusantara',
            'legal_name' => 'PT Enterprise Nusantara Mandiri',
            'business_type' => 'Teknologi & Konsultasi Manajemen',
            'email' => 'admin@enterprisenusantara.co.id',
            'phone' => '021-5558900',
            'address' => 'Gedung Menara Sudirman Lt. 24, Jakarta Selatan',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12190',
            'npwp' => '01.234.567.8-012.000',
            'status' => 'active',
        ]);

        $this->branch = Branch::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Kantor Pusat Sudirman',
            'code' => 'KPS-JKT',
            'address' => 'Gedung Menara Sudirman Lt. 24, Jakarta Selatan',
            'is_main_branch' => true,
            'status' => 'active',
        ]);

        $this->department = Department::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Engineering & Technology',
            'code' => 'ENG',
        ]);

        $this->positionDev = Position::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'title' => 'Software Engineer',
            'code' => 'SE-01',
        ]);

        $hrRole = Role::firstOrCreate(['name' => 'hr_admin', 'guard_name' => 'web']);
        $empRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

        $this->hrUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Dewi HR Director',
            'email' => 'dewi.hr@enterprisenusantara.co.id',
            'password' => bcrypt('Password123!'),
            'status' => 'active',
        ]);
        $this->hrUser->roles()->attach($hrRole->id, ['tenant_id' => $this->tenant->id]);

        $this->empUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@enterprisenusantara.co.id',
            'password' => bcrypt('Password123!'),
            'status' => 'active',
        ]);
        $this->empUser->roles()->attach($empRole->id, ['tenant_id' => $this->tenant->id]);

        $this->employeeBudi = Employee::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->empUser->id,
            'employee_code' => 'EMP-001',
            'nik_ktp' => '3171012345670001',
            'first_name' => 'Budi',
            'last_name' => 'Santoso',
            'email' => 'budi.santoso@enterprisenusantara.co.id',
            'phone' => '081234567890',
            'gender' => 'male',
            'birth_place' => 'Jakarta',
            'birth_date' => '1992-05-15',
            'marital_status' => 'married',
            'tax_ptkp_status' => 'K/1',
            'department_id' => $this->department->id,
            'position_id' => $this->positionDev->id,
            'employment_status' => 'probation',
            'join_date' => now()->subMonths(2)->toDateString(),
            'basic_salary' => 12000000,
            'status' => 'active',
        ]);

        $this->employeeAni = Employee::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'employee_code' => 'EMP-002',
            'nik_ktp' => '3171012345670002',
            'first_name' => 'Ani',
            'last_name' => 'Wijaya',
            'email' => 'ani.wijaya@enterprisenusantara.co.id',
            'gender' => 'female',
            'department_id' => $this->department->id,
            'position_id' => $this->positionDev->id,
            'employment_status' => 'pkwtt',
            'join_date' => now()->subYears(1)->toDateString(),
            'basic_salary' => 15000000,
            'status' => 'active',
        ]);

        // Subscription Setup
        $this->plan = SubscriptionPlan::create([
            'name' => 'Enterprise Cloud',
            'slug' => 'enterprise-cloud',
            'price_monthly' => 2500000,
            'price_yearly' => 25000000,
            'max_employees' => 250,
            'status' => 'active',
        ]);

        $this->subscription = Subscription::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'plan_id' => $this->plan->id,
            'billing_cycle' => 'monthly',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
        ]);

        $this->invoice = SubscriptionInvoice::create([
            'tenant_id' => $this->tenant->id,
            'subscription_id' => $this->subscription->id,
            'invoice_number' => 'INV-2026-0001',
            'amount' => 2500000,
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'pending',
        ]);

        // Training Course Setup
        $this->course = TrainingCourse::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'title' => 'Advanced Cloud Architecture & Security',
            'description' => 'Comprehensive masterclass on modern secure microservices and distributed systems.',
            'trainer_name' => 'Ir. Gunawan Wibisono',
            'duration_hours' => 32,
            'status' => 'active',
        ]);

        $this->enrollment = TrainingEnrollment::create([
            'tenant_id' => $this->tenant->id,
            'training_course_id' => $this->course->id,
            'employee_id' => $this->employeeBudi->id,
            'enrolled_at' => now()->subDays(10),
            'status' => 'completed',
        ]);
    }

    public function test_digital_contract_drafting_with_indonesian_legal_clauses(): void
    {
        $service = app(DigitalContractService::class);

        $contract = $service->draftContract($this->employeeBudi, [
            'type' => 'pkwt',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'monthly_wage' => 12000000,
        ]);

        $this->assertInstanceOf(EmploymentContract::class, $contract);
        $this->assertNotEmpty($contract->contract_number);
        $this->assertStringContainsString('HRD-PKWT', $contract->contract_number);
        $this->assertStringContainsString('PASAL 1', $contract->contract_body);
        $this->assertStringContainsString('PP No. 35 Tahun 2021', $contract->contract_body);
        $this->assertFalse($contract->is_digitally_signed);
        $this->assertNull($contract->signed_hash);
    }

    public function test_digital_contract_dual_signing_and_sha256_cryptographic_seal(): void
    {
        $service = app(DigitalContractService::class);

        $contract = $service->draftContract($this->employeeBudi, [
            'type' => 'pkwtt',
            'start_date' => now()->toDateString(),
            'monthly_wage' => 14000000,
        ]);

        // 1. Employee signs
        $service->signByEmployee($contract, 'SIG_EMP_BUDI_DATA', '110.35.42.10');
        $contract->refresh();

        $this->assertNotNull($contract->employee_signed_at);
        $this->assertEquals('110.35.42.10', $contract->employee_sign_ip);
        $this->assertFalse($contract->is_digitally_signed); // Waiting company

        // 2. Company signs
        $service->signByCompany($contract, 'SIG_COMPANY_DIRECTOR_DATA', $this->hrUser, '103.220.15.5');
        $contract->refresh();

        $this->assertNotNull($contract->company_signed_at);
        $this->assertEquals('103.220.15.5', $contract->company_sign_ip);
        $this->assertEquals($this->hrUser->name, $contract->signer_name);
        $this->assertTrue($contract->is_digitally_signed);
        $this->assertNotNull($contract->signed_hash);
        $this->assertEquals(64, strlen($contract->signed_hash)); // SHA256 length
    }

    public function test_probation_evaluation_weighted_scoring_formula(): void
    {
        $service = app(ProbationEvaluationService::class);

        // Tech 80% (bobot 40% = 32), Attendance 90% (bobot 30% = 27), Culture 80% (bobot 30% = 24) -> Total = 83.00
        $evaluation = $service->evaluateProbation($this->employeeBudi, $this->hrUser, [
            'technical_competence_score' => 80,
            'attendance_discipline_score' => 90,
            'culture_integrity_score' => 80,
            'evaluation_date' => now()->toDateString(),
        ]);

        $this->assertEquals(83.00, (float) $evaluation->final_score);
        $this->assertEquals('passed_pkwtt', $evaluation->decision);
        $this->assertNotEmpty($evaluation->sk_number);
    }

    public function test_probation_passed_promotes_employee_to_pkwtt_and_issues_sk(): void
    {
        $this->assertEquals('probation', $this->employeeBudi->employment_status);

        $service = app(ProbationEvaluationService::class);
        $evaluation = $service->evaluateProbation($this->employeeBudi, $this->hrUser, [
            'technical_competence_score' => 95,
            'attendance_discipline_score' => 90,
            'culture_integrity_score' => 92,
        ]);

        $this->employeeBudi->refresh();
        $this->assertEquals('pkwtt', $this->employeeBudi->employment_status);
        $this->assertEquals('passed_pkwtt', $evaluation->decision);
        $this->assertStringContainsString('SK-DIR/PKWTT', $evaluation->sk_number);
    }

    public function test_probation_failed_terminates_employment_pursuant_to_pasal_60(): void
    {
        $service = app(ProbationEvaluationService::class);
        $evaluation = $service->evaluateProbation($this->employeeBudi, $this->hrUser, [
            'technical_competence_score' => 50,
            'attendance_discipline_score' => 60,
            'culture_integrity_score' => 55,
        ]);

        $this->employeeBudi->refresh();
        $this->assertEquals('failed_terminated', $evaluation->decision);
        $this->assertEquals('inactive', $this->employeeBudi->status);
        $this->assertNotNull($this->employeeBudi->end_date);
    }

    public function test_payment_gateway_checkout_creates_transaction_and_snap_token(): void
    {
        $service = app(PaymentGatewayService::class);

        $transaction = $service->createCheckoutTransaction($this->invoice, 'midtrans', 'qris');

        $this->assertInstanceOf(PaymentGatewayTransaction::class, $transaction);
        $this->assertEquals('midtrans', $transaction->gateway_provider);
        $this->assertEquals('qris', $transaction->payment_type);
        $this->assertEquals(2500000, (float) $transaction->gross_amount);
        $this->assertEquals('pending', $transaction->transaction_status);
        $this->assertNotEmpty($transaction->snap_token);
        $this->assertNotEmpty($transaction->payment_url);
    }

    public function test_payment_gateway_webhook_settlement_marks_invoice_paid(): void
    {
        $service = app(PaymentGatewayService::class);
        $transaction = $service->createCheckoutTransaction($this->invoice, 'midtrans', 'qris');

        $serverKey = 'test-server-key-12345';
        $statusCode = '200';
        $grossAmount = number_format($this->invoice->amount, 2, '.', '');
        $orderId = $transaction->order_id;
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'MIDTRANS-TRX-998811',
        ];

        $processedTx = $service->handleMidtransWebhook($payload, $serverKey);

        $this->assertNotNull($processedTx);
        $this->assertEquals('settlement', $processedTx->transaction_status);
        $this->invoice->refresh();
        $this->assertEquals('paid', $this->invoice->status);
    }

    public function test_payment_gateway_simulate_sandbox_success(): void
    {
        $service = app(PaymentGatewayService::class);
        $transaction = $service->createCheckoutTransaction($this->invoice, 'midtrans', 'bca_va');

        $service->simulateSandboxPayment($transaction);

        $transaction->refresh();
        $this->invoice->refresh();
        $this->assertEquals('settlement', $transaction->transaction_status);
        $this->assertEquals('paid', $this->invoice->status);
        $this->assertNotNull($transaction->paid_at);
    }

    public function test_kirkpatrick_four_level_training_evaluation_and_learning_gain(): void
    {
        $service = app(KirkpatrickEvaluationService::class);

        // Pre-test: 60, Post-test: 90 -> Gain %: (90 - 60) / (100 - 60) * 100 = 75.0%
        $evaluation = $service->recordTestScores($this->enrollment, 60.00, 90.00);

        $this->assertInstanceOf(TrainingKirkpatrickEvaluation::class, $evaluation);
        $this->assertEquals(60.00, (float) $evaluation->pre_test_score);
        $this->assertEquals(90.00, (float) $evaluation->post_test_score);
        $this->assertEquals(75.00, (float) $evaluation->learning_gain_percentage);

        // Level 1: Reaction
        $service->submitReaction($evaluation, 4.80, 'Pelatihan sangat aplikatif dan meningkatkan produktivitas tim.');
        $evaluation->refresh();
        $this->assertEquals(4.80, (float) $evaluation->level1_reaction_score);

        // Level 3: Behavior
        $service->submitBehaviorAssessment($evaluation, $this->hrUser, 4.60, 'Mampu memimpin deployment microservices mandiri.');
        $evaluation->refresh();
        $this->assertEquals(4.60, (float) $evaluation->level3_behavior_score);
        $this->assertEquals('completed', $evaluation->status);
    }

    public function test_kirkpatrick_digital_certificate_issuance_and_integrity_hash(): void
    {
        $service = app(KirkpatrickEvaluationService::class);
        $service->recordTestScores($this->enrollment, 70.00, 95.00);

        $certificate = $service->issueCertificate($this->enrollment, $this->hrUser);

        $this->assertInstanceOf(TrainingCertificate::class, $certificate);
        $this->assertNotEmpty($certificate->certificate_number);
        $this->assertEquals('Budi Santoso', $certificate->recipient_name);
        $this->assertEquals(64, strlen($certificate->verification_hash)); // SHA256 integrity hash
    }

    public function test_flexible_working_arrangement_fwa_request_and_manager_approval(): void
    {
        $service = app(FwaService::class);

        $today = now()->toDateString();
        $schedule = $service->requestFwa($this->employeeBudi, $today, 'wfh', 'Deep work coding session');

        $this->assertInstanceOf(FwaSchedule::class, $schedule);
        $this->assertEquals('wfh', $schedule->work_mode);
        $this->assertEquals('pending', $schedule->status);

        // Manager Approves
        $service->processApproval($schedule, $this->hrUser, true);
        $schedule->refresh();

        $this->assertEquals('approved', $schedule->status);
        $this->assertEquals($this->hrUser->id, $schedule->approved_by);
        $this->assertNotNull($schedule->approved_at);

        // Test FWA Attendance Check
        $check = $service->checkAttendancePolicy($this->employeeBudi, $today);
        $this->assertEquals('wfh', $check['scheduled_mode']);
        $this->assertTrue($check['wfh_permitted']);
    }
}
