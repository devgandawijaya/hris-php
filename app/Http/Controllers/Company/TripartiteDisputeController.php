<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Employee;
use App\Models\BipartiteMeeting;
use App\Models\TripartiteIndustrialDispute;
use App\Services\Relations\IndustrialDisputeService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class TripartiteDisputeController extends Controller
{
    public function __construct(
        protected IndustrialDisputeService $disputeService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $disputes = TripartiteIndustrialDispute::with(['employee.department', 'bipartiteMeeting'])
            ->where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->latest('mediation_date')
            ->paginate(10);

        $employees = Employee::where('company_id', $company->id)->orderBy('first_name')->get();
        $bipartiteMeetings = BipartiteMeeting::where('tenant_id', $tenantId)->where('company_id', $company->id)->latest('meeting_date')->get();

        return view('company.disputes.index', compact('company', 'disputes', 'employees', 'bipartiteMeetings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'dispute_type' => 'required|in:perselisihan_hak,perselisihan_kepentingan,perselisihan_phk,perselisihan_antar_serikat',
            'employee_id' => 'nullable|exists:employees,id',
            'employee_name_external' => 'nullable|string|max:150',
            'bipartite_meeting_id' => 'nullable|exists:bipartite_meetings,id',
            'disnaker_mediator_name' => 'required|string|max:100',
            'disnaker_office' => 'required|string|max:150',
            'mediation_date' => 'required|date',
            'dispute_chronology' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $dispute = $this->disputeService->recordDispute($company, $validated);

            return back()->with('success', "Kasus Perselisihan Hubungan Industrial ({$dispute->case_number}) berhasil dicatat.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function recordRecommendation(Request $request, TripartiteIndustrialDispute $dispute): RedirectResponse
    {
        $validated = $request->validate([
            'mediator_written_recommendation' => 'required|string',
        ]);

        $this->disputeService->recordMediatorRecommendation($dispute, $validated['mediator_written_recommendation']);

        return back()->with('success', "Anjuran Tertulis Mediator Disnaker berhasil dicatat.");
    }

    public function recordResponse(Request $request, TripartiteIndustrialDispute $dispute): RedirectResponse
    {
        $validated = $request->validate([
            'company_response' => 'required|in:accepted,rejected',
            'worker_response' => 'required|in:accepted,rejected',
            'joint_agreement_number' => 'nullable|string|max:100',
        ]);

        $updated = $this->disputeService->recordPartyResponses(
            $dispute,
            $validated['company_response'],
            $validated['worker_response'],
            $validated['joint_agreement_number'] ?? null
        );

        $msg = ($updated->settlement_status === 'peaceful_agreement_pb')
            ? "Kedua belah pihak menerima anjuran mediator. Berkas Perjanjian Bersama (PB) resmi tercapai."
            : "Salah satu pihak menolak anjuran mediator. Kasus dieskalasi ke tahap Gugatan Pengadilan Hubungan Industrial (PHI).";

        return back()->with('success', $msg);
    }

    public function printDispute(TripartiteIndustrialDispute $dispute): View
    {
        $dispute->load(['company', 'employee.department', 'bipartiteMeeting']);
        return view('company.disputes.print_dispute', compact('dispute'));
    }
}
