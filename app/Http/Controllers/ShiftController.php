<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\Holiday;
use App\Tenancy\TenantManager;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ShiftController extends Controller
{
    public function __construct(protected TenantManager $tenantManager) {}

    public function index(): View
    {
        $shifts = Shift::all();
        $holidays = Holiday::orderBy('date')->get();

        return view('company.attendance.shifts', compact('shifts', 'holidays'));
    }

    public function storeShift(Request $request): RedirectResponse
    {
        $tenantId = $this->tenantManager->getActiveTenantId();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'is_night_shift' => 'nullable|boolean',
        ]);

        $validated['tenant_id'] = $tenantId;
        $validated['is_night_shift'] = $request->boolean('is_night_shift');

        Shift::create($validated);

        return back()->with('success', "Shift kerja {$validated['name']} ({$validated['start_time']} - {$validated['end_time']}) berhasil ditambahkan.");
    }

    public function deleteShift(int $id): RedirectResponse
    {
        Shift::findOrFail($id)->delete();
        return back()->with('success', 'Shift kerja berhasil dihapus.');
    }

    public function storeHoliday(Request $request): RedirectResponse
    {
        $tenantId = $this->tenantManager->getActiveTenantId();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'date' => 'required|date',
            'is_joint_leave' => 'nullable|boolean',
        ]);

        $validated['tenant_id'] = $tenantId;
        $validated['is_joint_leave'] = $request->boolean('is_joint_leave');

        Holiday::create($validated);

        return back()->with('success', "Hari libur {$validated['name']} berhasil ditambahkan ke kalender kerja.");
    }

    public function deleteHoliday(int $id): RedirectResponse
    {
        Holiday::findOrFail($id)->delete();
        return back()->with('success', 'Hari libur berhasil dihapus.');
    }
}

