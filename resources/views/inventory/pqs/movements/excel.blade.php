<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset Movement Report</title>
    <style>
        :root {
            --ink: #0f172a;
            --line: #1f2937;
            --surface: #f8fafc;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--ink);
            font-size: 12px;
        }

        .report-shell {
            border: 1px solid var(--line);
            border-radius: 14px;
            overflow: hidden;
        }

        .report-header {
            border-bottom: 1px solid var(--line);
            background: #eef2ff;
            padding: 10px 14px;
        }

        .report-header table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-header td {
            border: 0;
            vertical-align: middle;
        }

        .header-title {
            margin: 0;
            font-size: 28px;
            line-height: 1.15;
            font-weight: 900;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            text-align: center;
        }

        .header-subtitle {
            margin: 4px 0 0;
            text-align: center;
            font-size: 13px;
            color: #334155;
        }

        .header-meta {
            margin: 6px 0 0;
            text-align: center;
            font-size: 11px;
            color: #475569;
        }

        .report-table {
            border-collapse: collapse;
            width: 100%;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #1e293b;
            padding: 6px 8px;
            vertical-align: top;
        }

        .report-table th {
            background: #e2e8f0;
            text-align: left;
            font-weight: 700;
        }

        .summary-wrap {
            border-top: 2px solid #0f172a;
            padding: 10px 12px 12px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            border: 0;
            vertical-align: top;
        }

        .summary-card {
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            background: var(--surface);
            padding: 10px 12px;
            display: inline-block;
        }

        .prepared-label {
            margin: 0;
            font-size: 11px;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            text-align: left;
        }

        .prepared-name {
            margin: 7px 0 0;
            text-align: center;
            font-size: 17px;
            font-weight: 700;
        }

        .prepared-line {
            border-bottom: 1px solid #1f2937;
            width: 190px;
            height: 0;
            margin: 2px auto 0;
        }

        .prepared-role {
            margin: 4px 0 0;
            text-align: center;
            font-size: 12px;
            color: #475569;
        }
    </style>
</head>
<body>
    @php
        $totalMovements = $movements->count();
    @endphp
    <div class="report-shell">
        <div class="report-header">
            <table>
                <tr>
                    <td style="width:72px; text-align:left;">
                        <img src="{{ asset('images/bpi-logo.png') }}" alt="Department of Agriculture" width="62" height="62" style="display:block; object-fit:contain;">
                    </td>
                    <td>
                        <h1 class="header-title">Asset Movement Report</h1>
                        <p class="header-subtitle">Property, Plant & Equipment Movement Ledger</p>
                        <p class="header-meta">Generated on: {{ $generatedOnLabel ?? now()->format('F d, Y') }}</p>
                    </td>
                    <td style="width:72px; text-align:right;">
                        <img src="{{ asset('images/pqslogo.png') }}" alt="Plant Quarantine Service" width="62" height="62" style="display:block; margin-left:auto; object-fit:contain;">
                    </td>
                </tr>
            </table>
        </div>

        <table class="report-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Property No</th>
                <th>Article</th>
                <th>Movement Type</th>
                <th>From Location</th>
                <th>To Location</th>
                <th>From Employee</th>
                <th>To Employee</th>
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
                    <td>{{ $movement->from_endpoint_display ?: '' }}</td>
                    <td>{{ $movement->to_endpoint_display ?: '' }}</td>
                    <td>{{ $movement->from_custodian_display ?: '' }}</td>
                    <td>{{ $movement->to_custodian_display ?: '' }}</td>
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

            <div class="summary-wrap">
                <table class="summary-table">
                    <tr>
                        <td>
                            <div class="summary-card">
                                <strong>Total Movement Records: {{ number_format($totalMovements) }}</strong>
                            </div>
                        </td>
                        <td style="width:250px;">
                            <p class="prepared-label">Prepared by:</p>
                            <p class="prepared-name">{{ $preparedByName ?? 'System User' }}</p>
                            <div class="prepared-line"></div>
                            <p class="prepared-role">{{ $preparedByRole ?? 'User' }}</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
</body>
</html>
