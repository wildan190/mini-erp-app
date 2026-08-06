<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\System\Models\Role;
use App\Domain\System\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Granular Permissions for all 6 domains
        $permissions = [
            // HRM
            ['name' => 'View Employees', 'slug' => 'hrm.employees.view', 'module' => 'hrm'],
            ['name' => 'Manage Employees', 'slug' => 'hrm.employees.manage', 'module' => 'hrm'],
            ['name' => 'Approve Leave', 'slug' => 'hrm.leave.approve', 'module' => 'hrm'],
            ['name' => 'Approve Reimbursement', 'slug' => 'hrm.reimbursement.approve', 'module' => 'hrm'],

            // CRM
            ['name' => 'View Customers', 'slug' => 'crm.customers.view', 'module' => 'crm'],
            ['name' => 'Manage Leads', 'slug' => 'crm.leads.manage', 'module' => 'crm'],
            ['name' => 'Manage Quotations', 'slug' => 'crm.quotations.manage', 'module' => 'crm'],

            // Finance
            ['name' => 'View Ledger', 'slug' => 'finance.ledger.view', 'module' => 'finance'],
            ['name' => 'Manage Accounts', 'slug' => 'finance.accounts.manage', 'module' => 'finance'],
            ['name' => 'Approve Financial Records', 'slug' => 'finance.records.approve', 'module' => 'finance'],

            // Purchasing
            ['name' => 'Create Purchase Request', 'slug' => 'purchasing.pr.create', 'module' => 'purchasing'],
            ['name' => 'Approve Purchase Request', 'slug' => 'purchasing.pr.approve', 'module' => 'purchasing'],
            ['name' => 'Manage Purchase Orders', 'slug' => 'purchasing.po.manage', 'module' => 'purchasing'],

            // Project
            ['name' => 'View Projects', 'slug' => 'project.projects.view', 'module' => 'project'],
            ['name' => 'Manage Tasks', 'slug' => 'project.tasks.manage', 'module' => 'project'],
            ['name' => 'Approve Timesheets', 'slug' => 'project.timesheets.approve', 'module' => 'project'],

            // Inventory
            ['name' => 'View Products', 'slug' => 'inventory.products.view', 'module' => 'inventory'],
            ['name' => 'Manage Stock', 'slug' => 'inventory.stock.manage', 'module' => 'inventory'],
            ['name' => 'Approve Transfer Orders', 'slug' => 'inventory.transfers.approve', 'module' => 'inventory'],
        ];

        $createdPerms = [];
        foreach ($permissions as $p) {
            $createdPerms[$p['slug']] = Permission::updateOrCreate(
                ['slug' => $p['slug']],
                $p
            );
        }

        // 2. Roles Configuration
        $superAdmin = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'description' => 'Full access to all system capabilities', 'is_system' => true]
        );
        $superAdmin->permissions()->sync(array_column($createdPerms, 'id'));

        $hrManager = Role::updateOrCreate(
            ['slug' => 'hr-manager'],
            ['name' => 'HR Manager', 'description' => 'Full access to HRM domain', 'is_system' => false]
        );
        $hrManager->permissions()->sync(Permission::where('module', 'hrm')->pluck('id'));

        $financeManager = Role::updateOrCreate(
            ['slug' => 'finance-manager'],
            ['name' => 'Finance Manager', 'description' => 'Full access to Finance & Financial Approvals', 'is_system' => false]
        );
        $financeManager->permissions()->sync(Permission::whereIn('module', ['finance', 'purchasing'])->pluck('id'));

        $purchasingLead = Role::updateOrCreate(
            ['slug' => 'purchasing-lead'],
            ['name' => 'Purchasing Lead', 'description' => 'Manage Procurement and POs', 'is_system' => false]
        );
        $purchasingLead->permissions()->sync(Permission::where('module', 'purchasing')->pluck('id'));

        $warehouseManager = Role::updateOrCreate(
            ['slug' => 'warehouse-manager'],
            ['name' => 'Warehouse Manager', 'description' => 'Manage Warehouses and Stock Transfers', 'is_system' => false]
        );
        $warehouseManager->permissions()->sync(Permission::where('module', 'inventory')->pluck('id'));

        $employeeRole = Role::updateOrCreate(
            ['slug' => 'employee'],
            ['name' => 'Standard Employee', 'description' => 'Basic staff access', 'is_system' => false]
        );
        $employeeRole->permissions()->sync(Permission::whereIn('slug', [
            'purchasing.pr.create',
            'inventory.products.view',
            'project.projects.view'
        ])->pluck('id'));
    }
}
