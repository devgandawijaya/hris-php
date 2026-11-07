<?php

namespace App\Services\Learning;

use App\Models\TrainingCertificate;
use App\Models\TrainingEnrollment;
use App\Models\TrainingKirkpatrickEvaluation;
use App\Models\User;
use Carbon\Carbon;

class KirkpatrickEvaluationService
{
    /**
     * Record Pre-Test and Post-Test scores (Level 2: Learning).
     */
    public function recordTestScores(
        TrainingEnrollment $enrollment,
        float $preTest,
        float $postTest
    ): TrainingKirkpatrickEvaluation {
        $delta = round($postTest - $preTest, 2);
        $gainPercentage = $preTest > 0 ? round(($delta / $preTest) * 100, 2) : $postTest;

        $eval = TrainingKirkpatrickEvaluation::updateOrCreate(
            [
                'tenant_id' => $enrollment->tenant_id,
                'training_enrollment_id' => $enrollment->id,
            ],
            [
                'pre_test_score' => $preTest,
                'post_test_score' => $postTest,
                'learning_gain_percentage' => $gainPercentage,
                'status' => 'post_test',
            ]
        );

        $enrollment->score = $postTest;
        if ($postTest >= 70.0) {
            $enrollment->status = 'completed';
        }
        $enrollment->save();

        return $eval;
    }

    /**
     * Submit participant reaction feedback (Level 1: Reaction).
     */
    public function submitReaction(
        TrainingKirkpatrickEvaluation $evaluation,
        float $reactionScore,
        ?string $feedback = null
    ): TrainingKirkpatrickEvaluation {
        $evaluation->level1_reaction_score = min(5.0, max(1.0, $reactionScore));
        $evaluation->level1_feedback = $feedback;
        $evaluation->save();

        return $evaluation;
    }

    /**
     * Submit supervisor behavior observation (Level 3: Behavior).
     */
    public function submitBehaviorAssessment(
        TrainingKirkpatrickEvaluation $evaluation,
        User $supervisor,
        float $behaviorScore,
        ?string $notes = null
    ): TrainingKirkpatrickEvaluation {
        $evaluation->level3_behavior_score = min(5.0, max(1.0, $behaviorScore));
        $evaluation->level3_notes = $notes;
        $evaluation->level3_evaluated_by = $supervisor->id;
        $evaluation->level3_evaluated_at = now();
        $evaluation->status = 'completed';
        $evaluation->save();

        return $evaluation;
    }

    /**
     * Issue verifiable digital training certificate.
     */
    public function issueCertificate(TrainingEnrollment $enrollment, User $signer, ?string $signerTitle = null): TrainingCertificate
    {
        $employee = $enrollment->employee;
        $course = $enrollment->trainingCourse;
        $year = date('Y');

        $count = TrainingCertificate::where('tenant_id', $enrollment->tenant_id)
            ->whereYear('created_at', $year)
            ->count() + 1;

        $seq = str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        $certNumber = "CERT/{$year}/LND/{$seq}";

        $score = (float) $enrollment->score;
        $gradeLabel = $score >= 90.0 ? 'Lulus dengan Pujian (Distinction)' : ($score >= 80.0 ? 'Sangat Memuaskan (Merit)' : 'Lulus Memuaskan (Pass)');

        $hashPayload = "{$certNumber}|{$employee->id}|{$course->id}|{$score}|" . date('Y-m-d');
        $verificationHash = hash('sha256', $hashPayload);

        $certificate = TrainingCertificate::updateOrCreate(
            [
                'tenant_id' => $enrollment->tenant_id,
                'training_enrollment_id' => $enrollment->id,
            ],
            [
                'certificate_number' => $certNumber,
                'recipient_name' => $employee->full_name,
                'course_title' => $course->title,
                'completion_date' => date('Y-m-d'),
                'score_achieved' => $score,
                'grade_label' => $gradeLabel,
                'verification_hash' => $verificationHash,
                'signer_name' => $signer->name,
                'signer_title' => $signerTitle ?? 'Head of Learning & Talent Development',
            ]
        );

        $enrollment->certificate_number = $certNumber;
        $enrollment->save();

        return $certificate;
    }
}
