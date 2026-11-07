<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\BipartiteMeeting;
use App\Services\Relations\BipartiteService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class BipartiteController extends Controller
{
    public function __construct(
        protected BipartiteService $bipartiteService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $meetings = BipartiteMeeting::where('company_id', $company->id)
            ->latest('meeting_date')
            ->paginate(10);

        return view('company.bipartite.index', compact('company', 'meetings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'meeting_date' => ['required', 'date'],
            'meeting_title' => ['required', 'string', 'max:200'],
            'agenda_topics' => ['required', 'string'],
            'employer_representatives' => ['required', 'string'], // Comma-separated or newline
            'worker_representatives' => ['required', 'string'],
            'meeting_minutes' => ['required', 'string'],
            'action_items' => ['nullable', 'string'],
        ]);

        $employerReps = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $validated['employer_representatives'])));
        $workerReps = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $validated['worker_representatives'])));

        $validated['employer_representatives'] = $employerReps;
        $validated['worker_representatives'] = $workerReps;

        try {
            $meeting = $this->bipartiteService->recordMeeting($company, $validated);
            return back()->with('success', "Risalah Sidang Bipartit [{$meeting->meeting_number}] berhasil didokumentasikan.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function reportDisnaker(BipartiteMeeting $meeting): RedirectResponse
    {
        $this->bipartiteService->markReportedToDisnaker($meeting);
        return back()->with('success', "Laporan periodik 6 bulanan LKS Bipartit [{$meeting->meeting_number}] telah dilaporkan ke Disnaker.");
    }
}
