<?php

namespace App\Http\Controllers;

use App\Models\FundAllocation;

class FundAllocationController extends Controller
{
    public function show($id)
    {
        $allocation = FundAllocation::find($id);

        if (! $allocation) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $remainingAmount = $allocation->syncRemainingAmount();

        return response()->json([
            'id' => $allocation->id,
            'fund_cluster' => $allocation->fund_cluster,
            'total_amount' => (float) $allocation->total_amount,
            'remaining_amount' => $remainingAmount,
        ]);
    }
}
