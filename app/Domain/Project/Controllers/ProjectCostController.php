<?php

namespace App\Domain\Project\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Project\Models\Project;
use App\Domain\Project\Models\ProjectCost;
use App\Domain\Finance\Models\FinancialRecord;
use App\Domain\HRM\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectCostController extends Controller
{
    /**
     * Get aggregated financial overview (Budget vs Actual Cost vs Margins & Recent Expenditures).
     */
    public function financials(Request $request): JsonResponse
    {
        $projects = Project::with(['costs'])->get();

        $totalBudget = (float) $projects->sum('value');
        $actualCost  = (float) ProjectCost::sum('amount');
        $remaining   = max(0, $totalBudget - $actualCost);
        $marginPct   = $totalBudget > 0 ? round((($totalBudget - $actualCost) / $totalBudget) * 100, 1) : 0;

        $projectSpending = $projects->map(function ($proj) {
            $spent = (float) $proj->costs->sum('amount');
            return [
                'uuid'        => $proj->uuid,
                'name'        => $proj->name,
                'code'        => $proj->code,
                'value'       => (float) $proj->value,
                'total_spent' => $spent,
            ];
        });

        $recentExpenses = ProjectCost::with(['project', 'requestedByEmployee'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($cost) {
                return [
                    'uuid'                   => $cost->uuid,
                    'project_uuid'           => $cost->project_uuid,
                    'project'                => $cost->project ? [
                        'uuid' => $cost->project->uuid,
                        'name' => $cost->project->name,
                        'code' => $cost->project->code,
                    ] : null,
                    'type'                   => $cost->type,
                    'item_name'              => $cost->item_name ?? $cost->description,
                    'quantity'               => $cost->quantity ?? 1,
                    'unit_price'             => (float) ($cost->unit_price ?? $cost->amount),
                    'amount'                 => (float) $cost->amount,
                    'purpose'                => $cost->purpose ?? $cost->description,
                    'description'            => $cost->description,
                    'requested_by_name'      => $cost->requested_by_name ?? ($cost->requestedByEmployee ? trim("{$cost->requestedByEmployee->first_name} {$cost->requestedByEmployee->last_name}") : 'Staff'),
                    'requested_by_employee_uuid' => $cost->requested_by_employee_uuid,
                    'status'                 => $cost->status ?? 'pending',
                    'approved_by_name'       => $cost->approved_by_name,
                    'rejection_reason'       => $cost->rejection_reason,
                    'date'                   => $cost->date,
                    'finance_record_uuid'    => $cost->finance_record_uuid,
                ];
            });

        return response()->json([
            'message' => 'Project financial metrics and expenditures',
            'data'    => [
                'stats' => [
                    'total_budget'      => $totalBudget,
                    'actual_cost'       => $actualCost,
                    'remaining'         => $remaining,
                    'margin_percentage' => $marginPct,
                ],
                'project_spending' => $projectSpending,
                'recent_expenses'  => $recentExpenses,
            ]
        ]);
    }

    /**
     * Add a detailed cost/expense entry to a project and automatically sync to Finance Financial Records.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_uuid'                => 'required|string|exists:projects,uuid',
            'type'                        => 'required|string', // Material, Labor, Equipment, Subcontractor, Operational, Other
            'item_name'                   => 'required|string|max:255', // Beli apa (nama barang/jasa)
            'quantity'                    => 'nullable|integer|min:1', // Jumlah
            'unit_price'                  => 'nullable|numeric|min:0', // Harga satuan
            'amount'                      => 'nullable|numeric|min:0', // Total biaya (auto-calc if qty & unit_price given)
            'purpose'                     => 'required|string|max:1000', // Keperluan buat apa
            'description'                 => 'nullable|string', // Detail spesifikasi / catatan
            'requested_by_employee_uuid'  => 'nullable|string', // Siapa yang mengajukan (employee uuid)
            'requested_by_name'           => 'nullable|string|max:255', // Nama pemohon
            'date'                        => 'required|date',
            'reference_uuid'              => 'nullable|string',
        ]);

        $project = Project::where('uuid', $validated['project_uuid'])->firstOrFail();

        // Calculate total amount if unit_price and quantity provided
        $quantity  = $validated['quantity'] ?? 1;
        $unitPrice = isset($validated['unit_price']) ? (float)$validated['unit_price'] : 0;
        $amount    = isset($validated['amount']) && (float)$validated['amount'] > 0 
                     ? (float)$validated['amount'] 
                     : ($unitPrice > 0 ? $quantity * $unitPrice : 0);

        if ($amount <= 0 && $unitPrice > 0) {
            $amount = $quantity * $unitPrice;
        }

        // Resolve requested_by_name from Employee model if not explicitly provided
        $requestedByName = $validated['requested_by_name'] ?? null;
        if (empty($requestedByName) && !empty($validated['requested_by_employee_uuid'])) {
            $emp = Employee::where('uuid', $validated['requested_by_employee_uuid'])->first();
            if ($emp) {
                $requestedByName = trim("{$emp->first_name} {$emp->last_name}");
            }
        }
        if (empty($requestedByName)) {
            $user = $request->user();
            $requestedByName = $user ? $user->name : 'Staff';
        }

        // 1. Automatically Report / Sync to Finance module (FinancialRecord)
        $financeDescription = sprintf(
            "[Project Expense: %s] %s (Qty: %d @ Rp %s) - Keperluan: %s - Diajukan oleh: %s",
            $project->name,
            $validated['item_name'],
            $quantity,
            number_format($unitPrice > 0 ? $unitPrice : $amount, 0, ',', '.'),
            $validated['purpose'],
            $requestedByName
        );

        $financeRecord = FinancialRecord::create([
            'type'        => 'expense',
            'category'    => 'Project Expense - ' . ucfirst(strtolower($validated['type'])),
            'amount'      => $amount,
            'record_date' => $validated['date'],
            'description' => $financeDescription,
        ]);

        // 2. Save ProjectCost in project domain with link to finance_record_uuid
        $cost = $project->costs()->create([
            'type'                        => strtolower($validated['type']),
            'item_name'                   => $validated['item_name'],
            'quantity'                    => $quantity,
            'unit_price'                  => $unitPrice > 0 ? $unitPrice : $amount,
            'amount'                      => $amount,
            'purpose'                     => $validated['purpose'],
            'description'                 => $validated['description'] ?? $validated['purpose'],
            'requested_by_employee_uuid'  => $validated['requested_by_employee_uuid'] ?? null,
            'requested_by_name'           => $requestedByName,
            'date'                        => $validated['date'],
            'reference_uuid'              => $validated['reference_uuid'] ?? null,
            'finance_record_uuid'         => $financeRecord->uuid,
        ]);

        $cost->load(['project', 'requestedByEmployee']);

        return response()->json([
            'message' => 'Project expense recorded and successfully synced to Finance module',
            'data'    => $cost,
            'finance_record' => $financeRecord,
        ], 201);
    }

    /**
     * Delete a cost entry and reverse its finance record if present.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $cost = ProjectCost::where('uuid', $uuid)->firstOrFail();
        
        if ($cost->finance_record_uuid) {
            FinancialRecord::where('uuid', $cost->finance_record_uuid)->delete();
        }

        $cost->delete();

        return response()->json(['message' => 'Cost entry deleted and removed from Finance records']);
    }
}
