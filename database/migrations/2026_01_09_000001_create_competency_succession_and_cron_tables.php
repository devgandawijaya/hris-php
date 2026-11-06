<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Skill Categories (e.g. Technical/Hard Skill, Core Soft Skill, Leadership, Legal Compliance)
        Schema::create('skill_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'name']);
        });

        // 2. Competency / Skills Dictionary
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('skill_category_id')->constrained('skill_categories')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('code', 50)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'skill_category_id']);
            $table->unique(['tenant_id', 'name']);
        });

        // 3. Position Skill Requirements (Target proficiency level 1-5 per role)
        Schema::create('position_skill_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->unsignedTinyInteger('required_level')->default(3); // 1=Dasar, 2=Menengah, 3=Mahir, 4=Ahli, 5=Pakar
            $table->boolean('is_mandatory')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'position_id']);
            $table->unique(['tenant_id', 'position_id', 'skill_id'], 'pos_skill_unique');
        });

        // 4. Employee Skills & Assessment (Actual employee proficiency level 1-5 + Certifications)
        Schema::create('employee_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->unsignedTinyInteger('current_level')->default(1); // 1-5
            $table->string('certification_name')->nullable();
            $table->date('certification_date')->nullable();
            $table->date('valid_until')->nullable();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('assessment_notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
            $table->unique(['tenant_id', 'employee_id', 'skill_id'], 'emp_skill_unique');
        });

        // 5. Key Position Succession Plans
        Schema::create('succession_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
            $table->foreignId('current_holder_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('criticality_level', 30)->default('high'); // low, medium, high, critical
            $table->text('emergency_replacement_plan')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('active'); // active, archived
            $table->timestamps();

            $table->index(['tenant_id', 'position_id', 'status']);
        });

        // 6. Succession Candidates & Talent Bench
        Schema::create('succession_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('succession_plan_id')->constrained('succession_plans')->cascadeOnDelete();
            $table->foreignId('candidate_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('readiness_level', 30)->default('ready_1_year'); // ready_now, ready_1_year, ready_2_plus_years
            $table->text('strengths')->nullable();
            $table->text('development_needs')->nullable();
            $table->string('status', 30)->default('active'); // active, promoted, dropped
            $table->timestamps();

            $table->index(['tenant_id', 'succession_plan_id']);
            $table->unique(['tenant_id', 'succession_plan_id', 'candidate_employee_id'], 'succ_candidate_unique');
        });

        // 7. Background Cron Execution Logs
        Schema::create('cron_execution_logs', function (Blueprint $table) {
            $table->id();
            $table->string('command_name', 100);
            $table->string('status', 30)->default('success'); // success, failed
            $table->unsignedInteger('items_processed')->default(0);
            $table->text('summary')->nullable();
            $table->dateTime('executed_at');
            $table->timestamps();

            $table->index(['command_name', 'executed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cron_execution_logs');
        Schema::dropIfExists('succession_candidates');
        Schema::dropIfExists('succession_plans');
        Schema::dropIfExists('employee_skills');
        Schema::dropIfExists('position_skill_requirements');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('skill_categories');
    }
};

