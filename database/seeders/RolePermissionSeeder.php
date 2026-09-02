<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\System\Models\Role;
use App\Domain\System\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Permission Catalog (all modules)
        // These serve as a checklist for Super Admin when creating custom roles via the Role Management UI.
        $permissions = [
            // HRM
            ['name' => 'View Employees',             'slug' => 'hrm.employees.view',            'module' => 'hrm'],
            ['name' => 'Manage Employees',           'slug' => 'hrm.employees.manage',          'module' => 'hrm'],
            ['name' => 'Manage Departments',         'slug' => 'hrm.departments.manage',        'module' => 'hrm'],
            ['name' => 'Manage Designations',        'slug' => 'hrm.designations.manage',       'module' => 'hrm'],
            ['name' => 'Manage Office Locations',    'slug' => 'hrm.locations.manage',          'module' => 'hrm'],
            ['name' => 'Manage Talent Acquisition',  'slug' => 'hrm.recruitment.manage',        'module' => 'hrm'],
            ['name' => 'Approve Leave',              'slug' => 'hrm.leave.approve',             'module' => 'hrm'],
            ['name' => 'Approve Reimbursement',      'slug' => 'hrm.reimbursement.approve',     'module' => 'hrm'],
            ['name' => 'Approve Resignation',        'slug' => 'hrm.resignation.approve',       'module' => 'hrm'],
            ['name' => 'Manage Payroll',             'slug' => 'hrm.payroll.manage',            'module' => 'hrm'],
            ['name' => 'Manage Shifts',              'slug' => 'hrm.shifts.manage',             'module' => 'hrm'],

            // CRM
            ['name' => 'View Customers',           'slug' => 'crm.customers.view',         'module' => 'crm'],
            ['name' => 'Manage Customers',         'slug' => 'crm.customers.manage',       'module' => 'crm'],
            ['name' => 'Manage Leads',             'slug' => 'crm.leads.manage',           'module' => 'crm'],
            ['name' => 'Manage Quotations',        'slug' => 'crm.quotations.manage',      'module' => 'crm'],

            // Finance
            ['name' => 'View Ledger',              'slug' => 'finance.ledger.view',        'module' => 'finance'],
            ['name' => 'Manage Accounts',          'slug' => 'finance.accounts.manage',    'module' => 'finance'],
            ['name' => 'Approve Financial Records', 'slug' => 'finance.records.approve',   'module' => 'finance'],

            // Purchasing
            ['name' => 'Create Purchase Request',  'slug' => 'purchasing.pr.create',       'module' => 'purchasing'],
            ['name' => 'Approve Purchase Request', 'slug' => 'purchasing.pr.approve',      'module' => 'purchasing'],
            ['name' => 'Manage Purchase Orders',   'slug' => 'purchasing.po.manage',       'module' => 'purchasing'],
            ['name' => 'Manage Suppliers',         'slug' => 'purchasing.suppliers.manage','module' => 'purchasing'],

            // Project
            ['name' => 'View Projects',            'slug' => 'project.projects.view',      'module' => 'project'],
            ['name' => 'Manage Projects',          'slug' => 'project.projects.manage',    'module' => 'project'],
            ['name' => 'Manage Tasks',             'slug' => 'project.tasks.manage',       'module' => 'project'],
            ['name' => 'Approve Timesheets',       'slug' => 'project.timesheets.approve', 'module' => 'project'],

            // Inventory
            ['name' => 'View Products',            'slug' => 'inventory.products.view',    'module' => 'inventory'],
            ['name' => 'Manage Stock',             'slug' => 'inventory.stock.manage',     'module' => 'inventory'],
            ['name' => 'Approve Transfer Orders',  'slug' => 'inventory.transfers.approve','module' => 'inventory'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }

        // 2. Seed ONLY the Super Admin system role.
        // All other roles (HR Manager, Finance, etc.) are created by Super Admin via the Role Management UI.
        // Super Admin automatically bypasses ALL permission checks via the root bypass in HasRolesAndPermissions.
        Role::updateOrCreate(
            ['slug' => 'super-admin'],
            [
                'name'        => 'Super Admin',
                'description' => 'Full access to all system capabilities. Cannot be modified.',
                'is_system'   => true,
            ]
        );
    }
}
