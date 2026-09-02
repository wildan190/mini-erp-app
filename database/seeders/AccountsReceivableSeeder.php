<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Domain\Finance\Models\ArInvoice;
use App\Domain\Finance\Models\ArPayment;
use App\Domain\Finance\Models\FinancialRecord;
use App\Domain\CRM\Models\Customer;

class AccountsReceivableSeeder extends Seeder
{
    public function run(): void
    {
        if (ArInvoice::count() > 0) {
            $this->command->info('AR Invoices already seeded.');
            return;
        }

        $now = now();
        $customers = Customer::all();

        if ($customers->isEmpty()) {
            $this->command->info('No CRM customers available to seed AR invoices.');
            return;
        }

        $c1 = $customers->first();
        $c2 = $customers->count() > 1 ? $customers->skip(1)->first() : $c1;

        // 1. Invoice 1: Partially Paid (Sent)
        $inv1 = ArInvoice::create([
            'invoice_number'        => 'INV-202608-0001',
            'customer_uuid'         => $c1->uuid,
            'customer_name'         => $c1->name,
            'customer_email'        => $c1->email,
            'reference'             => 'PO-CORP-9821',
            'invoice_date'          => $now->copy()->subDays(25)->toDateString(),
            'due_date'              => $now->copy()->addDays(5)->toDateString(),
            'payment_terms'         => 'net_30',
            'subtotal'              => 50000000,
            'tax_rate'              => 11,
            'tax_amount'            => 5500000,
            'discount_amount'       => 0,
            'total_amount'          => 55500000,
            'paid_amount'           => 25000000,
            'status'                => 'partial',
            'notes'                 => 'Enterprise cloud deployment milestone 1 invoice.',
            'terms_and_conditions'  => 'Payment due within 30 calendar days.',
            'issued_by_name'        => 'Finance Officer',
            'sent_at'               => $now->copy()->subDays(25),
        ]);

        $inv1->items()->createMany([
            [
                'item_name'     => 'Enterprise Cloud ERP Implementation - Milestone 1',
                'description'   => 'Architecture discovery, setup, and DB schema provisioning',
                'quantity'      => 1,
                'unit'          => 'milestone',
                'unit_price'    => 35000000,
                'discount_rate' => 0,
                'tax_rate'      => 11,
                'amount'        => 35000000,
            ],
            [
                'item_name'     => 'Dedicated High-Performance Node Cluster (1 Year)',
                'description'   => 'Dedicated multi-zone Kubernetes hosting infrastructure',
                'quantity'      => 1,
                'unit'          => 'year',
                'unit_price'    => 15000000,
                'discount_rate' => 0,
                'tax_rate'      => 11,
                'amount'        => 15000000,
            ]
        ]);

        // Payment 1 for Invoice 1
        $finRec1 = FinancialRecord::create([
            'type'         => 'revenue',
            'category'     => 'Sales Revenue',
            'amount'       => 25000000,
            'record_date'  => $now->copy()->subDays(10)->toDateString(),
            'description'  => "[AR Receipt: {$c1->name}] REC-202608-0001 for Invoice INV-202608-0001 (Ref: TRF-BCA-98129)",
            'status'       => 'approved',
            'approved_by_name' => 'Finance Officer',
            'approved_at'      => $now->copy()->subDays(10),
        ]);

        $inv1->payments()->create([
            'payment_number'          => 'REC-202608-0001',
            'payment_date'            => $now->copy()->subDays(10)->toDateString(),
            'amount'                  => 25000000,
            'payment_method'          => 'bank_transfer',
            'reference_number'        => 'TRF-BCA-98129',
            'bank_account'            => 'BCA - 883019283 (Main Operational)',
            'recorded_by_name'        => 'Finance Officer',
            'status'                  => 'verified',
            'finance_record_uuid'     => $finRec1->uuid,
        ]);

        // 2. Invoice 2: Overdue Invoice
        $inv2 = ArInvoice::create([
            'invoice_number'        => 'INV-202607-0002',
            'customer_uuid'         => $c2->uuid,
            'customer_name'         => $c2->name,
            'customer_email'        => $c2->email,
            'reference'             => 'PO-SLA-7731',
            'invoice_date'          => $now->copy()->subDays(50)->toDateString(),
            'due_date'              => $now->copy()->subDays(20)->toDateString(),
            'payment_terms'         => 'net_30',
            'subtotal'              => 20000000,
            'tax_rate'              => 11,
            'tax_amount'            => 2200000,
            'discount_amount'       => 0,
            'total_amount'          => 22200000,
            'paid_amount'           => 0,
            'status'                => 'overdue',
            'notes'                 => 'Quarterly Managed Support and Security SLA.',
            'terms_and_conditions'  => 'Payment due within 30 calendar days.',
            'issued_by_name'        => 'Finance Officer',
            'sent_at'               => $now->copy()->subDays(50),
        ]);

        $inv2->items()->create([
            'item_name'     => 'Quarterly Managed Support & Maintenance SLA (Q3)',
            'description'   => '24/7 incident response, security patching, and monitoring',
            'quantity'      => 1,
            'unit'          => 'quarter',
            'unit_price'    => 20000000,
            'discount_rate' => 0,
            'tax_rate'      => 11,
            'amount'        => 20000000,
        ]);

        // 3. Invoice 3: Fully Paid
        $inv3 = ArInvoice::create([
            'invoice_number'        => 'INV-202608-0003',
            'customer_uuid'         => $c1->uuid,
            'customer_name'         => $c1->name,
            'customer_email'        => $c1->email,
            'reference'             => 'PO-CONSULT-019',
            'invoice_date'          => $now->copy()->subDays(15)->toDateString(),
            'due_date'              => $now->copy()->addDays(15)->toDateString(),
            'payment_terms'         => 'net_30',
            'subtotal'              => 10000000,
            'tax_rate'              => 11,
            'tax_amount'            => 1100000,
            'discount_amount'       => 0,
            'total_amount'          => 11100000,
            'paid_amount'           => 11100000,
            'status'                => 'paid',
            'notes'                 => 'Custom AI feature integration and staff training.',
            'issued_by_name'        => 'Finance Officer',
            'sent_at'               => $now->copy()->subDays(15),
        ]);

        $inv3->items()->create([
            'item_name'     => 'Custom AI Integration & Workflow Training',
            'description'   => 'Training workshop and custom LLM pipeline setup',
            'quantity'      => 1,
            'unit'          => 'package',
            'unit_price'    => 10000000,
            'discount_rate' => 0,
            'tax_rate'      => 11,
            'amount'        => 10000000,
        ]);

        $finRec3 = FinancialRecord::create([
            'type'         => 'revenue',
            'category'     => 'Sales Revenue',
            'amount'       => 11100000,
            'record_date'  => $now->copy()->subDays(5)->toDateString(),
            'description'  => "[AR Receipt: {$c1->name}] REC-202608-0002 for Invoice INV-202608-0003 (Ref: MIDTRANS-VA-89102)",
            'status'       => 'approved',
            'approved_by_name' => 'Finance Officer',
            'approved_at'      => $now->copy()->subDays(5),
        ]);

        $inv3->payments()->create([
            'payment_number'          => 'REC-202608-0002',
            'payment_date'            => $now->copy()->subDays(5)->toDateString(),
            'amount'                  => 11100000,
            'payment_method'          => 'midtrans_va',
            'reference_number'        => 'MIDTRANS-VA-89102',
            'bank_account'            => 'BCA Virtual Account - 883019283001',
            'recorded_by_name'        => 'Finance Officer',
            'status'                  => 'verified',
            'finance_record_uuid'     => $finRec3->uuid,
        ]);

        $this->command->info('Accounts Receivable demo invoices and payments seeded successfully.');
    }
}
