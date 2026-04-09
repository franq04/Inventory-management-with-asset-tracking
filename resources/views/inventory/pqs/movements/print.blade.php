<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Movement Report</title>
    <style>
        :root {
            --ink: #0f172a;
            --muted: #475569;
            --line: #1f2937;
            --surface: #f8fafc;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--ink);
            background: #fff;
        }

        @media print {
            @page {
                size: landscape;
                margin: 0.25in;
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            thead {
                display: table-header-group;
            }
            .report-shell {
                border-radius: 0;
            }
            .report-header {
                padding: 10px 12px;
            }
            .filters-bar {
                padding: 6px 10px;
            }
        }

        .report-shell {
            border: 1px solid var(--line);
            border-radius: 16px;
            overflow: hidden;
        }

        .report-header {
            border-bottom: 1px solid var(--line);
            background: linear-gradient(135deg, #f8fafc, #eef2ff);
            padding: 14px 18px;
        }

        .header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .header-center {
            flex: 1;
            text-align: center;
        }

        .header-title {
            margin: 0;
            font-size: 28px;
            line-height: 1.1;
            font-weight: 900;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .header-subtitle {
            margin: 4px 0 0;
            font-size: 14px;
            color: #334155;
        }

        .header-meta {
            margin: 8px 0 0;
            font-size: 12px;
            color: #475569;
        }

        .header-logo {
            width: 64px;
            height: 64px;
            object-fit: contain;
        }

        .filters-bar {
            border-bottom: 1px solid #334155;
            background: #f8fafc;
            padding: 8px 12px;
            font-size: 11px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #1e293b;
            padding: 4px 5px;
            vertical-align: top;
            line-height: 1.25;
        }

        .report-table th {
            background: #e2e8f0;
            color: #0f172a;
            text-align: left;
            letter-spacing: 0.02em;
        }

        .summary-wrap {
            margin-top: 6px;
            border-top: 2px solid #0f172a;
            padding: 8px 10px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            page-break-inside: avoid;
        }

        .summary-card {
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            background: var(--surface);
            padding: 10px 14px;
        }

        .summary-line {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
        }

        .prepared-by-block {
            width: 240px;
            margin-left: auto;
            text-align: right;
        }

        .prepared-by-label {
            margin: 0;
            font-size: 11px;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            text-align: left;
        }

        .prepared-by-name {
            margin: 8px 0 0;
            text-align: center;
            font-size: 18px;
            font-weight: 700;
        }

        .prepared-by-line {
            border-bottom: 1px solid var(--line);
            width: 190px;
            height: 0;
            margin: 2px auto 0;
        }

        .prepared-by-role {
            margin: 4px 0 0;
            text-align: center;
            font-size: 12px;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="report-shell">
        <div class="report-header">
            <div class="header-row">
                <img src="{{ asset('images/bpi-logo.png') }}" alt="Department of Agriculture" class="header-logo">
                <div class="header-center">
                    <h1 class="header-title">Asset Movement Report</h1>
                    <p class="header-subtitle">Property, Plant & Equipment Movement Ledger</p>
                    <p class="header-meta">Generated on: {{ $generatedOnLabel ?? now()->format('F d, Y') }}</p>
                </div>
                <img src="{{ asset('images/pqslogo.png') }}" alt="Plant Quarantine Service" class="header-logo">
            </div>
        </div>

        @php
            $appliedFilters = collect($filters ?? [])->filter(fn ($value) => (string) $value !== '');
            $totalMovements = $movements->count();
        @endphp
        <div class="filters-bar">
            <strong>Applied Filters:</strong>
            @if ($appliedFilters->isEmpty())
                None
            @else
                {{ $appliedFilters->map(function ($value, $key) {
                    return ucwords(str_replace('_', ' ', $key)) . ': ' . $value;
                })->join(' | ') }}
            @endif
        </div>

        @if ($movements->isNotEmpty())
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width: 115px;">Date</th>
                        <th style="width: 165px;">Property</th>
                        <th style="width: 95px;">Movement</th>
                        <th style="width: 235px;">Location</th>
                        <th style="width: 205px;">Employee</th>
                        <th style="width: 78px;">Recorded By</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($movements as $movement)
                        <tr>
                            <td>{{ optional($movement->effective_at)->format('m/d/Y h:i A') }}</td>
                            <td>
                                <div><strong>{{ $movement->property_no }}</strong></div>
                                <div>{{ $movement->property?->article ?: '' }}</div>
                            </td>
                            <td>{{ ucwords(str_replace('_', ' ', (string) $movement->movement_type)) }}</td>
                            <td>
                                <div><strong>From:</strong> {{ $movement->from_endpoint_display ?: 'Unspecified' }}</div>
                                <div><strong>To:</strong> {{ $movement->to_endpoint_display ?: 'Unspecified' }}</div>
                            </td>
                            <td>
                                <div><strong>From:</strong> {{ $movement->from_custodian_display ?: 'Unspecified' }}</div>
                                <div><strong>To:</strong> {{ $movement->to_custodian_display ?: 'Unspecified' }}</div>
                            </td>
                            <td>{{ $movement->movedByAccount?->username ?: 'System' }}</td>
                            <td>{{ $movement->remarks ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <table class="report-table">
                <tbody>
                    <tr>
                        <td colspan="7" style="text-align:center;">No movement records found.</td>
                    </tr>
                </tbody>
            </table>
        @endif

        <div class="summary-wrap">
            <div class="summary-card">
                <p class="summary-line">Total Movement Records: {{ number_format($totalMovements) }}</p>
            </div>
            <div class="prepared-by-block">
                <p class="prepared-by-label">Prepared by:</p>
                <p class="prepared-by-name">{{ $preparedByName ?? 'System User' }}</p>
                <div class="prepared-by-line"></div>
                <p class="prepared-by-role">{{ $preparedByRole ?? 'User' }}</p>
            </div>
        </div>
    </div>

    <div class="no-print" style="position:fixed;right:16px;bottom:16px;">
        <button onclick="window.print()" style="background:#2563eb;color:#fff;border:none;border-radius:10px;padding:10px 16px;font-weight:600;cursor:pointer;">Print</button>
    </div>
</body>
</html>
