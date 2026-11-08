<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_grievances', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_grievances', 'investigation_notes')) {
                $table->text('investigation_notes')->nullable()->after('resolution_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_grievances', function (Blueprint $table) {
            if (Schema::hasColumn('employee_grievances', 'investigation_notes')) {
                $table->dropColumn('investigation_notes');
            }
        });
    }
};
