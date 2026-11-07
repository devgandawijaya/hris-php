<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. ESG Ketenagakerjaan & Sustainability Snapshot (POJK No. 51/POJK.03/2017 & GRI Standards 401/404/405)
        Schema::create('esg_metric_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->unsignedSmallInteger('reporting_year');
            $table->decimal('gender_pay_gap_ratio', 6, 4)->default(1.0000); // Rasio upah perempuan terhadap laki-laki pada grade setara
            $table->decimal('female_workforce_pct', 5, 2)->default(0.00); // % perempuan di seluruh angkatan kerja
            $table->decimal('female_leadership_pct', 5, 2)->default(0.00); // % perempuan di tingkat manajerial & pimpinan
            $table->decimal('board_diversity_pct', 5, 2)->default(0.00); // % keterwakilan perempuan di Dewan Direksi & Komisaris
            $table->decimal('avg_training_hours_per_employee', 6, 2)->default(0.00); // Jam pelatihan tahunan rata-rata (GRI 404-1)
            $table->decimal('annual_turnover_rate', 5, 2)->default(0.00); // Rasio turnover karyawan (GRI 401-1)
            $table->decimal('lost_time_injury_frequency_rate', 6, 2)->default(0.00); // LTIFR per sejuta jam kerja (GRI 403-9)
            $table->string('status', 30)->default('published'); // draft, published, audited
            $table->text('sustainability_notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'reporting_year']);
        });

        // 2. Profil Komuter Harian Karyawan (Scope 3 GHG Commuting Emissions GHG Protocol)
        Schema::create('employee_commute_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->decimal('home_to_office_distance_km', 6, 2); // Jarak satu arah rumah - kantor
            $table->string('primary_commute_mode', 50); // car_gasoline, motorcycle_gasoline, electric_vehicle, public_transit, bicycle_walking
            $table->unsignedTinyInteger('weekly_office_days')->default(3); // Hari kerja di kantor (WFO) per minggu
            $table->unsignedTinyInteger('weekly_remote_days')->default(2); // Hari kerja remote/WFH per minggu
            $table->decimal('weekly_emissions_kg_co2', 8, 2)->default(0.00); // Emisi aktual mingguan
            $table->decimal('weekly_avoided_emissions_kg_co2', 8, 2)->default(0.00); // Emisi dihindari berkat WFH
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id']);
        });

        // 3. Audit Agregasi Emisi Karbon Komuter Perusahaan (Scope 3)
        Schema::create('commute_emission_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->unsignedSmallInteger('audit_year');
            $table->unsignedInteger('participating_employees_count')->default(0);
            $table->decimal('total_annual_commute_distance_km', 12, 2)->default(0.00);
            $table->decimal('gross_emissions_ton_co2e', 10, 2)->default(0.00); // Total emisi jika seluruhnya WFO
            $table->decimal('avoided_emissions_wfh_ton_co2e', 10, 2)->default(0.00); // Emisi dihemat berkat FWA/WFH
            $table->decimal('net_commute_emissions_ton_co2e', 10, 2)->default(0.00); // Emisi riil setelah WFH
            $table->string('status', 30)->default('completed'); // draft, completed, verified
            $table->timestamps();

            $table->index(['tenant_id', 'audit_year']);
        });

        // 4. Audit Ergonomi Tempat Kerja (Permenaker No. 5 Tahun 2018 K3 Lingkungan Kerja)
        Schema::create('ergonomic_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->date('assessment_date');
            $table->string('workstation_type', 100)->default('Office Desk & Monitor Workstation');
            $table->unsignedSmallInteger('lighting_lux_level')->default(350); // Standar Permenaker 5/2018: min 300 Lux
            $table->boolean('chair_lumbar_support_compliant')->default(true);
            $table->boolean('monitor_eye_level_compliant')->default(true);
            $table->boolean('keyboard_wrist_rest_compliant')->default(true);
            $table->string('posture_risk_level', 30)->default('low'); // low, moderate, high (metode RULA / REBA)
            $table->decimal('ergonomic_compliance_score', 5, 2)->default(85.00); // 0 - 100
            $table->text('corrective_actions')->nullable();
            $table->string('status', 30)->default('completed'); // draft, completed, action_required
            $table->timestamps();

            $table->index(['tenant_id', 'assessment_date']);
        });

        // 5. Survei Beban Kerja Digital & Stres Kognitif (Digital Fatigue Index)
        Schema::create('digital_stress_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('survey_date');
            $table->decimal('avg_daily_screen_hours', 4, 1)->default(8.0); // Rata-rata jam terpapar layar monitor
            $table->unsignedTinyInteger('meeting_fatigue_score')->default(3); // Skala 1 - 5 (Zoom fatigue)
            $table->unsignedTinyInteger('after_hours_communication_score')->default(2); // Skala 1 - 5 (gangguan pesan WA/Slack di luar jam kerja)
            $table->unsignedTinyInteger('eyestrain_headache_frequency')->default(2); // Skala 1 - 5
            $table->decimal('digital_burnout_risk_index', 5, 2)->default(25.00); // 0 - 100
            $table->string('risk_classification', 30)->default('low'); // low, moderate, high, severe
            $table->text('coping_recommendation')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'employee_id', 'survey_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_stress_surveys');
        Schema::dropIfExists('ergonomic_assessments');
        Schema::dropIfExists('commute_emission_audits');
        Schema::dropIfExists('employee_commute_profiles');
        Schema::dropIfExists('esg_metric_snapshots');
    }
};
