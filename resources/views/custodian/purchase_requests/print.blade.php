<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Requests Report</title>
    @vite('resources/css/app.css')
    <style>
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
    <div class="mb-6 border-b-2 border-black pb-4">
        <div class="flex items-center justify-between">
            <img src="{{ asset('images/bpi-logo.png') }}" alt="Department of Agriculture" class="h-16 w-16 object-contain">
            <div class="flex-1 text-center">
                <h1 class="text-2xl font-bold uppercase">Purchase Requests Report</h1>
                <p class="mt-1 text-sm">Generated Queue Output ({{ strtoupper($activeTab ?? 'ALL') }})</p>
                <p class="mt-2 text-xs text-gray-600">Generated on {{ now()->format('F d, Y h:i:s A') }} PHT</p>
            </div>
            <img src="{{ asset('images/pqslogo.png') }}" alt="Plant Quarantine Service" class="h-16 w-16 object-contain">
        </div>
    </div>

    <table class="w-full border-collapse text-xs">
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

    <div class="fixed bottom-4 right-4 no-print">
        <button onclick="window.print()" class="rounded bg-blue-600 px-4 py-2 text-white shadow hover:bg-blue-700">
            Print
        </button>
    </div>
</body>
</html>
