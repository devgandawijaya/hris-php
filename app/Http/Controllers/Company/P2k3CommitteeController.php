<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\P2k3Committee;
use App\Models\P2k3QuarterlyReport;
use App\Services\Compliance\P2k3DisnakerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class P2k3CommitteeController extends Controller
{
    public function __construct(
        protected P2k3DisnakerService $p2k3Service
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $activeWorkforce = Employee::where('company_id', $company->id)->where('status', 'active')->count();
        $complianceAssessment = $this->p2k3Service->assessMandatoryCompliance($company, $activeWorkforce, true);

        $committee = P2k3Committee::with(['quarterlyReports' => function ($q) {
            $q->latest('reporting_year')->latest('quarter');
        }])->where('company_id', $company->id)->first();

        $quarterlyReports = $committee ? $committee->quarterlyReports : collect();

        return view('company.p2k3.index', compact(
            'company',
            'committee',
            'complianceAssessment',
            'quarterlyReports',
            'activeWorkforce'
        ));
    }

    public function storeCommittee(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'decree_number' => 'required|string|max:100',
            'chairman_name' => 'required|string|max:100',
            'secretary_ahli_k3_name' => 'required|string|max:100',
            'ahli_k3_skp_number' => 'required|string|max:100',
            'formation_date' => 'required|date',
            'disnaker_ratification_number' => 'nullable|string|max:100',
            'total_committee_members' => 'required|integer|min:2',
        ]);

        try {
            $this->p2k3Service->registerCommittee(
                $company,
                $validated['decree_number'],
                $validated['chairman_name'],
                $validated['secretary_ahli_k3_name'],
                $validated['ahli_k3_skp_number'],
                $validated['formation_date'],
                $validated['disnaker_ratification_number'] ?? null,
                (int) $validated['total_committee_members']
            );

            return redirect()->route('p2k3.index')->with('success', 'Struktur Panitia Pembina K3 (P2K3) berhasil didaftarkan dan divalidasi SKP Kemnaker.');
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function storeQuarterlyReport(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'p2k3_committee_id' => 'required|exists:p2k3_committees,id',
            'reporting_year' => 'required|integer|min:2020|max:2050',
            'quarter' => 'required|string|in:Q1,Q2,Q3,Q4',
            'workplace_inspections_count' => 'required|integer|min:0',
            'safety_trainings_count' => 'required|integer|min:0',
            'incident_summary' => 'nullable|string|max:1000',
            'disnaker_receipt_number' => 'nullable|string|max:100',
        ]);

        $committee = P2k3Committee::findOrFail($validated['p2k3_committee_id']);

        $this->p2k3Service->submitQuarterlyReport(
            $committee,
            (int) $validated['reporting_year'],
            $validated['quarter'],
            (int) $validated['workplace_inspections_count'],
            (int) $validated['safety_trainings_count'],
            $validated['incident_summary'] ?? 'Zero accident di tempat kerja.',
            $validated['disnaker_receipt_number'] ?? null
        );

        return redirect()->route('p2k3.index')->with('success', "Laporan Triwulan {$validated['quarter']}-{$validated['reporting_year']} P2K3 berhasil disimpan / dilaporkan ke Disnaker.");
    }

    public function printReport(P2k3QuarterlyReport $report): View
    {
        $report->load(['committee.company']);
        return view('company.p2k3.print_report', compact('report'));
    }
}
