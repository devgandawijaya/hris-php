<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CmsContactMessage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CmsLeadController extends Controller
{
    public function index(Request $request): View
    {
        $query = CmsContactMessage::with('contactedBy')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $leads = $query->paginate(15)->withQueryString();

        $metrics = [
            'total' => CmsContactMessage::count(),
            'new' => CmsContactMessage::where('status', 'new')->count(),
            'contacted' => CmsContactMessage::where('status', 'contacted')->count(),
            'qualified' => CmsContactMessage::where('status', 'qualified')->count(),
            'converted' => CmsContactMessage::where('status', 'converted')->count(),
        ];

        return view('super-admin.cms.leads', compact('leads', 'metrics'));
    }

    public function updateStatus(Request $request, CmsContactMessage $lead): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,contacted,qualified,converted,closed'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $lead->status = $validated['status'];
        if (isset($validated['notes'])) {
            $lead->notes = $validated['notes'];
        }

        if ($validated['status'] !== 'new' && !$lead->contacted_at) {
            $lead->contacted_at = now();
            $lead->contacted_by_user_id = $request->user()->id;
        }

        $lead->is_read = true;
        $lead->save();

        return back()->with('success', "Prospek lead dari {$lead->name} ({$lead->company}) berhasil diperbarui ke status: {$lead->status}.");
    }
}
