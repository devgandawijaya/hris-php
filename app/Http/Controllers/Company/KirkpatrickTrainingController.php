<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\TrainingCertificate;
use App\Models\TrainingEnrollment;
use App\Models\TrainingKirkpatrickEvaluation;
use App\Services\Learning\KirkpatrickEvaluationService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KirkpatrickTrainingController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected KirkpatrickEvaluationService $kirkpatrickService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $company = Company::where('tenant_id', $tenant->id)->firstOrFail();

        $trainings = \App\Models\TrainingCourse::where('tenant_id', $tenant->id)->get();
        $certificates = TrainingCertificate::where('tenant_id', $tenant->id)->latest()->get();

        $enrollments = TrainingEnrollment::where('tenant_id', $tenant->id)
            ->with(['employee.department', 'trainingCourse', 'kirkpatrickEvaluation', 'digitalCertificate'])
            ->latest()
            ->paginate(15);

        $stats = [
            'total_enrollments' => TrainingEnrollment::where('tenant_id', $tenant->id)->count(),
            'certificates_issued' => TrainingCertificate::where('tenant_id', $tenant->id)->count(),
            'avg_learning_gain' => round((float) TrainingKirkpatrickEvaluation::where('tenant_id', $tenant->id)->avg('learning_gain_percentage'), 1),
            'avg_reaction' => round((float) TrainingKirkpatrickEvaluation::where('tenant_id', $tenant->id)->avg('level1_reaction_score'), 1),
        ];

        $summary = [
            'total_enrollments' => $stats['total_enrollments'],
            'evaluations_completed' => TrainingKirkpatrickEvaluation::where('tenant_id', $tenant->id)->whereNotNull('post_test_score')->count(),
            'avg_knowledge_gain' => $stats['avg_learning_gain'],
            'certificates_issued' => $stats['certificates_issued'],
        ];

        return view('company.training.kirkpatrick', compact('company', 'trainings', 'enrollments', 'certificates', 'summary', 'stats'));
    }

    public function storeTestScores(Request $request, TrainingEnrollment $enrollment): RedirectResponse
    {
        $validated = $request->validate([
            'pre_test_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'post_test_score' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $this->kirkpatrickService->recordTestScores(
            $enrollment,
            (float) $validated['pre_test_score'],
            (float) $validated['post_test_score']
        );

        return redirect()->back()->with('success', 'Nilai Pre-Test dan Post-Test (Level 2: Learning) berhasil disimpan.');
    }

    public function storeReaction(Request $request, TrainingEnrollment $enrollment): RedirectResponse
    {
        $validated = $request->validate([
            'reaction_score' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'level1_reaction_score' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'feedback_notes' => ['nullable', 'string', 'max:500'],
            'level1_feedback' => ['nullable', 'string', 'max:500'],
        ]);

        $score = (float) ($validated['level1_reaction_score'] ?? $validated['reaction_score'] ?? 5.0);
        $feedback = $validated['level1_feedback'] ?? $validated['feedback_notes'] ?? null;

        $evaluation = $enrollment->kirkpatrickEvaluation ?? TrainingKirkpatrickEvaluation::firstOrCreate(
            ['training_enrollment_id' => $enrollment->id],
            ['tenant_id' => $enrollment->tenant_id]
        );

        $this->kirkpatrickService->submitReaction(
            $evaluation,
            $score,
            $feedback
        );

        return redirect()->back()->with('success', 'Umpan balik kepuasan pelatihan (Level 1: Reaction) berhasil disimpan.');
    }

    public function storeBehavior(Request $request, TrainingEnrollment $enrollment): RedirectResponse
    {
        $validated = $request->validate([
            'behavior_score' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'level3_behavior_score' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'supervisor_observation' => ['nullable', 'string', 'max:500'],
            'level3_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $score = (float) ($validated['level3_behavior_score'] ?? $validated['behavior_score'] ?? 4.5);
        $notes = $validated['level3_notes'] ?? $validated['supervisor_observation'] ?? null;

        $evaluation = $enrollment->kirkpatrickEvaluation ?? TrainingKirkpatrickEvaluation::firstOrCreate(
            ['training_enrollment_id' => $enrollment->id],
            ['tenant_id' => $enrollment->tenant_id]
        );

        $this->kirkpatrickService->submitBehaviorAssessment(
            $evaluation,
            $request->user(),
            $score,
            $notes
        );

        return redirect()->back()->with('success', 'Evaluasi perilaku kerja pasca pelatihan (Level 3: Behavior) berhasil disimpan.');
    }

    public function issueCertificate(Request $request, TrainingEnrollment $enrollment): RedirectResponse
    {
        $cert = $this->kirkpatrickService->issueCertificate($enrollment, $request->user());

        return redirect()->back()->with('success', "Sertifikat digital kelulusan {$cert->certificate_number} berhasil diterbitkan.");
    }

    public function printCertificate(TrainingCertificate $certificate): View
    {
        $tenant = $this->tenantManager->getTenant();
        $company = Company::where('tenant_id', $tenant->id)->first() ?? Company::first();
        $certificate->load('enrollment.employee.department');
        return view('company.training.certificate', compact('company', 'certificate'));
    }
}
