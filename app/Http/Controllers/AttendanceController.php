<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRecord;
use App\Models\AttendanceCorrection;
use App\Models\Department;
use App\Models\Employee;
use App\Tenancy\TenantManager;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function __construct(protected TenantManager $tenantManager) {}

    public function index(Request $request): View
    {
        $selectedDate = $request->input('date', now()->toDateString());

        $query = AttendanceRecord::with(['employee.department', 'employee.position'])
            ->where('date', $selectedDate);

        if ($request->filled('department_id')) {
            $deptId = $request->department_id;
            $query->whereHas('employee', function ($q) use ($deptId) {
                $q->where('department_id', $deptId);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $records = $query->latest('clock_in')->paginate(15)->withQueryString();

        $departments = Department::all();

        // Calculate summary stats for selected date
        $allDayRecords = AttendanceRecord::where('date', $selectedDate)->get();
        $totalEmployees = Employee::where('status', 'active')->count();
        $presentCount = $allDayRecords->where('status', 'present')->count();
        $lateCount = $allDayRecords->where('status', 'late')->count();
        $absentCount = max(0, $totalEmployees - ($presentCount + $lateCount));

        return view('company.attendance.index', compact(
            'records',
            'departments',
            'selectedDate',
            'presentCount',
            'lateCount',
            'absentCount',
            'totalEmployees'
        ));
    }

    public function corrections(): View
    {
        $corrections = AttendanceCorrection::with('employee.department')
            ->latest('date')
            ->paginate(15);

        return view('company.attendance.corrections', compact('corrections'));
    }

    public function approveCorrection(int $id): RedirectResponse
    {
        $correction = AttendanceCorrection::findOrFail($id);

        // Update or create actual attendance record
        $record = AttendanceRecord::firstOrNew([
            'tenant_id' => $correction->tenant_id,
            'employee_id' => $correction->employee_id,
            'date' => $correction->date,
        ]);

        if ($correction->corrected_clock_in) {
            $record->clock_in = $correction->corrected_clock_in;
        }
        if ($correction->corrected_clock_out) {
            $record->clock_out = $correction->corrected_clock_out;
        }

        $record->status = 'present';
        $record->save();

        $correction->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Permohonan koreksi presensi berhasil disetujui dan data presensi telah diperbarui.');
    }

    public function rejectCorrection(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $correction = AttendanceCorrection::findOrFail($id);
        $correction->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Permohonan koreksi presensi telah ditolak.');
    }

    public function export(Request $request): StreamedResponse
    {
        $date = $request->input('date', now()->toDateString());
        $records = AttendanceRecord::with(['employee.department'])
            ->where('date', $date)
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"rekap_presensi_{$date}.csv\"",
        ];

        return response()->stream(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['NIK Karyawan', 'Nama Lengkap', 'Departemen', 'Tanggal', 'Clock In', 'Clock Out', 'Status', 'Menit Terlambat', 'Jam Kerja']);

            foreach ($records as $r) {
                fputcsv($handle, [
                    $r->employee?->employee_number,
                    $r->employee?->full_name,
                    $r->employee?->department?->name ?? '-',
                    $r->date->format('Y-m-d'),
                    $r->clock_in ?? '-',
                    $r->clock_out ?? '-',
                    $r->status,
                    $r->late_minutes,
                    $r->work_hours,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
