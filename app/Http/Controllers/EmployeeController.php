<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use App\Models\Role;
use App\Models\EmployeeDocument;
use App\Models\EmployeeMutation;
use App\Models\EmployeeFamily;
use App\Models\EmployeeEducation;
use App\Models\EmployeeEmploymentHistory;
use App\Models\EmployeeEmergencyContact;
use App\Models\Tenant;
use App\Services\Subscription\PlanGatingService;
use App\Tenancy\TenantManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class EmployeeController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected PlanGatingService $planGatingService
    ) {}

    public function index(Request $request): View
    {
        $query = Employee::with(['department', 'position', 'company']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('employment_status')) {
            $query->where('employment_status', $request->employment_status);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employees = $query->latest('join_date')->paginate(12)->withQueryString();
        $departments = Department::all();

        return view('company.employees.index', compact('employees', 'departments'));
    }

    public function create(): View|RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $tenant = $tenantId ? Tenant::find($tenantId) : null;
        if ($tenant && !$this->planGatingService->canAddEmployee($tenant)) {
            $usage = $this->planGatingService->getEmployeeUsage($tenant);
            return redirect()->route('company.employees.index')->with(
                'error',
                "Batas kuota karyawan ({$usage['max']} orang) pada paket langganan Anda ({$usage['plan_name']}) telah tercapai. Harap upgrade paket untuk menambah karyawan baru."
            );
        }

        $departments = Department::with('positions')->get();
        $positions = Position::all();
        $ptkpList = array_keys(config('compliance.ptkp', []));

        return view('company.employees.create', compact('departments', 'positions', 'ptkpList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $tenant = $tenantId ? Tenant::find($tenantId) : null;
        if ($tenant && !$this->planGatingService->canAddEmployee($tenant)) {
            return back()->withInput()->with('error', "Gagal menambah karyawan: Kuota paket langganan aktif telah mencapai batas maksimal.");
        }
        $tenantId = $this->tenantManager->getActiveTenantId();
        $company = $this->tenantManager->getCompany();

        $validated = $request->validate([
            'employee_number' => 'required|string|max:50',
            'nik_ktp' => 'nullable|string|max:20',
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'gender' => 'required|in:male,female',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'marital_status' => 'required|in:single,married,widowed,divorced',
            'tax_ptkp_status' => 'required|string|max:10',
            'npwp' => 'nullable|string|max:30',
            'bpjs_tk_number' => 'nullable|string|max:30',
            'bpjs_kes_number' => 'nullable|string|max:30',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'employment_status' => 'required|in:pkwtt,pkwt,probation,internship',
            'join_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:join_date',
            'bank_name' => 'nullable|string|max:50',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_holder' => 'nullable|string|max:150',
            'basic_salary' => 'required|numeric|min:0',
            'create_user_account' => 'nullable|boolean',
        ]);

        $employee = DB::transaction(function () use ($validated, $tenantId, $company, $request) {
            $userId = null;

            // Optionally create login account for Employee
            if ($request->boolean('create_user_account')) {
                $user = User::firstOrCreate(
                    ['email' => $validated['email']],
                    [
                        'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
                        'phone' => $validated['phone'],
                        'password' => Hash::make('Password123#'), // Default temp password
                        'active_tenant_id' => $tenantId,
                        'email_verified_at' => now(),
                    ]
                );

                $employeeRole = Role::where('slug', 'employee')->first();
                $user->tenants()->syncWithoutDetaching([$tenantId => ['is_owner' => false, 'role_id' => $employeeRole?->id]]);
                if ($employeeRole) {
                    $user->assignRole($employeeRole, $tenantId);
                }
                $userId = $user->id;
            }

            $validated['tenant_id'] = $tenantId;
            $validated['company_id'] = $company?->id ?? 1;
            $validated['user_id'] = $userId;
            $validated['status'] = 'active';

            return Employee::create($validated);
        });

        return redirect()->route('company.employees.show', $employee->id)
            ->with('success', "Karyawan {$employee->full_name} ({$employee->employee_number}) berhasil didaftarkan ke sistem.");
    }

    public function show(int $id): View
    {
        $employee = Employee::with([
            'department',
            'position',
            'reportsTo',
            'subordinates',
            'documents',
            'families',
            'educations',
            'employmentHistories',
            'emergencyContacts',
            'mutations.fromDepartment',
            'mutations.toDepartment',
            'mutations.fromPosition',
            'mutations.toPosition',
            'attendanceRecords' => fn($q) => $q->latest('date')->take(10),
            'leaveRequests.leaveType',
        ])->findOrFail($id);

        $departments = Department::all();
        $positions = Position::all();
        $allEmployees = Employee::where('tenant_id', $employee->tenant_id)->where('id', '!=', $employee->id)->get();

        return view('company.employees.show', compact('employee', 'departments', 'positions', 'allEmployees'));
    }

    public function edit(int $id): View
    {
        $employee = Employee::findOrFail($id);
        $departments = Department::all();
        $positions = Position::all();
        $ptkpList = array_keys(config('compliance.ptkp', []));

        return view('company.employees.edit', compact('employee', 'departments', 'positions', 'ptkpList'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'employee_number' => 'required|string|max:50',
            'nik_ktp' => 'nullable|string|max:20',
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'gender' => 'required|in:male,female',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'marital_status' => 'required|in:single,married,widowed,divorced',
            'tax_ptkp_status' => 'required|string|max:10',
            'npwp' => 'nullable|string|max:30',
            'bpjs_tk_number' => 'nullable|string|max:30',
            'bpjs_kes_number' => 'nullable|string|max:30',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'employment_status' => 'required|in:pkwtt,pkwt,probation,internship',
            'join_date' => 'required|date',
            'end_date' => 'nullable|date',
            'bank_name' => 'nullable|string|max:50',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_holder' => 'nullable|string|max:150',
            'basic_salary' => 'required|numeric|min:0',
            'status' => 'required|in:active,suspended,resigned,terminated',
        ]);

        $employee->update($validated);

        return redirect()->route('company.employees.show', $employee->id)
            ->with('success', 'Data profil karyawan berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $employee = Employee::findOrFail($id);
        $employee->update(['status' => 'terminated']);

        return redirect()->route('company.employees.index')
            ->with('success', "Status karyawan {$employee->full_name} berhasil dinonaktifkan / terminasi.");
    }

    public function uploadDocument(Request $request, int $id): RedirectResponse
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'document_type' => 'required|in:ktp,npwp,cv,contract,degree,certificate,other',
            'title' => 'required|string|max:150',
            'file' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        $filePath = '/storage/mock/documents/' . uniqid() . '.pdf';
        $fileSize = 1024;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileSize = (int) round($file->getSize() / 1024);
            $filePath = $file->store("tenants/{$employee->tenant_id}/documents", 'local');
        }

        EmployeeDocument::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'document_type' => $validated['document_type'],
            'title' => $validated['title'],
            'file_path' => $filePath,
            'file_size_kb' => $fileSize,
        ]);

        return back()->with('success', 'Dokumen karyawan berhasil diunggah.');
    }

    public function recordMutation(Request $request, int $id): RedirectResponse
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|in:promotion,demotion,transfer,status_change',
            'to_department_id' => 'nullable|exists:departments,id',
            'to_position_id' => 'nullable|exists:positions,id',
            'to_salary' => 'nullable|numeric|min:0',
            'effective_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($employee, $validated) {
            EmployeeMutation::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'type' => $validated['type'],
                'from_department_id' => $employee->department_id,
                'to_department_id' => $validated['to_department_id'] ?? $employee->department_id,
                'from_position_id' => $employee->position_id,
                'to_position_id' => $validated['to_position_id'] ?? $employee->position_id,
                'from_salary' => $employee->basic_salary,
                'to_salary' => $validated['to_salary'] ?? $employee->basic_salary,
                'effective_date' => $validated['effective_date'],
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
            ]);

            // Update current employee state
            $employee->update([
                'department_id' => $validated['to_department_id'] ?? $employee->department_id,
                'position_id' => $validated['to_position_id'] ?? $employee->position_id,
                'basic_salary' => $validated['to_salary'] ?? $employee->basic_salary,
            ]);
        });

        return back()->with('success', 'Mutasi / Promosi karier karyawan berhasil dicatat dan diterapkan.');
    }

    // --- PHASE 10: EXTENDED PROFILE METHODS ---

    public function addFamilyMember(Request $request, int $id): RedirectResponse
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'relationship' => 'required|in:spouse,child,parent,sibling',
            'gender' => 'required|in:male,female',
            'birth_date' => 'nullable|date',
            'nik' => 'nullable|string|max:30',
            'education_level' => 'nullable|string|max:50',
            'is_bpjs_covered' => 'nullable|boolean',
            'is_tax_dependent' => 'nullable|boolean',
            'contact_number' => 'nullable|string|max:50',
        ]);

        $validated['tenant_id'] = $employee->tenant_id;
        $validated['employee_id'] = $employee->id;
        $validated['is_bpjs_covered'] = $request->boolean('is_bpjs_covered');
        $validated['is_tax_dependent'] = $request->boolean('is_tax_dependent');

        EmployeeFamily::create($validated);

        return back()->with('success', 'Data anggota keluarga berhasil ditambahkan.');
    }

    public function deleteFamilyMember(int $id, int $familyId): RedirectResponse
    {
        $family = EmployeeFamily::where('employee_id', $id)->findOrFail($familyId);
        $family->delete();

        return back()->with('success', 'Data anggota keluarga berhasil dihapus.');
    }

    public function addEducation(Request $request, int $id): RedirectResponse
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'degree_level' => 'required|string|max:50',
            'institution_name' => 'required|string|max:150',
            'major' => 'nullable|string|max:150',
            'graduation_year' => 'required|integer|min:1970|max:2050',
            'gpa' => 'nullable|numeric|between:0,4.00',
            'certificate_number' => 'nullable|string|max:100',
        ]);

        $validated['tenant_id'] = $employee->tenant_id;
        $validated['employee_id'] = $employee->id;

        EmployeeEducation::create($validated);

        return back()->with('success', 'Riwayat pendidikan berhasil ditambahkan.');
    }

    public function deleteEducation(int $id, int $eduId): RedirectResponse
    {
        $edu = EmployeeEducation::where('employee_id', $id)->findOrFail($eduId);
        $edu->delete();

        return back()->with('success', 'Riwayat pendidikan berhasil dihapus.');
    }

    public function addEmploymentHistory(Request $request, int $id): RedirectResponse
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'company_name' => 'required|string|max:150',
            'position_title' => 'required|string|max:150',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'last_salary' => 'nullable|numeric|min:0',
            'leaving_reason' => 'nullable|string',
        ]);

        $validated['tenant_id'] = $employee->tenant_id;
        $validated['employee_id'] = $employee->id;

        EmployeeEmploymentHistory::create($validated);

        return back()->with('success', 'Pengalaman kerja terdahulu berhasil ditambahkan.');
    }

    public function deleteEmploymentHistory(int $id, int $historyId): RedirectResponse
    {
        $hist = EmployeeEmploymentHistory::where('employee_id', $id)->findOrFail($historyId);
        $hist->delete();

        return back()->with('success', 'Riwayat pengalaman kerja berhasil dihapus.');
    }

    public function addEmergencyContact(Request $request, int $id): RedirectResponse
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'contact_name' => 'required|string|max:150',
            'relationship' => 'required|string|max:50',
            'phone' => 'required|string|max:50',
            'address' => 'nullable|string',
            'is_primary' => 'nullable|boolean',
        ]);

        $validated['tenant_id'] = $employee->tenant_id;
        $validated['employee_id'] = $employee->id;
        $validated['is_primary'] = $request->boolean('is_primary', true);

        EmployeeEmergencyContact::create($validated);

        return back()->with('success', 'Kontak darurat berhasil ditambahkan.');
    }

    public function deleteEmergencyContact(int $id, int $contactId): RedirectResponse
    {
        $contact = EmployeeEmergencyContact::where('employee_id', $id)->findOrFail($contactId);
        $contact->delete();

        return back()->with('success', 'Kontak darurat berhasil dihapus.');
    }

    public function assignSupervisor(Request $request, int $id): RedirectResponse
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'reports_to_id' => 'nullable|exists:employees,id|different:id',
        ]);

        $employee->update([
            'reports_to_id' => $validated['reports_to_id'] ?? null,
        ]);

        return back()->with('success', 'Atasan langsung (Supervisor) berhasil diperbarui.');
    }
}

