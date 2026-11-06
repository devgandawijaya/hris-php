<?php

namespace App\Services\Engagement;

use App\Models\CompanyEvent;
use App\Models\Employee;
use App\Models\EmployeeRecognition;
use App\Models\EmployeeSurvey;
use App\Models\EmployeeSurveyResponse;
use App\Models\User;
use App\Services\Notification\AppNotificationService;

class SurveyAndRecognitionService
{
    public function __construct(
        protected AppNotificationService $notificationService
    ) {}

    /**
     * Create a new employee satisfaction survey / eNPS campaign
     */
    public function createSurvey(string|int $tenantId, array $data, ?User $author = null): EmployeeSurvey
    {
        return EmployeeSurvey::create([
            'tenant_id' => $tenantId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'survey_type' => $data['survey_type'] ?? 'enps',
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'is_anonymous' => $data['is_anonymous'] ?? true,
            'status' => 'active',
            'created_by_user_id' => $author?->id,
        ]);
    }

    /**
     * Submit an employee response to a survey
     */
    public function submitSurveyResponse(EmployeeSurvey $survey, ?Employee $employee, array $data): EmployeeSurveyResponse
    {
        return EmployeeSurveyResponse::create([
            'tenant_id' => $survey->tenant_id,
            'employee_survey_id' => $survey->id,
            'employee_id' => $survey->is_anonymous ? null : $employee?->id,
            'enps_score' => isset($data['enps_score']) ? (int)$data['enps_score'] : null,
            'satisfaction_rating' => isset($data['satisfaction_rating']) ? (int)$data['satisfaction_rating'] : null,
            'feedback' => $data['feedback'] ?? null,
            'submitted_at' => now(),
        ]);
    }

    /**
     * Grant Kudos / Employee of the Month recognition to an employee
     */
    public function grantRecognition(Employee $employee, User $sender, array $data): EmployeeRecognition
    {
        $recognition = EmployeeRecognition::create([
            'tenant_id' => $employee->tenant_id,
            'sender_user_id' => $sender->id,
            'employee_id' => $employee->id,
            'badge_type' => $data['badge_type'] ?? 'star_performer',
            'title' => $data['title'],
            'message' => $data['message'],
            'reward_amount' => isset($data['reward_amount']) ? (float)$data['reward_amount'] : 0,
            'is_public' => $data['is_public'] ?? true,
        ]);

        // Send recognition notification to employee
        if ($employee->user) {
            $this->notificationService->sendToUser(
                $employee->user,
                "Apresiasi Baru: " . $data['title'],
                "Selamat! Anda mendapatkan apresiasi dari " . $sender->name . ": \"{$data['message']}\"",
                'success',
                route('ess.dashboard')
            );
        }

        return $recognition;
    }

    /**
     * Create a new company-wide or departmental event
     */
    public function createCompanyEvent(string|int $tenantId, array $data, ?User $author = null): CompanyEvent
    {
        return CompanyEvent::create([
            'tenant_id' => $tenantId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'event_type' => $data['event_type'] ?? 'townhall',
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'location' => $data['location'] ?? null,
            'target_department_id' => $data['target_department_id'] ?? null,
            'created_by_user_id' => $author?->id,
        ]);
    }
}

