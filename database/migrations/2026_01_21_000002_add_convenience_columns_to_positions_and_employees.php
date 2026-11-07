<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('tenant_id')->constrained('companies')->nullOnDelete();
            $table->string('name')->nullable()->after('title');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('nik', 50)->nullable()->after('nik_ktp');
            $table->string('employment_type', 50)->nullable()->after('employment_status');
        });
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'name']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['nik', 'employment_type']);
        });
    }
};
