<?php

namespace App\Domain\Finance\Controllers;

use App\Domain\Finance\Models\FinancialRecord;
use App\Domain\Project\Models\ProjectCost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Finance Analytics", description: "AI-powered Finance and Analytics Endpoints")]
class FinanceDashboardController extends FinanceBaseController
{
    #[OA\Get(
        path: "/api/platform/finance/dashboard",
        summary: "Get Finance Dashboard Summary",
        security: [["sanctum" => []]],
        tags: ["Finance Analytics"],
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function index()
    {
        // Calculate verified totals (only approved or legacy records count towards realized P&L)
        $totalRevenue = (float) FinancialRecord::where('type', 'revenue')
            ->where(function ($q) {
                $q->where('status', 'approved')->orWhereNull('status');
            })
            ->sum('amount');

        $totalExpense = (float) FinancialRecord::where('type', 'expense')
            ->where(function ($q) {
                $q->where('status', 'approved')->orWhereNull('status');
            })
            ->sum('amount');

        $netProfit = $totalRevenue - $totalExpense;

        $pendingApprovalsCount = FinancialRecord::where('status', 'pending')->count();
        $pendingApprovalsTotal = (float) FinancialRecord::where('status', 'pending')->sum('amount');

        $recentTransactions = FinancialRecord::orderBy('record_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $pendingApprovals = FinancialRecord::where('status', 'pending')
            ->orderBy('record_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($rec) {
                $cost = ProjectCost::with(['project', 'requestedByEmployee'])
                    ->where('finance_record_uuid', $rec->uuid)
                    ->first();

                return [
                    'uuid'                => $rec->uuid,
                    'type'                => $rec->type,
                    'category'            => $rec->category,
                    'amount'              => (float) $rec->amount,
                    'status'              => $rec->status,
                    'record_date'         => $rec->record_date ? (is_string($rec->record_date) ? $rec->record_date : $rec->record_date->format('Y-m-d')) : null,
                    'description'         => $rec->description,
                    'project_cost'        => $cost ? [
                        'uuid'                       => $cost->uuid,
                        'project_name'               => $cost->project?->name,
                        'project_code'               => $cost->project?->code,
                        'item_name'                  => $cost->item_name ?? $cost->description,
                        'type'                       => $cost->type,
                        'quantity'                   => $cost->quantity ?? 1,
                        'unit_price'                 => (float) ($cost->unit_price ?? $cost->amount),
                        'amount'                     => (float) $cost->amount,
                        'purpose'                    => $cost->purpose,
                        'notes'                      => $cost->description,
                        'requested_by_name'          => $cost->requested_by_name ?? ($cost->requestedByEmployee ? trim("{$cost->requestedByEmployee->first_name} {$cost->requestedByEmployee->last_name}") : 'Staff'),
                        'date'                       => $cost->date,
                        'receipt_attachment_path'    => $cost->receipt_attachment_path,
                    ] : null,
                ];
            });

        $isPgSql = DB::connection()->getDriverName() === 'pgsql';
        $monthRaw = $isPgSql ? "TO_CHAR(record_date, 'YYYY-MM')" : 'strftime("%Y-%m", record_date)';

        $monthlyData = FinancialRecord::select(
            DB::raw("$monthRaw as month"),
            DB::raw("SUM(CASE WHEN type = 'revenue' AND (status = 'approved' OR status IS NULL) THEN amount ELSE 0 END) as revenue"),
            DB::raw("SUM(CASE WHEN type = 'expense' AND (status = 'approved' OR status IS NULL) THEN amount ELSE 0 END) as expense")
        )
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get();

        return $this->success([
            'summary' => [
                'total_revenue'           => $totalRevenue,
                'total_expense'           => $totalExpense,
                'net_profit'              => $netProfit,
                'profit_margin'           => $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0,
                'pending_approvals_count' => $pendingApprovalsCount,
                'pending_approvals_total' => $pendingApprovalsTotal,
            ],
            'monthly_trends'      => $monthlyData,
            'recent_transactions' => $recentTransactions,
            'pending_approvals'   => $pendingApprovals,
        ]);
    }

    /**
     * Approve a financial record (and sync back status to related project expense if applicable).
     */
    public function approveRecord(Request $request, string $uuid): JsonResponse
    {
        $record = FinancialRecord::where('uuid', $uuid)->firstOrFail();
        $user = $request->user();

        $record->update([
            'status'              => 'approved',
            'approved_by_user_id' => $user ? $user->id : null,
            'approved_by_name'    => $user ? $user->name : 'Finance Manager',
            'approved_at'         => now(),
            'rejection_reason'    => null,
        ]);

        // Sync back to ProjectCost if this was created from project expense
        $projectCost = ProjectCost::where('finance_record_uuid', $record->uuid)->first();
        if ($projectCost) {
            $projectCost->update([
                'status'              => 'approved',
                'approved_by_user_id' => $user ? $user->id : null,
                'approved_by_name'    => $user ? $user->name : 'Finance Manager',
                'approved_at'         => now(),
                'rejection_reason'    => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Expense / Financial record approved successfully',
            'data'    => $record,
        ]);
    }

    /**
     * Reject a financial record (and sync back status to related project expense if applicable).
     */
    public function rejectRecord(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $record = FinancialRecord::where('uuid', $uuid)->firstOrFail();
        $user = $request->user();

        $record->update([
            'status'              => 'rejected',
            'approved_by_user_id' => $user ? $user->id : null,
            'approved_by_name'    => $user ? $user->name : 'Finance Manager',
            'approved_at'         => now(),
            'rejection_reason'    => $validated['reason'],
        ]);

        // Sync back to ProjectCost
        $projectCost = ProjectCost::where('finance_record_uuid', $record->uuid)->first();
        if ($projectCost) {
            $projectCost->update([
                'status'              => 'rejected',
                'approved_by_user_id' => $user ? $user->id : null,
                'approved_by_name'    => $user ? $user->name : 'Finance Manager',
                'approved_at'         => now(),
                'rejection_reason'    => $validated['reason'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Expense / Financial record rejected',
            'data'    => $record,
        ]);
    }
}
