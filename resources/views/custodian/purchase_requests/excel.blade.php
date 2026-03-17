<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Purchase Requests Export</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>Purchase Requests Report</h1>
    <p>Generated on: {{ date('F d, Y h:i:s A') }} PHT</p>

    <table>
        <thead>
            <tr>
                <th>PR Number</th>
                <th>Requested By</th>
                <th>Division</th>
                <th>Section</th>
                <th>Status</th>
                <th>Total Cost</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseRequests as $request)
                <tr>
                    <td>{{ $request->pr_no }}</td>
                    <td>{{ $request->requester?->username ?? '—' }}</td>
                    <td>{{ $request->division?->division_name ?? '—' }}</td>
                    <td>{{ $request->section?->section_name ?? '—' }}</td>
                    <td>{{ $request->status?->status_name ?? 'Unknown' }}</td>
                    <td class="text-right">{{ number_format((float) $request->total_estimated_cost, 2) }}</td>
                    <td>{{ optional($request->created_at)->format('M d, Y h:i A') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">Grand Total</th>
                <th class="text-right">{{ number_format((float) $purchaseRequests->sum('total_estimated_cost'), 2) }}</th>
                <th>{{ number_format($purchaseRequests->count()) }} record(s)</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
