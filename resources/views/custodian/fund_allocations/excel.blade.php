<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fund Allocations Export</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #0f172a; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #1e293b; padding: 8px; text-align: left; }
        th { background-color: #e2e8f0; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .meta { margin: 4px 0; color: #475569; font-size: 12px; }
        .prepared { margin-top: 20px; width: 100%; }
        .prepared-wrap { width: 320px; margin-left: auto; text-align: right; }
        .line { border-bottom: 1px solid #1f2937; margin-top: 3px; }
    </style>
</head>
<body>
    <h1>Fund Allocations Report</h1>
    <p class="meta">Generated on: {{ $generatedOnLabel ?? now()->format('F d, Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>Fund Cluster</th>
                <th>Total Amount</th>
                <th>Allocated</th>
                <th>Remaining</th>
                <th>Utilization</th>
                <th>Linked PO</th>
                <th>Created By</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allocations as $allocation)
                @php
                    $allocated = (float) ($allocation->utilized_amount ?? 0);
                    $remaining = max((float) $allocation->total_amount - $allocated, 0);
                    $utilization = (float) $allocation->total_amount > 0
                        ? ($allocated / (float) $allocation->total_amount) * 100
                        : 0;
                    $employee = $allocation->creator?->employee;
                    $creatorName = $employee
                        ? trim(($employee->first_name ?? '') . ' ' . ($employee->middle_name ?? '') . ' ' . ($employee->last_name ?? ''))
                        : ($allocation->creator?->username ?? 'Unknown');
                @endphp
                <tr>
                    <td>{{ $allocation->fund_cluster }}</td>
                    <td class="text-right">{{ number_format((float) $allocation->total_amount, 2) }}</td>
                    <td class="text-right">{{ number_format($allocated, 2) }}</td>
                    <td class="text-right">{{ number_format($remaining, 2) }}</td>
                    <td class="text-center">{{ number_format($utilization, 1) }}%</td>
                    <td class="text-center">{{ number_format((int) ($allocation->po_count ?? 0)) }}</td>
                    <td>{{ $creatorName }}</td>
                    <td>{{ optional($allocation->created_at)->format('M d, Y h:i A') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th class="text-right">Grand Total</th>
                <th class="text-right">{{ number_format((float) $allocations->sum('total_amount'), 2) }}</th>
                <th class="text-right">{{ number_format((float) $allocations->sum(fn ($row) => (float) ($row->utilized_amount ?? 0)), 2) }}</th>
                <th class="text-right">{{ number_format((float) $allocations->sum(fn ($row) => max((float) $row->total_amount - (float) ($row->utilized_amount ?? 0), 0)), 2) }}</th>
                <th class="text-center">—</th>
                <th class="text-center">{{ number_format((int) $allocations->sum('po_count')) }}</th>
                <th colspan="2">{{ number_format($allocations->count()) }} record(s)</th>
            </tr>
        </tfoot>
    </table>

    <div class="prepared">
        <div class="prepared-wrap">
            <p class="meta" style="text-transform: uppercase; letter-spacing: 0.04em; font-weight: 700; text-align: left;">Prepared by:</p>
            <p style="font-weight: 700; margin-bottom: 0;">{{ $preparedByName ?? 'System User' }}</p>
            <div class="line"></div>
            <p class="meta" style="margin-top: 4px;">{{ $preparedByRole ?? 'User' }}</p>
        </div>
    </div>
</body>
</html>
