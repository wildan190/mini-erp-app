<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\System\Models\ApprovalChain;
use App\Domain\System\Models\Role;

class ApprovalChainSeeder extends Seeder
{
    public function run(): void
    {
        $purchasingLeadRole = Role::where('slug', 'purchasing-lead')->first();
        $financeManagerRole = Role::where('slug', 'finance-manager')->first();
        $warehouseManagerRole = Role::where('slug', 'warehouse-manager')->first();
        $hrManagerRole = Role::where('slug', 'hr-manager')->first();

        // 1. High-Value Purchase Request Chain (> IDR 10,000,000)
        $prChain = ApprovalChain::updateOrCreate(
            ['name' => 'High-Value Purchase Request Approval'],
            [
                'module'     => 'purchasing',
                'model_type' => 'App\Domain\Purchasing\Models\PurchaseRequest',
                'min_amount' => 10000000.00,
                'max_amount' => null,
                'is_active'  => true,
            ]
        );

        $prChain->steps()->delete();
        $prChain->steps()->create([
            'step_order'    => 1,
            'approver_type' => 'role',
            'approver_uuid' => $purchasingLeadRole?->uuid,
            'is_final_step' => false,
        ]);
        $prChain->steps()->create([
            'step_order'    => 2,
            'approver_type' => 'role',
            'approver_uuid' => $financeManagerRole?->uuid,
            'is_final_step' => true,
        ]);

        // 2. Inter-Warehouse Transfer Approval Chain
        $transferChain = ApprovalChain::updateOrCreate(
            ['name' => 'Inter-Warehouse Stock Transfer Approval'],
            [
                'module'     => 'inventory',
                'model_type' => 'App\Domain\Inventory\Models\InventoryTransferOrder',
                'min_amount' => 0,
                'max_amount' => null,
                'is_active'  => true,
            ]
        );

        $transferChain->steps()->delete();
        $transferChain->steps()->create([
            'step_order'    => 1,
            'approver_type' => 'role',
            'approver_uuid' => $warehouseManagerRole?->uuid,
            'is_final_step' => true,
        ]);

        // 3. Employee Reimbursement Approval Chain
        $reimbursementChain = ApprovalChain::updateOrCreate(
            ['name' => 'Employee Reimbursement Approval'],
            [
                'module'     => 'hrm',
                'model_type' => 'App\Domain\HRM\Models\Reimbursement',
                'min_amount' => 1000000.00,
                'max_amount' => null,
                'is_active'  => true,
            ]
        );

        $reimbursementChain->steps()->delete();
        $reimbursementChain->steps()->create([
            'step_order'    => 1,
            'approver_type' => 'department_manager',
            'approver_uuid' => null,
            'is_final_step' => false,
        ]);
        $reimbursementChain->steps()->create([
            'step_order'    => 2,
            'approver_type' => 'role',
            'approver_uuid' => $financeManagerRole?->uuid,
            'is_final_step' => true,
        ]);
    }
}
