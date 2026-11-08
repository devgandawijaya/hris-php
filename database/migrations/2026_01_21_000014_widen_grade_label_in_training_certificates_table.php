<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_certificates', function (Blueprint $table) {
            $table->string('grade_label', 100)->default('Lulus / Passed')->change();
        });
    }

    public function down(): void
    {
        Schema::table('training_certificates', function (Blueprint $table) {
            $table->string('grade_label', 30)->default('Lulus / Passed')->change();
        });
    }
};
