<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\EmployeeGrievance;
use App\Services\EmployeeRelations\GrievanceService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GrievanceController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected GrievanceService $grievanceService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $status = $request->query('status');

        $query = EmployeeGrievance::with(['employee.department', 'resolvedBy'])
            ->where('tenant_id', $tenant->id)
            ->latest();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $grievances = $query->paginate(15);

        $stats = [
            'total' => EmployeeGrievance::where('tenant_id', $tenant->id)->count(),
            'pending' => EmployeeGrievance::where('tenant_id', $tenant->id)->whereIn('status', ['submitted', 'in_review', 'investigating'])->count(),
            'resolved' => EmployeeGrievance::where('tenant_id', $tenant->id)->where('status', 'resolved')->count(),
        ];

        return view('company.grievances.index', compact('grievances', 'stats'));
    }

    public function updateStatus(Request $request, EmployeeGrievance $grievance): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:in_review,investigating,closed',
            'notes' => 'nullable|string',
        ]);

        $this->grievanceService->updateStatus($grievance, $validated['status'], $validated['notes'] ?? null);

        return back()->with('success', 'Status pengaduan karyawan berhasil diperbarui.');
    }

    public function resolve(Request $request, EmployeeGrievance $grievance): RedirectResponse
    {
        $validated = $request->validate([
            'resolution_notes' => 'required|string',
        ]);

        $this->grievanceService->resolveGrievance($grievance, Auth::user(), $validated['resolution_notes']);

        return back()->with('success', 'Pengaduan karyawan telah berhasil diselesaikan.');
    }
}
