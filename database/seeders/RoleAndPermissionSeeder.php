<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Tenant & Company Management
            ['name' => 'View Company Profile', 'slug' => 'company.view', 'module' => 'company'],
            ['name' => 'Update Company Profile', 'slug' => 'company.update', 'module' => 'company'],
            ['name' => 'Manage Company Settings', 'slug' => 'company.settings', 'module' => 'company'],
            ['name' => 'Manage Subscriptions', 'slug' => 'subscription.manage', 'module' => 'subscription'],

            // Organization
            ['name' => 'View Organization Structure', 'slug' => 'organization.view', 'module' => 'organization'],
            ['name' => 'Manage Departments & Positions', 'slug' => 'organization.manage', 'module' => 'organization'],

            // Employee Management
            ['name' => 'View Employees', 'slug' => 'employee.view', 'module' => 'employee'],
            ['name' => 'Create Employee', 'slug' => 'employee.create', 'module' => 'employee'],
            ['name' => 'Update Employee', 'slug' => 'employee.update', 'module' => 'employee'],
            ['name' => 'Delete Employee', 'slug' => 'employee.delete', 'module' => 'employee'],
            ['name' => 'Manage Employee Documents', 'slug' => 'employee.documents', 'module' => 'employee'],

            // Attendance
            ['name' => 'View Attendance Records', 'slug' => 'attendance.view', 'module' => 'attendance'],
            ['name' => 'Clock In and Clock Out', 'slug' => 'attendance.clock', 'module' => 'attendance'],
            ['name' => 'Approve Attendance Corrections', 'slug' => 'attendance.approve', 'module' => 'attendance'],
            ['name' => 'Manage Shifts and Schedules', 'slug' => 'attendance.manage_shifts', 'module' => 'attendance'],

            // Leave
            ['name' => 'Request Leave', 'slug' => 'leave.request', 'module' => 'leave'],
            ['name' => 'View Leave Requests', 'slug' => 'leave.view', 'module' => 'leave'],
            ['name' => 'Approve Leave Requests', 'slug' => 'leave.approve', 'module' => 'leave'],
            ['name' => 'Manage Leave Types', 'slug' => 'leave.manage_types', 'module' => 'leave'],

            // Payroll & Tax
            ['name' => 'View Payroll', 'slug' => 'payroll.view', 'module' => 'payroll'],
            ['name' => 'Process Payroll', 'slug' => 'payroll.process', 'module' => 'payroll'],
            ['name' => 'Approve Payroll', 'slug' => 'payroll.approve', 'module' => 'payroll'],
            ['name' => 'Export Payroll Reports', 'slug' => 'payroll.export', 'module' => 'payroll'],
            ['name' => 'View Personal Payslip', 'slug' => 'payroll.view_own_payslip', 'module' => 'payroll'],

            // Overtime & Reimbursement
            ['name' => 'Request Overtime', 'slug' => 'overtime.request', 'module' => 'overtime'],
            ['name' => 'Approve Overtime', 'slug' => 'overtime.approve', 'module' => 'overtime'],
            ['name' => 'Request Reimbursement', 'slug' => 'reimbursement.request', 'module' => 'reimbursement'],
            ['name' => 'Approve Reimbursement', 'slug' => 'reimbursement.approve', 'module' => 'reimbursement'],

            // Contract & Compliance
            ['name' => 'Manage Employment Contracts', 'slug' => 'contract.manage', 'module' => 'contract'],
            ['name' => 'Issue Warning Letters', 'slug' => 'disciplinary.manage', 'module' => 'disciplinary'],

            // CMS & Platform Management
            ['name' => 'Manage Landing CMS', 'slug' => 'cms.manage', 'module' => 'cms'],
            ['name' => 'View Platform Audit Logs', 'slug' => 'audit.view', 'module' => 'audit'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        }

        // 14 Default Roles
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Platform Super Administrator'],
            ['name' => 'SaaS Administrator', 'slug' => 'saas-admin', 'description' => 'Platform Technical & Operations Admin'],
            ['name' => 'Company Owner', 'slug' => 'company-owner', 'description' => 'Legal Owner of Company Tenant'],
            ['name' => 'Company Admin', 'slug' => 'company-admin', 'description' => 'Primary Administrator of Company Tenant'],
            ['name' => 'HR Director', 'slug' => 'hr-director', 'description' => 'Strategic HR Executive'],
            ['name' => 'HR Manager', 'slug' => 'hr-manager', 'description' => 'Operational Human Resource Manager'],
            ['name' => 'HR Staff', 'slug' => 'hr-staff', 'description' => 'Daily HR Operations and Personnel Admin'],
            ['name' => 'Payroll Staff', 'slug' => 'payroll-staff', 'description' => 'Compensation & Benefits Specialist'],
            ['name' => 'Finance', 'slug' => 'finance', 'description' => 'Company Finance & Treasury'],
            ['name' => 'Department Head', 'slug' => 'department-head', 'description' => 'Head of Department'],
            ['name' => 'Manager', 'slug' => 'manager', 'description' => 'Team and Line Manager'],
            ['name' => 'Team Leader', 'slug' => 'team-leader', 'description' => 'Front-line Team Lead'],
            ['name' => 'Employee', 'slug' => 'employee', 'description' => 'Regular Company Employee'],
            ['name' => 'Auditor', 'slug' => 'auditor', 'description' => 'Compliance and Audit Inspector (Read-only)'],
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(['slug' => $roleData['slug']], $roleData);

            // Grant appropriate permissions to roles
            if ($role->slug === 'company-owner' || $role->slug === 'company-admin') {
                $allPermIds = Permission::pluck('id')->toArray();
                $role->permissions()->sync($allPermIds);
            } elseif ($role->slug === 'hr-manager') {
                $hrPerms = Permission::whereIn('module', ['company', 'organization', 'employee', 'attendance', 'leave', 'payroll', 'overtime', 'reimbursement', 'contract', 'disciplinary'])->pluck('id');
                $role->permissions()->sync($hrPerms);
            } elseif ($role->slug === 'payroll-staff') {
                $payPerms = Permission::whereIn('module', ['payroll', 'attendance', 'employee'])->pluck('id');
                $role->permissions()->sync($payPerms);
            } elseif ($role->slug === 'employee') {
                $empPerms = Permission::whereIn('slug', ['attendance.clock', 'leave.request', 'payroll.view_own_payslip', 'overtime.request', 'reimbursement.request'])->pluck('id');
                $role->permissions()->sync($empPerms);
            } elseif ($role->slug === 'auditor') {
                $auditPerms = Permission::whereIn('slug', ['company.view', 'employee.view', 'attendance.view', 'leave.view', 'payroll.view', 'payroll.export', 'audit.view'])->pluck('id');
                $role->permissions()->sync($auditPerms);
            }
        }
    }
}

