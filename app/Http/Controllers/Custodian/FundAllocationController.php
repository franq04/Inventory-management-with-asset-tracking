<?php

namespace App\Http\Controllers\Custodian;

use App\Http\Controllers\Controller;
use App\Models\FundAllocation;
use App\Models\AuditLog;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class FundAllocationController extends Controller
{
    private function shouldRespondJson(Request $request): bool
    {
        return $request->ajax() || $request->expectsJson() || $request->wantsJson();
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $utilizationFilter = (string) $request->input('utilization', 'all');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = $this->allocationIndexQuery($search, $utilizationFilter, $dateFrom, $dateTo);

        $summary = $this->buildAllocationSummary($query);

        $totalBudget = (float) ($summary->total_budget ?? 0);
        $totalAllocated = (float) ($summary->total_utilized ?? 0);
        $totalRemaining = (float) ($summary->total_remaining ?? 0);
        $utilizationRate = $totalBudget > 0 ? ($totalAllocated / $totalBudget) * 100 : 0;

        $kpis = [
            'totalClusters' => (int) ($summary->total_clusters ?? 0),
            'totalBudget' => $totalBudget,
            'totalAllocated' => $totalAllocated,
            'totalRemaining' => $totalRemaining,
            'utilizationRate' => $utilizationRate,
        ];

        $allocations = $query
            ->with(['creator.employee'])
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax() || $request->boolean('ajax') || $request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'tableHtml' => view('custodian.fund_allocations.partials.table', compact('allocations'))->render(),
                'kpisHtml' => view('custodian.fund_allocations.partials.kpis', compact('kpis'))->render(),
                'totalRecords' => (int) $allocations->total(),
            ]);
        }

        return view('custodian.fund_allocations.index', compact('allocations', 'kpis', 'search', 'utilizationFilter', 'dateFrom', 'dateTo'));
    }

    public function show(FundAllocation $fundAllocation)
    {
        $fundAllocation->load(['creator.employee']);

        $purchaseOrders = PurchaseOrder::query()
            ->with([
                'purchaseRequest.requester.employee',
                'supplier',
                'status',
                'items',
            ])
            ->whereHas('purchaseRequest', function ($query) use ($fundAllocation) {
                $query->where('fund_allocation_id', $fundAllocation->id);
            })
            ->orderByDesc('order_date')
            ->orderByDesc('created_at')
            ->get();

        $totalUtilized = (float) $purchaseOrders->sum(function (PurchaseOrder $order) {
            return (float) $order->items->sum(function ($item) {
                return ((float) ($item->quantity ?? 0)) * ((float) ($item->unit_cost ?? 0));
            });
        });
        $totalAmount = (float) $fundAllocation->total_amount;
        $remainingAmount = max($totalAmount - $totalUtilized, 0);
        $utilizationRate = $totalAmount > 0 ? min(($totalUtilized / $totalAmount) * 100, 100) : 0;

        $creatorEmployee = $fundAllocation->creator?->employee;
        $creatorName = $creatorEmployee
            ? trim(collect([
                $creatorEmployee->first_name,
                $creatorEmployee->middle_name,
                $creatorEmployee->last_name,
                $creatorEmployee->suffix,
            ])->filter()->implode(' '))
            : ($fundAllocation->creator?->username ?? 'Unknown');

        $utilizationRows = $purchaseOrders->map(function (PurchaseOrder $order) {
            return [
                'po_no' => $order->po_no,
                'pr_no' => $order->pr_no,
                'status' => $order->status?->status_name ?? 'Unknown',
                'requester' => $order->purchaseRequest?->requester?->username ?? '—',
                'supplier' => $order->supplier?->supplier_name ?? '—',
                'ordered_at' => optional($order->order_date)->format('M d, Y') ?: optional($order->created_at)->format('M d, Y h:i A'),
                'ordered_amount' => (float) $order->items->sum(function ($item) {
                    return ((float) ($item->quantity ?? 0)) * ((float) ($item->unit_cost ?? 0));
                }),
                'properties' => $order->items->map(function ($item) {
                    return [
                        'description' => $item->item_description,
                        'quantity' => (int) $item->quantity,
                        'unit' => $item->unit,
                        'unit_cost' => (float) $item->unit_cost,
                        'ordered_total_cost' => (float) (($item->quantity ?? 0) * ($item->unit_cost ?? 0)),
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $fundAllocation->id,
                'fund_cluster' => $fundAllocation->fund_cluster,
                'total_amount' => $totalAmount,
                'remaining_amount' => $remainingAmount,
                'allocated_amount' => max($totalAmount - $remainingAmount, 0),
                'utilized_amount' => $totalUtilized,
                'utilization_rate' => $utilizationRate,
                'created_at' => optional($fundAllocation->created_at)->format('M d, Y h:i A'),
                'created_by' => $creatorName,
                'po_count' => $purchaseOrders->count(),
                'utilization_rows' => $utilizationRows,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fund_cluster' => 'required|string|max:100|unique:fund_allocations,fund_cluster',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $allocation = DB::transaction(function () use ($validated) {
            $allocation = FundAllocation::create([
                'fund_cluster' => $validated['fund_cluster'],
                'total_amount' => $validated['total_amount'],
                'remaining_amount' => $validated['total_amount'],
                'created_by' => Auth::id(),
            ]);

            AuditLog::create([
                'account_id' => Auth::id(),
                'table_name' => 'fund_allocations',
                'action' => 'CREATE',
                'description' => sprintf(
                    "Created fund allocation '%s' with total amount ₱%s",
                    $allocation->fund_cluster,
                    number_format($allocation->total_amount, 2)
                ),
                'log_time' => now(),
            ]);

            return $allocation;
        });

        if ($this->shouldRespondJson($request)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Fund allocation created successfully.',
                'data' => $allocation,
            ]);
        }

        return redirect()->route('custodian.fund_allocations.index')
            ->with('success', 'Fund allocation created successfully.');
    }

    public function update(Request $request, FundAllocation $fundAllocation)
    {
        $validated = $request->validate([
            'fund_cluster' => 'required|string|max:100|unique:fund_allocations,fund_cluster,' . $fundAllocation->id,
            'total_amount' => 'required|numeric|min:0',
        ]);

        $oldCluster = $fundAllocation->fund_cluster;
        $oldTotal = $fundAllocation->total_amount;
        $allocated = $oldTotal - $fundAllocation->remaining_amount;

        // Ensure new total is not less than already allocated amount
        if ($validated['total_amount'] < $allocated) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'total_amount' => [
                        sprintf(
                            'Cannot reduce total below allocated amount. Already allocated: ₱%s',
                            number_format($allocated, 2)
                        )
                    ]
                ],
                'message' => 'Invalid total amount.',
            ], 422);
        }

        DB::transaction(function () use ($fundAllocation, $validated, $oldCluster, $oldTotal, $allocated) {
            $delta = $validated['total_amount'] - $oldTotal;
            $fundAllocation->update([
                'fund_cluster' => $validated['fund_cluster'],
                'total_amount' => $validated['total_amount'],
                'remaining_amount' => $fundAllocation->remaining_amount + $delta,
            ]);

            $changes = [];
            if ($oldCluster !== $validated['fund_cluster']) {
                $changes[] = "Cluster: '{$oldCluster}' → '{$validated['fund_cluster']}'";
            }
            if (abs($delta) >= 0.01) {
                $changes[] = sprintf(
                    "Total: ₱%s → ₱%s (Δ %s₱%s)",
                    number_format($oldTotal, 2),
                    number_format($validated['total_amount'], 2),
                    $delta > 0 ? '+' : '',
                    number_format(abs($delta), 2)
                );
            }

            $description = sprintf(
                "Updated fund allocation '%s'. %s. Allocated: ₱%s, Remaining: ₱%s",
                $fundAllocation->fund_cluster,
                implode(', ', $changes),
                number_format($allocated, 2),
                number_format($fundAllocation->remaining_amount, 2)
            );

            AuditLog::create([
                'account_id' => Auth::id(),
                'table_name' => 'fund_allocations',
                'action' => 'UPDATE',
                'description' => $description,
                'log_time' => now(),
            ]);
        });

        if ($this->shouldRespondJson($request)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Fund allocation updated successfully.',
                'data' => $fundAllocation->fresh(),
            ]);
        }

        return redirect()->route('custodian.fund_allocations.index')
            ->with('success', 'Fund allocation updated successfully.');
    }

    /**
     * Backward-compatible update entrypoint for clients that send PUT to
     * the collection endpoint with allocation_id in the payload.
     */
    public function updateFromRequest(Request $request)
    {
        $id = $request->input('allocation_id');

        if (!$id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing allocation reference.',
            ], 422);
        }

        $fundAllocation = FundAllocation::find($id);

        if (!$fundAllocation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Fund allocation not found.',
            ], 404);
        }

        return $this->update($request, $fundAllocation);
    }

    public function destroy(Request $request, FundAllocation $fundAllocation)
    {
        $allocated = $fundAllocation->total_amount - $fundAllocation->remaining_amount;

        if ($allocated > 0) {
            return response()->json([
                'status' => 'error',
                'message' => sprintf(
                    'Cannot delete fund allocation with active reservations. Allocated: ₱%s',
                    number_format($allocated, 2)
                ),
            ], 422);
        }

        DB::transaction(function () use ($fundAllocation) {
            $cluster = $fundAllocation->fund_cluster;
            $total = $fundAllocation->total_amount;

            $fundAllocation->delete();

            AuditLog::create([
                'account_id' => Auth::id(),
                'table_name' => 'fund_allocations',
                'action' => 'DELETE',
                'description' => sprintf(
                    "Deleted fund allocation '%s' (Total: ₱%s)",
                    $cluster,
                    number_format($total, 2)
                ),
                'log_time' => now(),
            ]);
        });

        if ($this->shouldRespondJson($request)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Fund allocation deleted successfully.',
            ]);
        }

        return redirect()->route('custodian.fund_allocations.index')
            ->with('success', 'Fund allocation deleted successfully.');
    }

    public function printPdf(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $utilizationFilter = (string) $request->input('utilization', 'all');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $allocations = $this->allocationIndexQuery($search, $utilizationFilter, $dateFrom, $dateTo)
            ->with(['creator.employee'])
            ->orderByDesc('id')
            ->get();

        [$preparedByName, $preparedByRole] = $this->resolvePreparedBy();

        return view('custodian.fund_allocations.print', [
            'allocations' => $allocations,
            'generatedOnLabel' => $this->buildGeneratedOnLabel($dateFrom, $dateTo),
            'preparedByName' => $preparedByName,
            'preparedByRole' => $preparedByRole,
            'search' => $search,
            'utilizationFilter' => $utilizationFilter,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $utilizationFilter = (string) $request->input('utilization', 'all');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $allocations = $this->allocationIndexQuery($search, $utilizationFilter, $dateFrom, $dateTo)
            ->with(['creator.employee'])
            ->orderByDesc('id')
            ->get();

        [$preparedByName, $preparedByRole] = $this->resolvePreparedBy();

        $html = view('custodian.fund_allocations.excel', [
            'allocations' => $allocations,
            'generatedOnLabel' => $this->buildGeneratedOnLabel($dateFrom, $dateTo),
            'preparedByName' => $preparedByName,
            'preparedByRole' => $preparedByRole,
            'search' => $search,
            'utilizationFilter' => $utilizationFilter,
        ])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="Fund-Allocations-' . date('Y-m-d') . '.xls"');
    }

    private function allocationIndexQuery(string $search = '', string $utilizationFilter = 'all', ?string $dateFrom = null, ?string $dateTo = null)
    {
        $utilizationSubquery = DB::table('purchase_orders as po')
            ->join('purchase_requests as pr', 'pr.pr_no', '=', 'po.pr_no')
            ->leftJoin('purchase_order_items as poi', 'poi.po_no', '=', 'po.po_no')
            ->whereNotNull('pr.fund_allocation_id')
            ->groupBy('pr.fund_allocation_id')
            ->selectRaw('pr.fund_allocation_id as fund_allocation_id')
            ->selectRaw('COALESCE(SUM(COALESCE(poi.total_cost, (COALESCE(poi.quantity, 0) * COALESCE(poi.unit_cost, 0)))), 0) as utilized_amount')
            ->selectRaw('COUNT(DISTINCT po.po_no) as po_count');

        $query = FundAllocation::query()
            ->leftJoinSub($utilizationSubquery, 'utilization', function ($join) {
                $join->on('utilization.fund_allocation_id', '=', 'fund_allocations.id');
            })
            ->select('fund_allocations.*')
            ->selectRaw('COALESCE(utilization.utilized_amount, 0) as utilized_amount')
            ->selectRaw('COALESCE(utilization.po_count, 0) as po_count');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('fund_allocations.fund_cluster', 'like', "%{$search}%")
                    ->orWhereHas('creator', function ($accountQuery) use ($search) {
                        $accountQuery->where('username', 'like', "%{$search}%")
                            ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                                $employeeQuery
                                    ->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%")
                                    ->orWhere('middle_name', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereExists(function ($orderQuery) use ($search) {
                        $orderQuery->selectRaw('1')
                            ->from('purchase_orders as po')
                            ->join('purchase_requests as pr', 'pr.pr_no', '=', 'po.pr_no')
                            ->whereColumn('pr.fund_allocation_id', 'fund_allocations.id')
                            ->where(function ($nested) use ($search) {
                                $nested->where('po.po_no', 'like', "%{$search}%")
                                    ->orWhere('pr.pr_no', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if ($dateFrom) {
            $query->whereDate('fund_allocations.created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('fund_allocations.created_at', '<=', $dateTo);
        }

        switch ($utilizationFilter) {
            case 'unused':
                $query->whereRaw('COALESCE(utilization.utilized_amount, 0) <= 0');
                break;
            case 'partial':
                $query->whereRaw('COALESCE(utilization.utilized_amount, 0) > 0')
                    ->whereRaw('COALESCE(utilization.utilized_amount, 0) < fund_allocations.total_amount');
                break;
            case 'full':
                $query->whereRaw('COALESCE(utilization.utilized_amount, 0) >= fund_allocations.total_amount');
                break;
            case 'over':
                $query->whereRaw('COALESCE(utilization.utilized_amount, 0) > fund_allocations.total_amount');
                break;
            default:
                break;
        }

        return $query;
    }

    private function buildAllocationSummary(EloquentBuilder $query): object
    {
        return DB::query()
            ->fromSub((clone $query)->toBase(), 'allocation_rows')
            ->selectRaw('COUNT(*) as total_clusters')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total_budget')
                ->selectRaw('COALESCE(SUM(GREATEST(total_amount - utilized_amount, 0)), 0) as total_remaining')
            ->selectRaw('COALESCE(SUM(utilized_amount), 0) as total_utilized')
            ->first();
    }

    private function resolvePreparedBy(): array
    {
        $account = Auth::user();
        $employee = $account?->employee;

        $name = $employee
            ? trim(collect([
                $employee->first_name,
                $employee->middle_name,
                $employee->last_name,
                $employee->suffix,
            ])->filter()->implode(' '))
            : ($account?->username ?? 'System User');

        $role = $account?->role
            ? ucwords(str_replace('_', ' ', (string) $account->role))
            : 'User';

        return [$name, $role];
    }

    private function buildGeneratedOnLabel(?string $dateFrom, ?string $dateTo): string
    {
        if ($dateFrom && $dateTo) {
            return sprintf('%s to %s', date('F d, Y', strtotime($dateFrom)), date('F d, Y', strtotime($dateTo)));
        }

        if ($dateFrom) {
            return sprintf('From %s', date('F d, Y', strtotime($dateFrom)));
        }

        if ($dateTo) {
            return sprintf('Until %s', date('F d, Y', strtotime($dateTo)));
        }

        return now()->format('F d, Y');
    }

    /**
     * Suggest a new fund cluster code (e.g., FY2025-GEN-001).
     */
    public function suggest()
    {
        $year = now()->year;
        $fy = 'FY' . $year;

        // Count allocations created this year to produce a sequence number
        $count = FundAllocation::whereYear('created_at', $year)->count();
        $next = $count + 1;

        $code = sprintf('%s-GEN-%03d', $fy, $next);

        return response()->json([
            'suggested_code' => $code,
            'year' => $year,
        ]);
    }
}
