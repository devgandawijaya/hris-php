<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyAnnouncement;
use App\Models\Department;
use App\Services\EmployeeRelations\EngagementService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected EngagementService $engagementService
    ) {}

    public function index(): View
    {
        $tenant = $this->tenantManager->getTenant();

        $announcements = CompanyAnnouncement::with(['targetDepartment', 'createdBy'])
            ->where('tenant_id', $tenant->id)
            ->orderBy('is_pinned', 'desc')
            ->latest('published_at')
            ->paginate(12);

        $departments = Department::where('tenant_id', $tenant->id)->get();

        return view('company.announcements.index', compact('announcements', 'departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:general,policy,holiday,event,urgent',
            'target_department_id' => 'nullable|exists:departments,id',
            'is_pinned' => 'nullable|boolean',
            'published_at' => 'required|date',
            'expires_at' => 'nullable|date|after_or_equal:published_at',
        ]);

        $this->engagementService->createAnnouncement($tenant->id, $validated, Auth::user());

        return back()->with('success', 'Pengumuman resmi perusahaan berhasil dipublikasikan.');
    }

    public function destroy(CompanyAnnouncement $announcement): RedirectResponse
    {
        $announcement->delete();

        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}

