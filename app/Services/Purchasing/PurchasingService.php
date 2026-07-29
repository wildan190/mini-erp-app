<?php

namespace App\Services\Purchasing;

use App\Models\Purchasing\PurchaseRequest;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Purchasing\GoodsReceipt;
use App\Models\Purchasing\PurchaseInvoice;
use App\Domain\Finance\Services\AccountingService;
use App\Domain\Finance\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchasingService
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    /**
     * Create a Purchase Order from a Purchase Request
     */
    public function createPOFromPR(PurchaseRequest $pr, $supplierId, array $items)
    {
        return DB::transaction(function () use ($pr, $supplierId, $items) {
            $po = PurchaseOrder::create([
                'number' => 'PO-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'supplier_id' => $supplierId,
                'purchase_request_id' => $pr->id,
                'date' => now()->toDateString(),
                'status' => 'draft'
            ]);

            $total = 0;
            foreach ($items as $item) {
                $itemTotal = $item['qty'] * $item['price'];
                $po->items()->create([
                    'item_name' => $item['item_name'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'total' => $itemTotal
                ]);
                $total += $itemTotal;
            }

            $po->update(['total_amount' => $total, 'subtotal' => $total]);
            $pr->update(['status' => 'approved']);

            return $po;
        });
    }

    /**
     * Create a Purchase Invoice and integrate with Finance (GL)
     */
    public function createInvoice(array $data, array $items)
    {
        return DB::transaction(function () use ($data, $items) {
            $invoice = PurchaseInvoice::create($data);
            
            foreach ($items as $item) {
                $invoice->items()->create($item);
            }

            // Finance Integration: Generate Journal Entry
            $this->postInvoiceToLedger($invoice);

            return $invoice;
        });
    }

    protected function postInvoiceToLedger(PurchaseInvoice $invoice)
    {
        // Default Accounts (In a real app, these would be configurable)
        $payableAccount = Account::where('code', '2010')->first(); // Accounts Payable
        $expenseAccount = Account::where('code', '5010')->first(); // Purchase/Expense Account

        if (!$payableAccount || !$expenseAccount) {
            // Log warning or handle missing accounts
            return;
        }

        $entryData = [
            'date' => $invoice->date,
            'reference' => $invoice->number,
            'description' => "Purchase Invoice from Supplier: " . $invoice->supplier->name,
        ];

        $entryItems = [
            [
                'account_uuid' => $expenseAccount->uuid,
                'debit' => $invoice->total_amount,
                'credit' => 0,
                'description' => 'Purchase of goods'
            ],
            [
                'account_uuid' => $payableAccount->uuid,
                'debit' => 0,
                'credit' => $invoice->total_amount,
                'description' => 'Liability to supplier'
            ]
        ];

        $entry = $this->accountingService->postEntry($entryData, $entryItems);
        $invoice->update(['journal_entry_uuid' => $entry->uuid, 'status' => 'open']);
    }
}
