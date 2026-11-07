<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\OutsourcingVendor;
use App\Models\OutsourcingContract;
use App\Services\Compliance\OutsourcingComplianceService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class OutsourcingVendorController extends Controller
{
    public function __construct(
        protected OutsourcingComplianceService $outsourcingService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $vendors = OutsourcingVendor::with(['contracts.workerAssignments'])
            ->where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->latest()
            ->paginate(10);

        return view('company.outsourcing.index', compact('company', 'vendors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'nib_oss_license' => 'required|string|max:100',
            'legal_entity_type' => 'nullable|string|max:50',
            'disnaker_registration_number' => 'nullable|string|max:100',
            'pic_name' => 'required|string|max:100',
            'pic_contact' => 'required|string|max:100',
            'management_fee_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $this->outsourcingService->registerVendor($company, $validated);

            return redirect()->route('outsourcing.index')->with('success', 'Rekanan Alih Daya (Vendor) berhasil didaftarkan sesuai PP No. 35/2021.');
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(string $id): View
    {
        $tenantId = session('active_tenant_id');
        $vendor = OutsourcingVendor::with(['contracts.workerAssignments'])->where('tenant_id', $tenantId)->findOrFail($id);

        return view('company.outsourcing.show', compact('vendor'));
    }

    public function storeContract(Request $request, string $vendorId): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $vendor = OutsourcingVendor::where('tenant_id', $tenantId)->findOrFail($vendorId);

        $validated = $request->validate([
            'contract_number' => 'required|string|max:100',
            'service_type' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'headcount_quota' => 'required|integer|min:1',
            'monthly_billing_amount' => 'nullable|numeric|min:0',
            'tupe_protection_guarantee' => 'nullable|boolean',
        ]);

        $this->outsourcingService->createContract($vendor, $validated);

        return back()->with('success', 'Kontrak SPK Alih Daya berhasil dibuat dengan jaminan klausul TUPE.');
    }
}
