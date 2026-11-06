<?php

namespace App\Services\Talent;

use App\Models\Employee;
use App\Models\TrainingCourse;
use App\Models\TrainingEnrollment;
use Illuminate\Support\Facades\DB;

class TrainingService
{
    /**
     * Create a new training program / course.
     */
    public function createCourse(int $tenantId, array $data): TrainingCourse
    {
        return TrainingCourse::create([
            'tenant_id' => $tenantId,
            'title' => $data['title'],
            'trainer_name' => $data['trainer_name'] ?? null,
            'category' => $data['category'] ?? 'technical',
            'location' => $data['location'] ?? 'Online / Zoom',
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'capacity' => $data['capacity'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => 'scheduled',
        ]);
    }

    /**
     * Enroll an employee in a course.
     */
    public function enrollEmployee(TrainingCourse $course, Employee $employee): TrainingEnrollment
    {
        return TrainingEnrollment::firstOrCreate(
            [
                'tenant_id' => $course->tenant_id,
                'training_course_id' => $course->id,
                'employee_id' => $employee->id,
            ],
            [
                'status' => 'enrolled',
            ]
        );
    }

    /**
     * Mark training completion and generate certificate number.
     */
    public function completeEnrollment(
        TrainingEnrollment $enrollment,
        float $score,
        ?string $feedback = null
    ): TrainingEnrollment {
        $certNo = 'CERT-' . date('Y') . '-' . str_pad($enrollment->training_course_id, 4, '0', STR_PAD_LEFT) . '-' . str_pad($enrollment->employee_id, 4, '0', STR_PAD_LEFT);

        $enrollment->update([
            'status' => 'completed',
            'score' => $score,
            'certificate_number' => $certNo,
            'feedback' => $feedback,
        ]);

        return $enrollment;
    }
}
