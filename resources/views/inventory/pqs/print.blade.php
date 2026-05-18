<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property, Plant & Equipment Ledger</title>
    @vite('resources/css/app.css')
    <style>
        :root {
            --ink: #0f172a;
            --muted: #475569;
            --line: #1f2937;
            --surface: #f8fafc;
        }

        @media print {
            @page {
                size: landscape;
                margin: 0.4in;
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                margin: 0;
                background: #fff !important;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
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
            tfoot {
                display: table-footer-group;
            }
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--ink);
        }

        .report-shell {
            border: 1px solid var(--line);
            border-radius: 16px;
            overflow: hidden;
        }

        .report-header {
            border-bottom: 1px solid var(--line);
            background: linear-gradient(135deg, #f8fafc, #eef2ff);
        }

        .report-table th,
        .report-table td {
            border: 1px solid #1e293b;
        }

        .report-table th {
            background: #e2e8f0;
            color: #0f172a;
            letter-spacing: 0.02em;
        }

        .summary-card {
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            background: var(--surface);
        }

        .prepared-by-block {
            width: 240px;
            margin-left: auto;
            text-align: right;
        }

        .prepared-by-line {
            border-bottom: 1px solid var(--line);
            width: 190px;
            height: 0;
            margin-top: 2px;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body class="bg-white">
    <div class="report-shell">
    <!-- Header Section with Logos -->
    <div class="report-header px-4 py-4">
        <div class="flex items-center justify-between gap-4">
            <img src="{{ asset('images/bpi-logo.png') }}" alt="Department of Agriculture" class="h-16 w-16 object-contain">
            <div class="flex-1 text-center">
                <h1 class="text-4xl font-black uppercase tracking-[0.04em]">Property, Plant & Equipment Ledger Card</h1>
                <p class="text-sm mt-1 text-slate-700">Comprehensive Inventory Report</p>
                <p class="text-xs text-slate-600 mt-2">Generated on: {{ $generatedOnLabel ?? now()->format('F d, Y') }}</p>
            </div>
            <img src="{{ asset('images/pqslogo.png') }}" alt="Plant Quarantine Service" class="h-16 w-16 object-contain">
        </div>
    </div>

    @php
        $recordsPerPage = 15; // Adjust based on landscape orientation
        $chunks = $records->chunk($recordsPerPage);
        $totalRecords = $records->count();
        $totalValue = $records->sum('total_value');
    @endphp

    @foreach($chunks as $chunkIndex => $chunk)
        @if($chunkIndex > 0)
            <div class="page-break"></div>
            <!-- Repeat header on new page -->
            <div class="mb-4 text-center border-b border-slate-800 pb-2 pt-2">
                <h2 class="text-lg font-bold">PQS Registry (continued)</h2>
                <p class="text-xs text-gray-600">Page {{ $chunkIndex + 1 }} of {{ $chunks->count() }}</p>
            </div>
        @endif

        <!-- Table -->
        <table class="report-table w-full border-collapse text-xs">
            <thead>
                <tr>
                    <th class="border border-black px-2 py-2 text-left font-semibold w-24">Property No.</th>
                    <th class="border border-black px-2 py-2 text-left font-semibold w-32">Article</th>
                    <th class="border border-black px-2 py-2 text-left font-semibold w-40">Description</th>
                    <th class="border border-black px-2 py-2 text-center font-semibold w-20">Date Acquired</th>
                    <th class="border border-black px-2 py-2 text-center font-semibold w-16">Quantity</th>
                    <th class="border border-black px-2 py-2 text-right font-semibold w-20">Unit Value</th>
                    <th class="border border-black px-2 py-2 text-right font-semibold w-24">Total Value</th>
                    <th class="border border-black px-2 py-2 text-left font-semibold w-32">Accountable Officer</th>
                    <th class="border border-black px-2 py-2 text-center font-semibold w-16">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($chunk as $record)
                @php
                    $assetStatusLabel = match ((string) ($record->asset_status ?? \App\Models\PqsRecord::STATUS_ACTIVE)) {
                        \App\Models\PqsRecord::STATUS_MAINTENANCE => 'Under Maintenance',
                        \App\Models\PqsRecord::STATUS_FOR_REPAIR,
                        \App\Models\PqsRecord::STATUS_DISPOSED,
                        \App\Models\PqsRecord::STATUS_LOST => 'Unserviceable',
                        default => 'Serviceable',
                    };
                @endphp
                <tr class="hover:bg-slate-50/60">
                    <td class="border border-black px-2 py-1.5 text-xs">{{ $record->property_no }}</td>
                    <td class="border border-black px-2 py-1.5 text-xs">{{ $record->article }}</td>
                    <td class="border border-black px-2 py-1.5 text-xs">
                        {{ Str::limit($record->description, 60) }}
                    </td>
                    <td class="border border-black px-2 py-1.5 text-center text-xs">
                        {{ $record->date_acquired ? $record->date_acquired->format('m/d/Y') : '—' }}
                    </td>
                    <td class="border border-black px-2 py-1.5 text-center text-xs">
                        {{ number_format($record->on_hand_per_count) }}
                    </td>
                    <td class="border border-black px-2 py-1.5 text-right text-xs">
                        ₱{{ number_format($record->unit_value, 2) }}
                    </td>
                    <td class="border border-black px-2 py-1.5 text-right text-xs font-semibold">
                        ₱{{ number_format($record->total_value, 2) }}
                    </td>
                    <td class="border border-black px-2 py-1.5 text-xs text-slate-800">
                        {{ $record->accountableOfficer ? $record->accountableOfficer->full_name : 'Unassigned' }}
                    </td>
                    <td class="border border-black px-2 py-1.5 text-center text-xs">
                        <span class="font-semibold">{{ $assetStatusLabel }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="border border-black px-2 py-4 text-center text-gray-500">
                        No records found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($chunkIndex === $chunks->count() - 1)
        <!-- Summary on last page -->
        <div class="mt-6 border-t-2 border-slate-900 pt-4 px-2 pb-4">
            <div class="flex justify-between items-center">
                <div class="summary-card px-4 py-3">
                    <p class="text-sm font-semibold">Total Records: {{ number_format($totalRecords) }}</p>
                    <p class="text-sm font-semibold mt-1">Total Inventory Value: ₱{{ number_format($totalValue, 2) }}</p>
                </div>
                <div class="prepared-by-block">
                    <p class="text-xs text-slate-600 font-semibold uppercase tracking-wide text-left">Prepared by:</p>
                    <div class="mt-3 text-center">
                        <p class="text-sm font-bold">{{ $preparedByName }}</p>
                        <div class="prepared-by-line"></div>
                        <p class="text-xs text-slate-600 mt-1">{{ $preparedByRole }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endforeach
    </div>
        <!-- Print Controls (screen only) -->
        <div class="fixed bottom-4 right-4 no-print flex gap-2">
            <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 flex items-center gap-2">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
</body>
</html>
