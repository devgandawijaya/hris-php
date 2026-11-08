<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_courses', function (Blueprint $table) {
            $table->dateTime('start_date')->nullable()->change();
            $table->dateTime('end_date')->nullable()->change();
            if (!Schema::hasColumn('training_courses', 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            }
        });

        Schema::table('training_enrollments', function (Blueprint $table) {
            if (!Schema::hasColumn('training_enrollments', 'enrolled_at')) {
                $table->dateTime('enrolled_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('training_courses', function (Blueprint $table) {
            $table->dateTime('start_date')->nullable(false)->change();
            $table->dateTime('end_date')->nullable(false)->change();
            if (Schema::hasColumn('training_courses', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }
        });

        Schema::table('training_enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('training_enrollments', 'enrolled_at')) {
                $table->dropColumn('enrolled_at');
            }
        });
    }
};
