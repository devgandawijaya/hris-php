<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Position;
use App\Models\Branch;
use App\Tenancy\TenantManager;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OrganizationController extends Controller
{
    public function __construct(protected TenantManager $tenantManager) {}

    public function index(): View
    {
        $departments = Department::with(['parent', 'positions', 'employees'])->get();
        $positions = Position::with(['department', 'employees'])->get();
        $branches = Branch::all();

        return view('company.organization.index', compact('departments', 'positions', 'branches'));
    }

    public function storeDepartment(Request $request): RedirectResponse
    {
        $tenantId = $this->tenantManager->getActiveTenantId();
        $company = $this->tenantManager->getCompany();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:departments,id',
        ]);

        $validated['tenant_id'] = $tenantId;
        $validated['company_id'] = $company?->id ?? 1;

        Department::create($validated);

        return back()->with('success', "Departemen {$validated['name']} berhasil ditambahkan.");
    }

    public function deleteDepartment(int $id): RedirectResponse
    {
        $dept = Department::findOrFail($id);
        if ($dept->employees()->exists()) {
            return back()->with('error', 'Departemen ini tidak dapat dihapus karena masih memiliki data karyawan aktif.');
        }
        $dept->delete();

        return back()->with('success', 'Departemen berhasil dihapus.');
    }

    public function storePosition(Request $request): RedirectResponse
    {
        $tenantId = $this->tenantManager->getActiveTenantId();

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'department_id' => 'required|exists:departments,id',
            'job_level' => 'required|string|max:50',
        ]);

        $validated['tenant_id'] = $tenantId;

        Position::create($validated);

        return back()->with('success', "Jabatan / Posisi {$validated['title']} berhasil ditambahkan.");
    }

    public function deletePosition(int $id): RedirectResponse
    {
        $pos = Position::findOrFail($id);
        if ($pos->employees()->exists()) {
            return back()->with('error', 'Posisi ini masih digunakan oleh karyawan.');
        }
        $pos->delete();

        return back()->with('success', 'Posisi jabatan berhasil dihapus.');
    }

    public function storeBranch(Request $request): RedirectResponse
    {
        $tenantId = $this->tenantManager->getActiveTenantId();
        $company = $this->tenantManager->getCompany();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:10|max:5000',
        ]);

        $validated['tenant_id'] = $tenantId;
        $validated['company_id'] = $company?->id ?? 1;

        Branch::create($validated);

        return back()->with('success', "Kantor Cabang {$validated['name']} dengan radius geofencing {$validated['radius_meters']}m berhasil disimpan.");
    }

    public function deleteBranch(int $id): RedirectResponse
    {
        Branch::findOrFail($id)->delete();
        return back()->with('success', 'Kantor cabang berhasil dihapus.');
    }
}

