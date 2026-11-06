<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Role;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\LeaveType;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->is_super_admin) {
                return redirect()->intended(route('super.admin.dashboard'));
            }

            // Set active tenant session
            if ($user->active_tenant_id) {
                $request->session()->put('active_tenant_id', $user->active_tenant_id);
            } else {
                $firstTenant = $user->tenants()->first();
                if ($firstTenant) {
                    $user->update(['active_tenant_id' => $firstTenant->id]);
                    $request->session()->put('active_tenant_id', $firstTenant->id);
                }
            }

            // Direct regular employees without management role to Employee Self-Service
            if ($user->hasRole('employee') && !$user->hasRole(['company-owner', 'company-admin', 'hr-manager', 'payroll-staff'])) {
                return redirect()->intended(route('ess.dashboard'));
            }

            return redirect()->intended(route('company.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Kombinasi email dan password yang Anda masukkan tidak sesuai.',
        ])->onlyInput('email');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:200',
            'company_email' => 'required|email|max:255|unique:users,email',
            'pic_name' => 'required|string|max:150',
            'phone' => 'required|string|max:50',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'accepted',
        ]);

        $user = DB::transaction(function () use ($validated) {
            // 1. Create Tenant
            $slugBase = Str::slug($validated['company_name']);
            $slug = $slugBase;
            $counter = 1;
            while (Tenant::where('slug', $slug)->exists()) {
                $slug = "{$slugBase}-{$counter}";
                $counter++;
            }

            $tenant = Tenant::create([
                'name' => $validated['company_name'],
                'slug' => $slug,
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(14),
            ]);

            // 2. Create Company
            $company = Company::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => $validated['company_name'],
                'legal_name' => $validated['company_name'],
                'email' => $validated['company_email'],
                'phone' => $validated['phone'],
            ]);

            // 3. Create Company Settings (Default Indonesia Compliance)
            CompanySetting::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'company_id' => $company->id,
                'work_days_per_week' => 5,
                'regular_work_hours_per_day' => 8.00,
                'weekly_work_hour_limit' => 40.00,
                'late_tolerance_minutes' => 15,
                'bpjs_kes_max_cap' => 12000000.00,
                'bpjs_tk_jp_max_cap' => 10042300.00,
                'jkk_risk_grade' => 'low',
                'payroll_cutoff_day' => 20,
                'payroll_payment_day' => 25,
            ]);

            // 4. Create User (Company Owner)
            $newUser = User::create([
                'name' => $validated['pic_name'],
                'email' => $validated['company_email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'active_tenant_id' => $tenant->id,
                'email_verified_at' => now(),
            ]);

            // 5. Grant Company Owner Role
            $ownerRole = Role::where('slug', 'company-owner')->first();
            $newUser->tenants()->attach($tenant->id, [
                'role_id' => $ownerRole?->id,
                'is_owner' => true,
            ]);

            if ($ownerRole) {
                $newUser->assignRole($ownerRole, $tenant->id);
            }

            // 6. Attach Free Trial Subscription
            $trialPlan = SubscriptionPlan::where('slug', 'free-trial')->first();
            if ($trialPlan) {
                Subscription::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $trialPlan->id,
                    'status' => 'trial',
                    'billing_cycle' => 'monthly',
                    'starts_at' => now(),
                    'trial_ends_at' => now()->addDays(14),
                ]);
            }

            // 7. Seed Initial Leave Types
            LeaveType::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => 'Cuti Tahunan',
                'code' => 'AL',
                'days_allowed' => 12,
                'is_paid' => true,
            ]);

            LeaveType::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => 'Cuti Sakit',
                'code' => 'SL',
                'days_allowed' => 14,
                'is_paid' => true,
            ]);

            return $newUser;
        });

        Auth::login($user);
        $request->session()->put('active_tenant_id', $user->active_tenant_id);

        return redirect()->route('company.dashboard')->with('success', 'Selamat datang di RiHRIS! Akun dan ruang kerja perusahaan Anda berhasil dibuat.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
}

