<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('entity_type', 50); // leave, overtime, reimbursement, payroll
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'entity_type']);
        });

        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->cascadeOnDelete();
            $table->unsignedInteger('step_order');
            $table->string('approver_role_slug', 50);
            $table->timestamps();

            $table->unique(['workflow_id', 'step_order']);
        });

        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('workflow_id')->constrained('workflows')->cascadeOnDelete();
            $table->string('approvable_type', 150);
            $table->unsignedBigInteger('approvable_id');
            $table->unsignedInteger('current_step')->default(1);
            $table->string('status', 30)->default('pending'); // pending, approved, rejected
            $table->timestamps();

            $table->index(['tenant_id', 'approvable_type', 'approvable_id']);
        });

        Schema::create('approval_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_id')->constrained('approval_requests')->cascadeOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 30); // approved, rejected, delegated
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->index('approval_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_histories');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflows');
    }
};

