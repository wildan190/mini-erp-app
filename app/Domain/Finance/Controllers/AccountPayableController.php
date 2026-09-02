<?php

namespace App\Domain\Finance\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Purchasing\Models\Supplier;
use App\Domain\Finance\Models\ApBill;
use App\Domain\Finance\Models\ApPayment;
use App\Domain\Finance\Services\AccountPayableService;
use App\Domain\Finance\Services\MidtransDisbursementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Account Payable", description: "AP vendors (= suppliers), bills, and Midtrans Iris disbursement payments")]
class AccountPayableController extends Controller
{
    public function __construct(
        protected AccountPayableService $apService,
        protected MidtransDisbursementService $midtrans,
    ) {}

    // ── DASHBOARD ─────────────────────────────────────────────────────────────

    public function dashboard(): JsonResponse
    {
        $now = now();

        $totalOutstanding = ApBill::whereIn('status', ['approved', 'partial'])
            ->selectRaw('SUM(total_amount - paid_amount) as total')->value('total') ?? 0;

        $aging = [
            'current'     => ApBill::whereIn('status', ['approved', 'partial'])->where('due_date', '>=', $now)->count(),
            'days_1_30'   => ApBill::whereIn('status', ['approved', 'partial'])->whereBetween('due_date', [$now->copy()->subDays(30), $now->copy()->subDay()])->count(),
            'days_31_60'  => ApBill::whereIn('status', ['approved', 'partial'])->whereBetween('due_date', [$now->copy()->subDays(60), $now->copy()->subDays(31)])->count(),
            'days_61_90'  => ApBill::whereIn('status', ['approved', 'partial'])->whereBetween('due_date', [$now->copy()->subDays(90), $now->copy()->subDays(61)])->count(),
            'over_90'     => ApBill::whereIn('status', ['approved', 'partial'])->where('due_date', '<', $now->copy()->subDays(90))->count(),
        ];

        $recentBills = ApBill::with('vendor')
            ->whereIn('status', ['approved', 'partial'])
            ->orderBy('due_date')
            ->take(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_outstanding' => (float) $totalOutstanding,
                'aging'             => $aging,
                'due_soon'          => $recentBills,
                'iris_balance'      => $this->midtrans->getBalance(),
            ]
        ]);
    }

    // ── VENDORS (= SUPPLIERS) ──────────────────────────────────────────────────

    public function indexVendors(Request $request): JsonResponse
    {
        $query = Supplier::query();
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('bank_account_number', 'like', "%{$search}%");
            });
        }
        if ($request->boolean('ap_ready')) {
            $query->whereNotNull('bank_code')
                  ->whereNotNull('bank_account_number')
                  ->whereNotNull('bank_account_name');
        }
        return response()->json([
            'success' => true,
            'data' => $query->orderBy('name')->get()->append('is_ap_ready'),
        ]);
    }

    public function storeVendor(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'email'               => 'nullable|email',
            'contact'             => 'nullable|string',
            'npwp'                => 'nullable|string',
            'bank_code'           => 'required|string',
            'bank_account_number' => 'required|string',
            'bank_account_name'   => 'required|string',
            'notes'               => 'nullable|string',
        ]);

        $supplier = Supplier::create($data);

        // Auto-register as Iris beneficiary
        $this->midtrans->createBeneficiary($supplier);

        return response()->json(['success' => true, 'data' => $supplier->fresh()->append('is_ap_ready')], 201);
    }

    public function updateVendorBankInfo(Request $request, string $uuid): JsonResponse
    {
        $supplier = Supplier::where('uuid', $uuid)->firstOrFail();

        $data = $request->validate([
            'bank_code'           => 'required|string',
            'bank_account_number' => 'required|string',
            'bank_account_name'   => 'required|string',
        ]);

        $supplier->update($data);

        // Register / update Iris beneficiary
        $this->midtrans->createBeneficiary($supplier);

        return response()->json(['success' => true, 'data' => $supplier->fresh()->append('is_ap_ready')]);
    }

    // ── BILLS ─────────────────────────────────────────────────────────────────

    public function indexBills(Request $request): JsonResponse
    {
        $query = ApBill::with('vendor')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->vendor_uuid, fn($q) => $q->whereHas('vendor', fn($v) => $v->where('uuid', $request->vendor_uuid)))
            ->orderBy('due_date');

        $bills = $query->paginate($request->get('per_page', 15));

        return response()->json(['success' => true, 'data' => $bills]);
    }

    public function storeBill(Request $request): JsonResponse
    {
        $data = $request->validate([
            'vendor_uuid'  => 'required|exists:suppliers,uuid',
            'reference'    => 'nullable|string',
            'bill_date'    => 'required|date',
            'due_date'     => 'required|date|after_or_equal:bill_date',
            'tax_rate'     => 'nullable|numeric|min:0|max:100',
            'notes'        => 'nullable|string',
            'items'        => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity'    => 'required|numeric|min:0',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.account_uuid'=> 'nullable|exists:accounts,uuid',
        ]);

        $supplier = Supplier::where('uuid', $data['vendor_uuid'])->firstOrFail();

        $subtotal = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);
        $taxRate  = $data['tax_rate'] ?? 0;
        $taxAmount = round($subtotal * $taxRate / 100, 2);
        $total = round($subtotal + $taxAmount, 2);

        $bill = ApBill::create([
            'vendor_id'    => $supplier->id,
            'bill_number'  => 'BILL-' . strtoupper(Str::random(8)),
            'reference'    => $data['reference'] ?? null,
            'bill_date'    => $data['bill_date'],
            'due_date'     => $data['due_date'],
            'subtotal'     => $subtotal,
            'tax_amount'   => $taxAmount,
            'total_amount' => $total,
            'paid_amount'  => 0,
            'status'       => 'draft',
            'notes'        => $data['notes'] ?? null,
        ]);

        foreach ($data['items'] as $item) {
            $bill->items()->create([
                'description'  => $item['description'],
                'quantity'     => $item['quantity'],
                'unit_price'   => $item['unit_price'],
                'amount'       => round($item['quantity'] * $item['unit_price'], 2),
                'account_uuid' => $item['account_uuid'] ?? null,
            ]);
        }

        return response()->json(['success' => true, 'data' => $bill->load(['vendor', 'items'])], 201);
    }

    public function showBill(string $uuid): JsonResponse
    {
        $bill = ApBill::with(['vendor', 'items.account', 'payments.creator', 'approvedBy'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json(['success' => true, 'data' => $bill]);
    }

    public function approveBill(string $uuid): JsonResponse
    {
        $bill = ApBill::where('uuid', $uuid)->firstOrFail();
        $updated = $this->apService->approveBill($bill, request()->user());

        return response()->json(['success' => true, 'message' => 'Bill approved', 'data' => $updated]);
    }

    // ── PAYMENTS ──────────────────────────────────────────────────────────────

    public function payBill(Request $request, string $uuid): JsonResponse
    {
        $bill = ApBill::with('vendor')->where('uuid', $uuid)->firstOrFail();

        $data = $request->validate([
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'nullable|in:bank_transfer,midtrans_iris,cash,other',
            'notes'          => 'nullable|string',
        ]);

        $payment = $this->apService->payBill(
            $bill,
            $request->user(),
            (float) $data['amount'],
            $data['payment_method'] ?? 'midtrans_iris',
            $data['notes'] ?? null
        );

        return response()->json(['success' => true, 'message' => 'Payment processed', 'data' => $payment->load('bill.vendor')]);
    }

    public function reconcilePayment(string $paymentUuid): JsonResponse
    {
        $payment = ApPayment::where('uuid', $paymentUuid)->firstOrFail();
        $updated = $this->apService->reconcilePayment($payment);

        return response()->json(['success' => true, 'data' => $updated]);
    }

    public function irisBalance(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->midtrans->getBalance()]);
    }
}
