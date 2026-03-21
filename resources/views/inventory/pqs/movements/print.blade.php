<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset Movement Report</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; margin: 20px; }
        h1 { margin: 0 0 6px 0; font-size: 22px; }
        .meta { margin: 0 0 12px 0; font-size: 12px; color: #6b7280; }
        .filters { margin: 0 0 14px 0; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; font-size: 11px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 7px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <button type="button" class="no-print" onclick="window.print()">Print</button>
    <h1>Asset Movement Report</h1>
    <p class="meta">Generated {{ now()->format('M d, Y h:i A') }}</p>

    @php
        $appliedFilters = collect($filters ?? [])->filter(fn ($value) => (string) $value !== '');
    @endphp
    <p class="filters">
        <strong>Applied Filters:</strong>
        @if ($appliedFilters->isEmpty())
            None
        @else
            {{ $appliedFilters->map(function ($value, $key) {
                return ucwords(str_replace('_', ' ', $key)) . ': ' . $value;
            })->join(' | ') }}
        @endif
    </p>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Property</th>
                <th>Movement Type</th>
                <th>From Location</th>
                <th>To Location</th>
                <th>From Custodian</th>
                <th>To Custodian</th>
                <th>Recorded By</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movements as $movement)
                <tr>
                    <td>{{ optional($movement->effective_at)->format('Y-m-d H:i') }}</td>
                    <td>
                        <div><strong>{{ $movement->property_no }}</strong></div>
                        <div>{{ $movement->property?->article ?: '' }}</div>
                    </td>
                    <td>{{ ucwords(str_replace('_', ' ', (string) $movement->movement_type)) }}</td>
                    <td>{{ $movement->fromLocation?->location_name ?: 'Unspecified' }}</td>
                    <td>{{ $movement->toLocation?->location_name ?: 'Unspecified' }}</td>
                    <td>{{ $movement->fromCustodian?->full_name ?: 'Unspecified' }}</td>
                    <td>{{ $movement->toCustodian?->full_name ?: 'Unspecified' }}</td>
                    <td>{{ $movement->movedByAccount?->username ?: 'System' }}</td>
                    <td>{{ $movement->remarks ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center;">No movement records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
