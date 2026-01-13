<?php

namespace App\Http\Controllers\Custodian;

use App\Http\Controllers\Controller;
use App\Models\FundAllocation;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FundAllocationController extends Controller
{
    public function index()
    {
        $allocations = FundAllocation::with(['creator.employee'])
            ->orderBy('fund_cluster')
            ->paginate(15);

        return view('custodian.fund_allocations.index', compact('allocations'));
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

        if ($request->wantsJson()) {
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

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Fund allocation updated successfully.',
                'data' => $fundAllocation->fresh(),
            ]);
        }

        return redirect()->route('custodian.fund_allocations.index')
            ->with('success', 'Fund allocation updated successfully.');
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

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Fund allocation deleted successfully.',
            ]);
        }

        return redirect()->route('custodian.fund_allocations.index')
            ->with('success', 'Fund allocation deleted successfully.');
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
