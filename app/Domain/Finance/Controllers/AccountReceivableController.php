<?php

namespace App\Domain\Finance\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Finance\Models\ArInvoice;
use App\Domain\Finance\Models\ArInvoiceItem;
use App\Domain\Finance\Models\ArPayment;
use App\Domain\Finance\Models\FinancialRecord;
use App\Domain\CRM\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Account Receivable", description: "Enterprise AR customer invoices, payments, aging reports, and cash collection analytics")]
class AccountReceivableController extends Controller
{
    // ── 1. DASHBOARD & AGING ANALYTICS ────────────────────────────────────────

    public function dashboard(): JsonResponse
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();

        // 1. Total outstanding AR (unpaid balances)
        $activeInvoices = ArInvoice::whereIn('status', ['sent', 'partial', 'overdue'])->get();
        $totalOutstanding = $activeInvoices->sum(fn($inv) => (float) $inv->total_amount - (float) $inv->paid_amount);

        // 2. Overdue amount
        $overdueInvoices = $activeInvoices->filter(fn($inv) => $inv->due_date < $now->toDateString());
        $overdueAmount = $overdueInvoices->sum(fn($inv) => (float) $inv->total_amount - (float) $inv->paid_amount);

        // 3. Collected this month
        $collectedThisMonth = ArPayment::where('status', 'verified')
            ->where('payment_date', '>=', $startOfMonth->toDateString())
            ->sum('amount');

        // 4. Aging Buckets
        $aging = [
            'current' => [
                'count' => 0,
                'amount' => 0.0,
            ],
            'days_1_30' => [
                'count' => 0,
                'amount' => 0.0,
            ],
            'days_31_60' => [
                'count' => 0,
                'amount' => 0.0,
            ],
            'days_61_90' => [
                'count' => 0,
                'amount' => 0.0,
            ],
            'over_90' => [
                'count' => 0,
                'amount' => 0.0,
            ],
        ];

        foreach ($activeInvoices as $inv) {
            $balance = (float) $inv->total_amount - (float) $inv->paid_amount;
            if ($balance <= 0) continue;

            $dueDate = \Carbon\Carbon::parse($inv->due_date);
            if ($dueDate->isFuture() || $dueDate->isToday()) {
                $aging['current']['count']++;
                $aging['current']['amount'] += $balance;
            } else {
                $daysOverdue = (int) $dueDate->diffInDays($now, false);
                if ($daysOverdue <= 30) {
                    $aging['days_1_30']['count']++;
                    $aging['days_1_30']['amount'] += $balance;
                } elseif ($daysOverdue <= 60) {
                    $aging['days_31_60']['count']++;
                    $aging['days_31_60']['amount'] += $balance;
                } elseif ($daysOverdue <= 90) {
                    $aging['days_61_90']['count']++;
                    $aging['days_61_90']['amount'] += $balance;
                } else {
                    $aging['over_90']['count']++;
                    $aging['over_90']['amount'] += $balance;
                }
            }
        }

        // 5. Invoices due soon
        $dueSoon = ArInvoice::with('customer')
            ->whereIn('status', ['sent', 'partial'])
            ->orderBy('due_date', 'asc')
            ->take(6)
            ->get()
            ->map(function ($inv) {
                $inv->balance_due = $inv->balance_due;
                return $inv;
            });

