<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Tenancy\TenantManager;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class LeaveManagementController extends Controller
{
    public function __construct(protected TenantManager $tenantManager) {}

    public function index(Request $request): View
    {
        $query = LeaveRequest::with(['employee.department', 'leaveType']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest('start_date')->paginate(15)->withQueryString();
        $types = LeaveType::all();

        return view('company.leave.index', compact('requests', 'types'));
    }

    public function approve(int $id): RedirectResponse
    {
        $leave = LeaveRequest::with(['employee', 'leaveType'])->findOrFail($id);
        $leave->update(['status' => 'approved']);

        // Update leave balance if annual leave
        $balance = LeaveBalance::firstOrNew([
            'tenant_id' => $leave->tenant_id,
            'employee_id' => $leave->employee_id,
            'leave_type_id' => $leave->leave_type_id,
            'year' => date('Y', strtotime($leave->start_date)),
        ]);

        if (!$balance->exists) {
            $balance->total_allocated = $leave->leaveType->days_allowed ?? 12;
            $balance->used = 0;
            $balance->remaining = $balance->total_allocated;
        }

        $balance->used += $leave->total_days;
        $balance->remaining = max(0, $balance->total_allocated - $balance->used);
        $balance->save();

        return back()->with('success', "Permohonan cuti karyawan {$leave->employee?->full_name} ({$leave->total_days} hari) berhasil disetujui.");
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $leave = LeaveRequest::findOrFail($id);
        $leave->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', "Permohonan cuti telah ditolak.");
    }

    public function storeType(Request $request): RedirectResponse
    {
        $tenantId = $this->tenantManager->getActiveTenantId();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'days_allowed' => 'required|integer|min:1|max:365',
            'is_paid' => 'nullable|boolean',
        ]);

        $validated['tenant_id'] = $tenantId;
        $validated['is_paid'] = $request->boolean('is_paid', true);

        LeaveType::create($validated);

        return back()->with('success', "Jenis cuti {$validated['name']} berhasil ditambahkan.");
    }

    public function deleteType(int $id): RedirectResponse
    {
        $type = LeaveType::findOrFail($id);
        if ($type->leaveRequests()->exists()) {
            return back()->with('error', 'Jenis cuti tidak dapat dihapus karena sudah memiliki riwayat pengajuan cuti.');
        }
        $type->delete();

        return back()->with('success', 'Jenis cuti berhasil dihapus.');
    }
}

