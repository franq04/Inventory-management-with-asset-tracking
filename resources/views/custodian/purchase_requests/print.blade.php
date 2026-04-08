<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Requests Report</title>
    @vite('resources/css/app.css')
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

        @media print {
            @page {
                size: landscape;
                margin: 0.5in;
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                margin: 0;
                background: #fff !important;
            }
            .no-print { display: none !important; }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-white p-6 text-gray-900">
    <div class="report-shell">
    <div class="report-header px-4 py-4">
        <div class="flex items-center justify-between">
            <img src="{{ asset('images/bpi-logo.png') }}" alt="Department of Agriculture" class="h-16 w-16 object-contain">
            <div class="flex-1 text-center">
                <h1 class="text-3xl font-black uppercase tracking-[0.04em]">Purchase Requests Report</h1>
                <p class="mt-1 text-sm text-slate-700">Procurement Request Monitoring Summary ({{ strtoupper($activeTab ?? 'ALL') }})</p>
                <p class="mt-2 text-xs text-gray-600">Generated on: {{ $generatedOnLabel ?? now()->format('F d, Y') }}</p>
            </div>
            <img src="{{ asset('images/pqslogo.png') }}" alt="Plant Quarantine Service" class="h-16 w-16 object-contain">
        </div>
    </div>

    <table class="report-table w-full border-collapse text-xs">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-black px-2 py-2 text-left font-semibold">PR Number</th>
                <th class="border border-black px-2 py-2 text-left font-semibold">Requested By</th>
                <th class="border border-black px-2 py-2 text-left font-semibold">Division / Section</th>
                <th class="border border-black px-2 py-2 text-left font-semibold">Status</th>
                <th class="border border-black px-2 py-2 text-right font-semibold">Total Cost</th>
                <th class="border border-black px-2 py-2 text-left font-semibold">Submitted</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchaseRequests as $request)
                <tr>
                    <td class="border border-black px-2 py-1.5">{{ $request->pr_no }}</td>
                    <td class="border border-black px-2 py-1.5">{{ $request->requester?->username ?? '—' }}</td>
                    <td class="border border-black px-2 py-1.5">
                        {{ $request->division?->division_name ?? '—' }}
                        @if($request->section?->section_name)
                            <span class="text-gray-600"> / {{ $request->section->section_name }}</span>
                        @endif
                    </td>
                    <td class="border border-black px-2 py-1.5">{{ $request->status?->status_name ?? 'Unknown' }}</td>
                    <td class="border border-black px-2 py-1.5 text-right">{{ number_format((float) $request->total_estimated_cost, 2) }}</td>
                    <td class="border border-black px-2 py-1.5">{{ optional($request->created_at)->format('M d, Y h:i A') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="border border-black px-2 py-4 text-center text-gray-500">No purchase requests found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="border border-black px-2 py-2 text-right">Grand Total</th>
                <th class="border border-black px-2 py-2 text-right">{{ number_format((float) $purchaseRequests->sum('total_estimated_cost'), 2) }}</th>
                <th class="border border-black px-2 py-2 text-left">{{ number_format($purchaseRequests->count()) }} record(s)</th>
            </tr>
        </tfoot>
    </table>

    <div class="px-5 pb-5 pt-4 border-t border-slate-800">
        <div class="prepared-by-block">
            <p class="text-xs text-slate-600 font-semibold uppercase tracking-wide text-left">Prepared by:</p>
            <div class="mt-3 text-center">
                <p class="text-sm font-bold">{{ $preparedByName ?? 'System User' }}</p>
                <div class="prepared-by-line"></div>
                <p class="text-xs text-slate-600 mt-1">{{ $preparedByRole ?? 'User' }}</p>
            </div>
        </div>
    </div>
    </div>

    <div class="fixed bottom-4 right-4 no-print">
        <button onclick="window.print()" class="rounded bg-blue-600 px-4 py-2 text-white shadow hover:bg-blue-700">
            Print
        </button>
    </div>
</body>
</html>
