<?php

namespace App\Services\Relations;

use App\Models\Company;
use App\Models\BipartiteMeeting;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class BipartiteService
{
    /**
     * Record a new official Bipartite Meeting.
     */
    public function recordMeeting(Company $company, array $data): BipartiteMeeting
    {
        $employerReps = is_array($data['employer_representatives']) ? $data['employer_representatives'] : [];
        $workerReps = is_array($data['worker_representatives']) ? $data['worker_representatives'] : [];

        // Permenaker 32/2008 Pasal 6: Unsur pengusaha dan pekerja seimbang 1:1
        if (count($employerReps) === 0 || count($workerReps) === 0) {
            throw ValidationException::withMessages([
                'representatives' => ['LKS Bipartit wajib dihadiri oleh unsur perwakilan pengusaha dan unsur pekerja minimal masing-masing 1 orang.']
            ]);
        }

        $date = Carbon::parse($data['meeting_date']);
        $year = $date->format('Y');
        $month = $date->format('m');
        $count = BipartiteMeeting::where('tenant_id', $company->tenant_id)->count() + 1;
        $meetingNumber = sprintf('BIPARTIT/%s/%s/%04d', $year, $month, $count);

        return BipartiteMeeting::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'meeting_number' => $meetingNumber,
            'meeting_date' => $date->toDateString(),
            'meeting_title' => $data['meeting_title'],
            'agenda_topics' => $data['agenda_topics'],
            'employer_representatives' => $employerReps,
            'worker_representatives' => $workerReps,
            'meeting_minutes' => $data['meeting_minutes'],
            'action_items' => $data['action_items'] ?? null,
            'status' => 'conducted',
        ]);
    }

    /**
     * Mark meeting reported to Disnaker (Permenaker 32/2008 Pasal 15 semester report).
     */
    public function markReportedToDisnaker(BipartiteMeeting $meeting, ?string $reportDate = null): BipartiteMeeting
    {
        $meeting->update([
            'disnaker_reported_at' => $reportDate ?? now()->toDateString(),
            'status' => 'reported_to_disnaker',
        ]);

        return $meeting;
    }
}
