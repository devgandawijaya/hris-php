<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\CompanyRegulation;
use App\Services\Compliance\CompanyRegulationService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CompanyRegulationController extends Controller
{
    public function __construct(
        protected CompanyRegulationService $regulationService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $regulations = CompanyRegulation::where('company_id', $company->id)
            ->latest('effective_end_date')
            ->get();

        $alerts = $this->regulationService->getRenewalAlerts($company);

        return view('company.regulations.index', compact('company', 'regulations', 'alerts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'document_type' => ['required', 'in:peraturan_perusahaan_pp,perjanjian_kerja_bersama_pkb'],
            'title' => ['required', 'string', 'max:150'],
            'reg_number' => ['nullable', 'string', 'max:100'],
            'effective_start_date' => ['required', 'date'],
            'effective_end_date' => ['required', 'date', 'after:effective_start_date'],
            'disnaker_office' => ['nullable', 'string', 'max:150'],
            'summary_provisions' => ['nullable', 'string'],
        ]);

        $regulation = $this->regulationService->registerRegulation($company, $validated);

        return back()->with('success', "Dokumen {$regulation->title} berhasil didaftarkan ke sistem.");
    }
}
