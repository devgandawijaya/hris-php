<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Job Vacancies / Postings
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('title');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->string('employment_type', 30)->default('full_time'); // full_time, part_time, contract, internship
            $table->string('location')->default('Jakarta / Onsite');
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->decimal('salary_min', 15, 2)->nullable();
            $table->decimal('salary_max', 15, 2)->nullable();
            $table->string('status', 30)->default('published'); // draft, published, closed
            $table->date('deadline')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // 2. Candidates Database
        Schema::create('job_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->string('resume_path')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'email']);
        });

        // 3. Job Applications & Recruitment Pipeline
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('job_posting_id')->constrained('job_postings')->cascadeOnDelete();
            $table->foreignId('job_candidate_id')->constrained('job_candidates')->cascadeOnDelete();
            $table->string('stage', 30)->default('applied'); // applied, screening, interview, offered, hired, rejected
            $table->dateTime('interview_scheduled_at')->nullable();
            $table->text('interview_notes')->nullable();
            $table->decimal('interview_score', 5, 2)->nullable(); // 0.00 - 100.00
            $table->decimal('offered_salary', 15, 2)->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('converted_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'stage']);
            $table->unique(['tenant_id', 'job_posting_id', 'job_candidate_id'], 'job_app_unique');
        });

        // 4. Company Assets (Hardware, Access Cards, Vehicles)
        Schema::create('company_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('asset_code', 50);
            $table->string('name');
            $table->string('category', 50); // laptop, phone, vehicle, access_card, other
            $table->string('serial_number')->nullable();
            $table->string('status', 30)->default('available'); // available, assigned, in_repair, retired
            $table->foreignId('assigned_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->date('assigned_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->unique(['tenant_id', 'asset_code']);
        });

        // 5. Onboarding & Offboarding Checklist Tasks
        Schema::create('onboarding_offboarding_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('type', 30); // onboarding, offboarding
            $table->string('title');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->dateTime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id', 'type']);
        });

        // 6. Exit Interviews
        Schema::create('exit_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('interview_date');
            $table->string('reason_for_leaving', 50); // new_job, compensation, career_change, personal, relocate, company_culture, other
            $table->text('feedback')->nullable();
            $table->boolean('willing_to_recommend')->default(true);
            $table->foreignId('interviewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
        });

        // 7. Performance Appraisal Cycles
        Schema::create('performance_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name'); // e.g. "Penilaian Kinerja Semester 1 2026"
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 30)->default('active'); // active, reviewing, completed
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // 8. KPI / OKR Goals
        Schema::create('kpi_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('performance_cycle_id')->nullable()->constrained('performance_cycles')->nullOnDelete();
            $table->string('type', 20)->default('kpi'); // kpi, okr
            $table->string('title');
            $table->decimal('target_value', 10, 2);
            $table->decimal('current_value', 10, 2)->default(0.00);
            $table->string('unit', 30)->default('%'); // %, Rp, Pcs, Tasks, Score
            $table->decimal('weight', 5, 2)->default(100.00); // Bobot %
            $table->date('deadline')->nullable();
            $table->string('status', 30)->default('in_progress'); // in_progress, achieved, missed
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
        });

        // 9. Performance Reviews & 360/Manager Appraisals
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('performance_cycle_id')->constrained('performance_cycles')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('self_rating', 5, 2)->nullable(); // 0.00 - 100.00
            $table->text('self_feedback')->nullable();
            $table->decimal('manager_rating', 5, 2)->nullable();
            $table->text('manager_feedback')->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->string('status', 30)->default('draft'); // draft, submitted, finalized
            $table->timestamps();

            $table->unique(['tenant_id', 'performance_cycle_id', 'employee_id'], 'perf_review_unique');
        });

        // 10. Training Courses (Learning & Development)
        Schema::create('training_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('title');
            $table->string('trainer_name')->nullable();
            $table->string('category', 50)->default('technical'); // technical, leadership, compliance, soft_skills
            $table->string('location')->default('Online / Zoom');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->unsignedInteger('capacity')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 30)->default('scheduled'); // scheduled, ongoing, completed, cancelled
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // 11. Training Enrollments & Certifications
        Schema::create('training_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('training_course_id')->constrained('training_courses')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('status', 30)->default('enrolled'); // enrolled, attended, completed, dropped
            $table->decimal('score', 5, 2)->nullable();
            $table->string('certificate_number')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'training_course_id', 'employee_id'], 'training_enroll_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_enrollments');
        Schema::dropIfExists('training_courses');
        Schema::dropIfExists('performance_reviews');
        Schema::dropIfExists('kpi_goals');
        Schema::dropIfExists('performance_cycles');
        Schema::dropIfExists('exit_interviews');
        Schema::dropIfExists('onboarding_offboarding_checklists');
        Schema::dropIfExists('company_assets');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_candidates');
        Schema::dropIfExists('job_postings');
    }
};