        // 6. Recent verified payments
        $recentPayments = ArPayment::with('invoice')
            ->where('status', 'verified')
            ->orderBy('payment_date', 'desc')
            ->take(6)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_outstanding'     => (float) $totalOutstanding,
                'overdue_amount'        => (float) $overdueAmount,
                'collected_this_month'  => (float) $collectedThisMonth,
                'total_invoices_count'  => ArInvoice::count(),
                'active_invoices_count' => $activeInvoices->count(),
                'aging'                 => $aging,
                'due_soon'              => $dueSoon,
                'recent_payments'       => $recentPayments,
            ]
        ]);
    }

    // ── 2. INVOICES CRUD ──────────────────────────────────────────────────────

    public function indexInvoices(Request $request): JsonResponse
    {
        $query = ArInvoice::with(['customer', 'items', 'payments'])
            ->orderBy('invoice_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_uuid')) {
            $query->where('customer_uuid', $request->customer_uuid);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                  ->orWhere('customer_name', 'like', "%{$s}%")
                  ->orWhere('reference', 'like', "%{$s}%");
            });
        }

        $invoices = $query->paginate($request->integer('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data'   => $invoices,
        ]);
    }

    public function storeInvoice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_uuid'         => ['nullable', 'string'],
            'customer_name'         => ['required', 'string', 'max:255'],
            'customer_email'        => ['nullable', 'email'],
            'reference'             => ['nullable', 'string', 'max:100'],
            'invoice_date'          => ['required', 'date'],
            'due_date'              => ['required', 'date', 'after_or_equal:invoice_date'],
            'payment_terms'         => ['required', 'string'],
            'tax_rate'              => ['numeric', 'min:0', 'max:100'],
            'discount_amount'       => ['numeric', 'min:0'],
            'notes'                 => ['nullable', 'string'],
            'terms_and_conditions'  => ['nullable', 'string'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.item_name'     => ['required', 'string', 'max:255'],
            'items.*.description'   => ['nullable', 'string'],
            'items.*.quantity'      => ['required', 'numeric', 'min:0.01'],
            'items.*.unit'          => ['nullable', 'string'],
            'items.*.unit_price'    => ['required', 'numeric', 'min:0'],
            'items.*.discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // Compute Subtotal
            $subtotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['unit_price'];
                $discRate = (float) ($item['discount_rate'] ?? 0);
                $lineAmount = ($qty * $price) * (1 - ($discRate / 100));
                $subtotal += $lineAmount;

                $itemsData[] = [
                    'item_name'     => $item['item_name'],
                    'description'   => $item['description'] ?? null,
                    'quantity'      => $qty,
                    'unit'          => $item['unit'] ?? 'pcs',
                    'unit_price'    => $price,
                    'discount_rate' => $discRate,
                    'amount'        => $lineAmount,
                ];
            }

            $discountAmount = (float) ($validated['discount_amount'] ?? 0);
            $taxable = max(0, $subtotal - $discountAmount);
            $taxRate = (float) ($validated['tax_rate'] ?? 0);
            $taxAmount = round($taxable * ($taxRate / 100), 2);
            $totalAmount = $taxable + $taxAmount;

            // Generate Invoice Number: INV-YYYYMM-XXXX
            $datePrefix = date('Ym', strtotime($validated['invoice_date']));
            $latestCount = ArInvoice::where('invoice_number', 'like', "INV-{$datePrefix}-%")->count() + 1;
            $invoiceNumber = sprintf("INV-%s-%04d", $datePrefix, $latestCount);

            $user = $request->user();

            $invoice = ArInvoice::create([
                'invoice_number'        => $invoiceNumber,
                'customer_uuid'         => $validated['customer_uuid'] ?? null,
                'customer_name'         => $validated['customer_name'],
                'customer_email'        => $validated['customer_email'] ?? null,
                'reference'             => $validated['reference'] ?? null,
                'invoice_date'          => $validated['invoice_date'],
                'due_date'              => $validated['due_date'],
                'payment_terms'         => $validated['payment_terms'],
                'subtotal'              => $subtotal,
                'tax_rate'              => $taxRate,
                'tax_amount'            => $taxAmount,
                'discount_amount'       => $discountAmount,
                'total_amount'          => $totalAmount,
                'paid_amount'           => 0,
                'status'                => 'draft',
                'notes'                 => $validated['notes'] ?? null,
                'terms_and_conditions'  => $validated['terms_and_conditions'] ?? null,
                'issued_by_user_id'     => $user?->id,
                'issued_by_name'        => $user?->name ?? 'Finance Officer',
            ]);

            foreach ($itemsData as $item) {
                $invoice->items()->create($item);
            }

            $invoice->load(['customer', 'items']);

            return response()->json([
                'status'  => 'success',
                'message' => "Invoice {$invoiceNumber} created successfully.",
                'data'    => $invoice,
            ], 201);
        });
    }

    public function showInvoice(string $uuid): JsonResponse
    {
        $invoice = ArInvoice::with(['customer', 'items', 'payments'])->findOrFail($uuid);

        return response()->json([
            'status' => 'success',
            'data'   => $invoice,
        ]);
    }

    public function sendInvoice(string $uuid): JsonResponse
    {
        $invoice = ArInvoice::findOrFail($uuid);

        if ($invoice->status === 'draft') {
            $invoice->update([
                'status'  => 'sent',
                'sent_at' => now(),
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Invoice {$invoice->invoice_number} marked as Sent.",
            'data'    => $invoice->fresh(['customer', 'items', 'payments']),
        ]);
    }

    public function cancelInvoice(Request $request, string $uuid): JsonResponse
    {
        $invoice = ArInvoice::findOrFail($uuid);

        if ($invoice->paid_amount > 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cannot cancel invoice with recorded payments.',
            ], 422);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $invoice->update([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancellation_reason' => $validated['reason'],
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Invoice {$invoice->invoice_number} has been cancelled.",
            'data'    => $invoice,
        ]);
    }

    // ── 3. RECORD PAYMENT (RECEIPT) ──────────────────────────────────────────

    public function recordPayment(Request $request, string $invoiceUuid): JsonResponse
    {
        $invoice = ArInvoice::findOrFail($invoiceUuid);

        if (in_array($invoice->status, ['draft', 'cancelled', 'paid'])) {
            return response()->json([
                'status'  => 'error',
                'message' => "Cannot record payment on an invoice with status '{$invoice->status}'.",
            ], 422);
        }

        $balanceDue = (float) $invoice->total_amount - (float) $invoice->paid_amount;

        $validated = $request->validate([
            'payment_date'            => ['required', 'date'],
            'amount'                  => ['required', 'numeric', 'min:1', "max:{$balanceDue}"],
            'payment_method'          => ['required', 'string'],
            'reference_number'        => ['nullable', 'string', 'max:100'],
            'bank_account'            => ['nullable', 'string', 'max:100'],
            'receipt_attachment_path' => ['nullable', 'string'],
            'notes'                   => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated, $invoice, $request) {
            $user = $request->user();
            $payAmount = (float) $validated['amount'];

            // Generate Payment Receipt Number: REC-YYYYMM-XXXX
            $datePrefix = date('Ym', strtotime($validated['payment_date']));
            $latestCount = ArPayment::where('payment_number', 'like', "REC-{$datePrefix}-%")->count() + 1;
            $paymentNumber = sprintf("REC-%s-%04d", $datePrefix, $latestCount);

            // Create Financial Record (Income/Revenue Posting)
            $financeRecord = FinancialRecord::create([
                'type'         => 'revenue',
                'category'     => 'Sales Revenue',
                'amount'       => $payAmount,
                'record_date'  => $validated['payment_date'],
                'description'  => "[AR Receipt: {$invoice->customer_name}] {$paymentNumber} for Invoice {$invoice->invoice_number}" . ($validated['reference_number'] ? " (Ref: {$validated['reference_number']})" : ""),
                'status'       => 'approved',
                'approved_by_user_id' => $user?->id,
                'approved_by_name'    => $user?->name ?? 'Finance Officer',
                'approved_at'         => now(),
            ]);

            $payment = ArPayment::create([
                'ar_invoice_uuid'         => $invoice->uuid,
                'payment_number'          => $paymentNumber,
                'payment_date'            => $validated['payment_date'],
                'amount'                  => $payAmount,
                'payment_method'          => $validated['payment_method'],
                'reference_number'        => $validated['reference_number'] ?? null,
                'bank_account'            => $validated['bank_account'] ?? null,
                'receipt_attachment_path' => $validated['receipt_attachment_path'] ?? null,
                'notes'                   => $validated['notes'] ?? null,
                'recorded_by_user_id'     => $user?->id,
                'recorded_by_name'        => $user?->name ?? 'Finance Officer',
                'status'                  => 'verified',
                'finance_record_uuid'     => $financeRecord->uuid,
            ]);

            // Update Invoice Paid Amount and Status
            $newPaid = (float) $invoice->paid_amount + $payAmount;
            $newStatus = ($newPaid >= (float) $invoice->total_amount) ? 'paid' : 'partial';

            $invoice->update([
                'paid_amount' => $newPaid,
                'status'      => $newStatus,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => "Payment of Rp " . number_format($payAmount, 0, ',', '.') . " recorded successfully ({$paymentNumber}).",
                'data'    => [
                    'payment' => $payment,
                    'invoice' => $invoice->fresh(['customer', 'items', 'payments']),
                ]
            ], 201);
        });
    }

    // ── 4. CUSTOMERS LIST FOR AR ─────────────────────────────────────────────

    public function indexCustomers(): JsonResponse
    {
        $customers = Customer::orderBy('name')->get()->map(function ($c) {
            $invoices = ArInvoice::where('customer_uuid', $c->uuid)
                ->whereIn('status', ['sent', 'partial', 'overdue'])
                ->get();

            $totalOutstanding = $invoices->sum(fn($i) => (float) $i->total_amount - (float) $i->paid_amount);
            $overdueCount = $invoices->filter(fn($i) => $i->due_date < now()->toDateString())->count();

            return [
                'uuid'              => $c->uuid,
                'name'              => $c->name,
                'company_name'      => $c->company_name,
                'email'             => $c->email,
                'phone'             => $c->phone,
                'tax_id'            => $c->tax_id,
                'credit_limit'      => (float) ($c->credit_limit ?? 0),
                'payment_terms'     => $c->payment_terms ?? 'net_30',
                'total_outstanding' => (float) $totalOutstanding,
                'overdue_invoices'  => $overdueCount,
                'active_invoices'   => $invoices->count(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $customers,
        ]);
    }
}
