<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset Movement Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111827; }
        h2 { margin: 0 0 8px 0; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <h2>Asset Movement Report</h2>
    <p class="muted">Generated {{ now()->format('M d, Y h:i A') }}</p>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Property No</th>
                <th>Article</th>
                <th>Movement Type</th>
                <th>From Location</th>
                <th>To Location</th>
                <th>From Custodian</th>
                <th>To Custodian</th>
                <th>Recorded By</th>
                <th>Reason</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movements as $movement)
                <tr>
                    <td>{{ optional($movement->effective_at)->format('Y-m-d H:i') }}</td>
                    <td>{{ $movement->property_no }}</td>
                    <td>{{ $movement->property?->article ?: '' }}</td>
                    <td>{{ strtoupper((string) $movement->movement_type) }}</td>
                    <td>{{ $movement->fromLocation?->location_name ?: '' }}</td>
                    <td>{{ $movement->toLocation?->location_name ?: '' }}</td>
                    <td>{{ $movement->fromCustodian?->full_name ?: '' }}</td>
                    <td>{{ $movement->toCustodian?->full_name ?: '' }}</td>
                    <td>{{ $movement->movedByAccount?->username ?: 'System' }}</td>
                    <td>{{ $movement->reason_code ?: '' }}</td>
                    <td>{{ $movement->remarks ?: '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11">No movement records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
