<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Extend subscription_plans with explicit feature flag columns
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->boolean('has_api_access')->default(false)->after('storage_limit_mb');
            $table->boolean('has_whatsapp')->default(false)->after('has_api_access');
            $table->boolean('has_custom_workflows')->default(false)->after('has_whatsapp');
            $table->boolean('has_bank_disbursal')->default(false)->after('has_custom_workflows');
            $table->boolean('has_ebupot_export')->default(false)->after('has_bank_disbursal');
            $table->boolean('has_digital_contracts')->default(false)->after('has_ebupot_export');
        });

        // 2. Extend cms_contact_messages with CRM lead management status & notes
        Schema::table('cms_contact_messages', function (Blueprint $table) {
            $table->string('status', 30)->default('new')->after('is_read'); // new, contacted, qualified, converted, closed
            $table->text('notes')->nullable()->after('status');
            $table->foreignId('contacted_by_user_id')->nullable()->constrained('users')->nullOnDelete()->after('notes');
            $table->timestamp('contacted_at')->nullable()->after('contacted_by_user_id');

            $table->index('status');
        });

        // 3. Multi-channel Notification Templates
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('code', 50); // e.g. PKWT_EXPIRY, PAYSLIP_READY, LEAVE_APPROVED, SP_ISSUED
            $table->string('name', 100);
            $table->string('channel', 20)->default('whatsapp'); // whatsapp, email
            $table->string('category', 50)->default('general'); // contract, payroll, attendance, disciplinary, general
            $table->string('subject')->nullable(); // For email
            $table->text('body_template');
            $table->json('variables')->nullable(); // Available placeholders e.g. ["employee_name", "company_name"]
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'code', 'channel']);
        });

        // 4. Multi-channel Notification Dispatch Logs
        Schema::create('notification_dispatch_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('notification_templates')->nullOnDelete();
            $table->string('channel', 20)->default('whatsapp'); // whatsapp, email
            $table->string('recipient_name', 150);
            $table->string('recipient_target', 150); // Phone number or email address
            $table->string('subject')->nullable();
            $table->text('message_preview');
            $table->string('status', 30)->default('sent'); // pending, sent, simulated, failed
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('dispatched_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'channel', 'status']);
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_dispatch_logs');
        Schema::dropIfExists('notification_templates');

        Schema::table('cms_contact_messages', function (Blueprint $table) {
            $table->dropForeign(['contacted_by_user_id']);
            $table->dropColumn(['status', 'notes', 'contacted_by_user_id', 'contacted_at']);
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn([
                'has_api_access',
                'has_whatsapp',
                'has_custom_workflows',
                'has_bank_disbursal',
                'has_ebupot_export',
                'has_digital_contracts',
            ]);
        });
    }
};
