<?php

namespace App\Services\Talent;

use App\Models\Employee;
use App\Models\MentorshipPair;
use App\Models\MentorshipCheckin;
use Carbon\Carbon;
use InvalidArgumentException;

class MentorshipService
{
    /**
     * Assign a Senior Buddy / Mentor to a new employee
     */
    public function assignMentor(Employee $mentee, Employee $mentor, string $startDate, ?string $objectives = null): MentorshipPair
    {
        if ($mentee->id === $mentor->id) {
            throw new InvalidArgumentException("Mentee dan mentor tidak boleh orang yang sama.");
        }

        $tenantId = $mentee->tenant_id;
        $start = Carbon::parse($startDate);
        $end = $start->copy()->addDays(90); // 90 days standard onboarding probation cycle

        $pair = MentorshipPair::create([
            'tenant_id' => $tenantId,
            'company_id' => $mentee->company_id,
            'mentee_employee_id' => $mentee->id,
            'mentor_employee_id' => $mentor->id,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'status' => 'active',
            'program_objectives' => $objectives ?? 'Bimbingan pengenalan budaya perusahaan, adaptasi sistem kerja, dan pemenuhan ekspektasi 90 hari masa probation.',
        ]);

        // Auto-schedule Day 30, Day 60, Day 90 Milestones
        $milestones = [
            [
                'milestone' => 'day_30',
                'scheduled_date' => $start->copy()->addDays(30)->toDateString(),
                'agenda_topics' => 'Pengenalan budaya tim, instalasi tools kerja, dan orientasi proses bisnis inti.',
            ],
            [
                'milestone' => 'day_60',
                'scheduled_date' => $start->copy()->addDays(60)->toDateString(),
                'agenda_topics' => 'Review kemandirian tugas, penyelesaian hambatan teknis, dan integrasi lintas departemen.',
            ],
            [
                'milestone' => 'day_90',
                'scheduled_date' => $start->copy()->addDays(90)->toDateString(),
                'agenda_topics' => 'Refleksi pencapaian masa percobaan, kesiapan evaluasi SK PKWTT, dan rencana pengembangan lanjutan.',
            ],
        ];

        foreach ($milestones as $m) {
            MentorshipCheckin::create([
                'tenant_id' => $tenantId,
                'mentorship_pair_id' => $pair->id,
                'milestone' => $m['milestone'],
                'scheduled_date' => $m['scheduled_date'],
                'status' => 'pending',
                'agenda_topics' => $m['agenda_topics'],
            ]);
        }

        return $pair->load('checkins');
    }

    /**
     * Submit check-in evaluation and feedback (2-way)
     */
    public function submitCheckin(MentorshipCheckin $checkin, array $data): MentorshipCheckin
    {
        $checkin->update([
            'completed_date' => Carbon::today()->toDateString(),
            'status' => 'completed',
            'mentee_satisfaction_score' => $data['mentee_satisfaction_score'] ?? null,
            'mentee_feedback' => $data['mentee_feedback'] ?? null,
            'mentor_adaptation_score' => $data['mentor_adaptation_score'] ?? null,
            'mentor_notes' => $data['mentor_notes'] ?? null,
            'action_plan' => $data['action_plan'] ?? null,
        ]);

        // If all 3 check-ins are completed, mark pair as completed
        $pair = $checkin->mentorshipPair;
        $totalCheckins = $pair->checkins()->count();
        $completedCheckins = $pair->checkins()->where('status', 'completed')->count();

        if ($totalCheckins > 0 && $completedCheckins === $totalCheckins) {
            $pair->update(['status' => 'completed']);
        }

        return $checkin->fresh();
    }

    /**
     * Get aggregate analytics on mentorship program for HR dashboard
     */
    public function getMentorshipSummary(int $tenantId): array
    {
        $pairs = MentorshipPair::where('tenant_id', $tenantId)->get();
        $totalPairs = $pairs->count();
        $activePairs = $pairs->where('status', 'active')->count();
        $completedPairs = $pairs->where('status', 'completed')->count();

        $checkins = MentorshipCheckin::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->get();

        $avgSatisfaction = $checkins->avg('mentee_satisfaction_score') ?? 0;
        $avgAdaptation = $checkins->avg('mentor_adaptation_score') ?? 0;

        return [
            'total_pairs' => $totalPairs,
            'active_pairs' => $activePairs,
            'completed_pairs' => $completedPairs,
            'completion_rate' => $totalPairs > 0 ? round(($completedPairs / $totalPairs) * 100, 1) : 0,
            'avg_mentee_satisfaction' => round((float) $avgSatisfaction, 2),
            'avg_mentor_adaptation' => round((float) $avgAdaptation, 2),
        ];
    }
}
