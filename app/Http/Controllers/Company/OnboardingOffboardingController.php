<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyAsset;
use App\Models\Employee;
use App\Models\ExitInterview;
use App\Models\OnboardingOffboardingChecklist;
use App\Services\Talent\OnboardingOffboardingService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OnboardingOffboardingController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected OnboardingOffboardingService $onboardingService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $type = $request->query('type', 'onboarding');

        $checklists = OnboardingOffboardingChecklist::with(['employee.department', 'assignedUser'])
            ->where('tenant_id', $tenant->id)
            ->where('type', $type)
            ->latest()
            ->paginate(15);

        $assets = CompanyAsset::with('assignedEmployee')
            ->where('tenant_id', $tenant->id)
            ->get();

        $exitInterviews = ExitInterview::with(['employee.department', 'interviewer'])
            ->where('tenant_id', $tenant->id)
            ->latest('interview_date')
            ->get();

        $employees = Employee::where('tenant_id', $tenant->id)->where('status', 'active')->get();

        $stats = [
            'total_onboarding_pending' => OnboardingOffboardingChecklist::where('tenant_id', $tenant->id)->where('type', 'onboarding')->where('is_completed', false)->count(),
            'total_offboarding_pending' => OnboardingOffboardingChecklist::where('tenant_id', $tenant->id)->where('type', 'offboarding')->where('is_completed', false)->count(),
            'total_assets' => $assets->count(),
            'assigned_assets' => $assets->where('status', 'assigned')->count(),
        ];

        return view('company.onboarding.index', compact('checklists', 'assets', 'exitInterviews', 'employees', 'type', 'stats'));
    }

    public function initialize(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:onboarding,offboarding',
        ]);

        $employee = Employee::where('tenant_id', $tenant->id)->findOrFail($validated['employee_id']);
        $currentUser = Auth::user();

        if ($validated['type'] === 'onboarding') {
            $this->onboardingService->initializeOnboarding($employee, $currentUser);
            $msg = "Daftar periksa orientasi (Onboarding) untuk {$employee->full_name} berhasil diinisialisasi.";
        } else {
            $this->onboardingService->initializeOffboarding($employee, $currentUser);
            $msg = "Daftar periksa pemutusan/pengunduran diri (Offboarding) untuk {$employee->full_name} berhasil diinisialisasi.";
        }

        return back()->with('success', $msg);
    }

    public function completeTask(OnboardingOffboardingChecklist $task): RedirectResponse
    {
        $this->onboardingService->completeTask($task);
        return back()->with('success', 'Tugas checklist telah ditandai Selesai.');
    }

    public function storeAsset(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'asset_code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'category' => 'required|in:laptop,phone,vehicle,access_card,other',
            'serial_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        CompanyAsset::create([
            'tenant_id' => $tenant->id,
            'asset_code' => $validated['asset_code'],
            'name' => $validated['name'],
            'category' => $validated['category'],
            'serial_number' => $validated['serial_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'available',
        ]);

        return back()->with('success', "Aset '{$validated['name']}' ({$validated['asset_code']}) berhasil didaftarkan ke inventaris.");
    }

    public function assignAsset(Request $request, CompanyAsset $asset): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $employee = Employee::where('tenant_id', $tenant->id)->findOrFail($validated['employee_id']);
        $this->onboardingService->assignAsset($asset, $employee);

        return back()->with('success', "Aset '{$asset->name}' berhasil diserahkan ke {$employee->full_name}.");
    }

    public function returnAsset(CompanyAsset $asset): RedirectResponse
    {
        $this->onboardingService->returnAsset($asset);
        return back()->with('success', "Aset '{$asset->name}' telah dikembalikan ke inventaris kantor.");
    }

    public function recordExitInterview(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'interview_date' => 'required|date',
            'reason_for_leaving' => 'required|in:new_job,compensation,career_change,personal,relocate,company_culture,other',
            'feedback' => 'nullable|string',
            'willing_to_recommend' => 'nullable|boolean',
        ]);

        $employee = Employee::where('tenant_id', $tenant->id)->findOrFail($validated['employee_id']);
        $currentUser = Auth::user();

        $this->onboardingService->recordExitInterview(
            $employee,
            $validated,
            $currentUser
        );

        return back()->with('success', "Hasil Exit Interview untuk {$employee->full_name} berhasil disimpan.");
    }
}
