<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Employee;
use App\Models\MentorshipPair;
use App\Models\MentorshipCheckin;
use App\Services\Talent\MentorshipService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class MentorshipController extends Controller
{
    public function __construct(
        protected MentorshipService $mentorshipService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $pairs = MentorshipPair::with(['mentee.department', 'mentor.department', 'checkins'])
            ->where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->latest('start_date')
            ->paginate(10);

        $pendingCheckins = MentorshipCheckin::with(['mentorshipPair.mentee', 'mentorshipPair.mentor'])
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->orderBy('scheduled_date')
            ->take(10)
            ->get();

        $summary = $this->mentorshipService->getMentorshipSummary($tenantId);

        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('first_name')->get();

        return view('company.mentorship.index', compact('company', 'pairs', 'pendingCheckins', 'summary', 'employees'));
    }

    public function storePair(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $validated = $request->validate([
            'mentee_employee_id' => 'required|exists:employees,id',
            'mentor_employee_id' => 'required|exists:employees,id|different:mentee_employee_id',
            'start_date' => 'required|date',
            'program_objectives' => 'nullable|string',
        ]);

        $mentee = Employee::where('tenant_id', $tenantId)->findOrFail($validated['mentee_employee_id']);
        $mentor = Employee::where('tenant_id', $tenantId)->findOrFail($validated['mentor_employee_id']);

        try {
            $pair = $this->mentorshipService->assignMentor(
                $mentee,
                $mentor,
                $validated['start_date'],
                $validated['program_objectives'] ?? null
            );

            return back()->with('success', "Program Onboarding Buddy berhasil diinisiasi untuk {$mentee->full_name} dengan Mentor {$mentor->full_name}. Milestone Day 30, 60, dan 90 telah dijadwalkan.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function completeCheckin(Request $request, MentorshipCheckin $checkin): RedirectResponse
    {
        $validated = $request->validate([
            'mentee_satisfaction_score' => 'required|integer|min:1|max:5',
            'mentee_feedback' => 'nullable|string',
            'mentor_adaptation_score' => 'required|integer|min:1|max:5',
            'mentor_notes' => 'nullable|string',
            'action_plan' => 'nullable|string',
        ]);

        $this->mentorshipService->submitCheckin($checkin, $validated);

        return back()->with('success', "Evaluasi check-in {$checkin->milestone} berhasil diselesaikan dan dicatat.");
    }
}
