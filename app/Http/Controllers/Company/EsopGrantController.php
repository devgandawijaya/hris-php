<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EsopGrant;
use App\Services\Financial\EsopVestingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class EsopGrantController extends Controller
{
    public function __construct(
        protected EsopVestingService $esopService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $grants = EsopGrant::with(['employee.department', 'employee.position'])
            ->where('company_id', $company->id)
            ->latest()
            ->paginate(15);

        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('first_name')->get();

        $stats = [
            'total_shares_pool' => EsopGrant::where('company_id', $company->id)->sum('total_shares_granted'),
            'total_vested_shares' => EsopGrant::where('company_id', $company->id)->sum('vested_shares_count'),
            'total_exercised_shares' => EsopGrant::where('company_id', $company->id)->sum('exercised_shares_count'),
            'total_active_grants' => EsopGrant::where('company_id', $company->id)->where('status', '!=', 'cancelled')->count(),
        ];

        return view('company.esop.index', compact('company', 'grants', 'employees', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'grant_date' => 'required|date',
            'total_shares_granted' => 'required|integer|min:1',
            'strike_price_per_share' => 'required|numeric|min:0',
            'fair_market_value' => 'required|numeric|min:0',
            'vesting_schedule_years' => 'required|integer|min:1|max:10',
            'cliff_months' => 'required|integer|min:0|max:36',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        $this->esopService->issueGrant(
            $employee,
            (int) $validated['total_shares_granted'],
            (float) $validated['strike_price_per_share'],
            (float) $validated['fair_market_value'],
            $validated['grant_date'],
            (int) $validated['vesting_schedule_years'],
            (int) $validated['cliff_months']
        );

        return redirect()->route('esop.index')->with('success', 'Grant Opsi Saham (ESOP) berhasil diterbitkan untuk karyawan terpilih.');
    }

    public function refreshVesting(EsopGrant $grant): RedirectResponse
    {
        $this->esopService->updateVestingProgress($grant);
        return back()->with('success', "Progres vesting ESOP {$grant->grant_number} berhasil diperbarui.");
    }

    public function exercise(Request $request, EsopGrant $grant): RedirectResponse
    {
        $validated = $request->validate([
            'shares_to_exercise' => 'required|integer|min:1',
        ]);

        try {
            $result = $this->esopService->exerciseOptions($grant, (int) $validated['shares_to_exercise']);

            return back()->with('success', sprintf(
                'Berhasil exercise %s lembar saham. Total biaya eksekusi: Rp %s (Potensi Capital Gain: Rp %s).',
                number_format($result['shares_exercised']),
                number_format($result['total_exercise_cost'], 0, ',', '.'),
                number_format($result['intrinsic_gain'], 0, ',', '.')
            ));
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
